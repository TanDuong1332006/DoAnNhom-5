<?php
namespace App\Http\Controllers;

use App\Models\KhachHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage, Mail};
use Illuminate\Support\Str;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $user=$request->user('sanctum');
        if ($request->bearerToken()) abort_unless($user,401);
        if ($user) abort_unless($user instanceof KhachHang && ($user->role ?: 'customer')==='customer' && !$user->is_khoa && $user->is_kich_hoat,403);
        $data=$request->validate([
            'topic'=>'required|in:product,delivery,service,website,other','body'=>'required|string|min:10|max:5000',
            'email'=>'nullable|email|max:255','phone'=>['nullable','regex:/^\+?[0-9][0-9\s-]{7,23}[0-9]$/','regex:/^(?:\D*\d){9,15}\D*$/'],
            'website'=>'nullable|size:0','images'=>'sometimes|array|max:5',
            'images.*'=>'required|file|image|mimes:jpg,jpeg,png,webp|max:1024|dimensions:max_width=6000,max_height=6000',
        ]);
        $paths=[];
        try {
            $id=DB::transaction(function () use ($request,$data,$user,&$paths) {
                $id=DB::table('private_feedback')->insertGetId(['customer_id'=>$user?->id,'topic'=>$data['topic'],'body'=>$data['body'],
                    'email'=>$data['email']??null,'phone'=>$data['phone']??null,'status'=>'unread','created_at'=>now(),'updated_at'=>now()]);
                foreach ($request->file('images',[]) as $file) {
                    $mime=$file->getMimeType();
                    $ext=match($mime){'image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp',default=>abort(422,'Ảnh không hợp lệ.')};
                    $path=$file->storeAs('feedback',Str::uuid().'.'.$ext,'local');
                    abort_unless($path,500,'Không thể lưu ảnh.'); $paths[]=$path;
                    DB::table('feedback_attachments')->insert(['feedback_id'=>$id,'path'=>$path,'mime'=>$mime,'bytes'=>$file->getSize(),'created_at'=>now(),'updated_at'=>now()]);
                }
                return $id;
            });
        } catch (\Throwable $error) { foreach($paths as $path) Storage::disk('local')->delete($path); throw $error; }
        return response()->json(['status'=>1,'message'=>'Cảm ơn bạn. SOFEP đã nhận góp ý riêng của bạn.','reference'=>$id],201);
    }
    public function index(Request $request)
    {
        $request->validate(['q'=>'nullable|string|max:160','status'=>'nullable|in:unread,processing,resolved','topic'=>'nullable|in:product,delivery,service,website,other']);
        $q=DB::table('private_feedback')->select('id','customer_id','topic','status','created_at')->orderByDesc('id');
        if($request->filled('q')) $q->where(fn($q)=>$q->where('body','like','%'.$request->q.'%')->orWhere('email','like','%'.$request->q.'%'));
        foreach(['status','topic'] as $field) if($request->filled($field)) $q->where($field,$request->$field);
        return response()->json(['data'=>$q->paginate(20)]);
    }
    public function show(int $id)
    {
        $feedback=DB::table('private_feedback')->find($id); abort_unless($feedback,404);
        $images=DB::table('feedback_attachments')->where('feedback_id',$id)->get(['id','mime','bytes']);
        return response()->json(['data'=>$feedback,'images'=>$images,'email_reply_enabled'=>(bool)config('engagement.feedback_email_enabled',false)]);
    }
    public function update(Request $request,int $id)
    {
        $data=$request->validate(['status'=>'required|in:unread,processing,resolved','internal_note'=>'nullable|string|max:5000']);
        abort_unless(DB::table('private_feedback')->where('id',$id)->exists(),404);
        DB::table('private_feedback')->where('id',$id)->update($data+['updated_at'=>now()]);
        return response()->json(['status'=>1]);
    }
    public function image(int $id,int $image)
    {
        $row=DB::table('feedback_attachments')->where('feedback_id',$id)->where('id',$image)->first();abort_unless($row,404);
        abort_unless(preg_match('~^feedback/[a-f0-9-]+\.(jpg|png|webp)$~D',$row->path) && Storage::disk('local')->exists($row->path),404);
        return response()->file(Storage::disk('local')->path($row->path),['Content-Type'=>$row->mime,'X-Content-Type-Options'=>'nosniff','Cache-Control'=>'private, no-store']);
    }
    public function reply(Request $request,int $id)
    {
        $data=$request->validate(['body'=>'required|string|min:2|max:5000','channel'=>'required|in:in_app,email']);
        $f=DB::table('private_feedback')->find($id);abort_unless($f,404);
        if($data['channel']==='email') {
            abort_unless(config('engagement.feedback_email_enabled',false) && in_array(config('mail.default'), ['smtp','ses','postmark','resend'], true) && filter_var($f->email,FILTER_VALIDATE_EMAIL),422,'Kênh email chưa được cấu hình và phê duyệt, hoặc địa chỉ không hợp lệ.');
            // Không dùng kênh này cho chiến dịch marketing. Bật riêng sau khi xác minh cấu hình.
            Mail::raw($data['body'], fn($mail)=>$mail->to($f->email)->subject('SOFEP phản hồi góp ý #'.$id));
        } else {
            $customer=$f->customer_id ? KhachHang::find($f->customer_id) : null;
            abort_unless($customer && ($customer->role ?: 'customer')==='customer' && !$customer->is_khoa && $customer->is_kich_hoat,422,'Khách chưa có hộp thư Customer hợp lệ; có thể lưu ghi chú hoặc dùng kênh liên hệ đã xác minh.');
        }
        DB::transaction(function () use($id,$f,$data){
            if($data['channel']==='in_app') DB::table('customer_notifications')->insertOrIgnore(['customer_id'=>$f->customer_id,
                'delivery_key'=>'feedback:'.$id.':'.hash('sha256',$data['body']),'title'=>'SOFEP phản hồi góp ý #'.$id,
                'body'=>$data['body'],'kind'=>'system','created_at'=>now(),'updated_at'=>now()]);
            DB::table('private_feedback')->where('id',$id)->update(['reply'=>$data['body'],'reply_channel'=>$data['channel'],'replied_at'=>now(),'updated_at'=>now()]);
        });
        return response()->json(['status'=>1,'message'=>'Đã gửi phản hồi riêng.']);
    }
}
