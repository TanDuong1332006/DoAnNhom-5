<?php
namespace App\Http\Controllers;
use App\Models\{MaGiamGia,KhachHang};
use App\Services\VoucherWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerInboxController extends Controller
{
    public function count(Request $request)
    {
        return response()->json(['unread'=>DB::table('customer_notifications')->where('customer_id',$request->user()->id)->whereNull('read_at')->count()]);
    }
    public function index(Request $request)
    {
        $request->validate(['filter'=>'nullable|in:all,unread,promotion,system']); $id=$request->user()->id;
        $q=DB::table('customer_notifications')->where('customer_id',$id);
        if($request->filter==='unread') $q->whereNull('read_at');
        if($request->filter==='promotion') $q->whereIn('kind',['promotion','thanks','voucher']);
        if($request->filter==='system') $q->whereIn('kind',['general','system']);
        $page=$q->orderByDesc('id')->paginate(20);
        $vouchers=MaGiamGia::whereIn('id',$page->getCollection()->pluck('voucher_id')->filter()->unique())->get()->keyBy('id');
        $page->setCollection($page->getCollection()->map(function($n)use($vouchers,$request){
            $n->voucher=isset($vouchers[$n->voucher_id]) ? app(VoucherWallet::class)->describe($vouchers[$n->voucher_id],$request->user()) : null; return $n;
        }));
        return response()->json(['data'=>$page,'unread'=>DB::table('customer_notifications')->where('customer_id',$id)->whereNull('read_at')->count()]);
    }
    public function read(Request $request,int $id)
    {
        $q=DB::table('customer_notifications')->where('customer_id',$request->user()->id)->where('id',$id);abort_unless($q->exists(),404);
        $q->whereNull('read_at')->update(['read_at'=>now(),'updated_at'=>now()]); return response()->json(['status'=>1]);
    }
    public function readAll(Request $request)
    {
        DB::table('customer_notifications')->where('customer_id',$request->user()->id)->whereNull('read_at')->update(['read_at'=>now(),'updated_at'=>now()]);return response()->json(['status'=>1]);
    }
    public function preferences(Request $request)
    {
        if($request->isMethod('put')){
            $data=$request->validate(['marketing_enabled'=>'required|boolean']);
            DB::table('customer_preferences')->updateOrInsert(['customer_id'=>$request->user()->id],$data+['created_at'=>now(),'updated_at'=>now()]);
        }
        return response()->json(['marketing_enabled'=>(bool)(DB::table('customer_preferences')->where('customer_id',$request->user()->id)->value('marketing_enabled') ?? true)]);
    }
    public function wallet(Request $request)
    {
        $rows=DB::table('saved_vouchers')->where('customer_id',$request->user()->id)->orderByDesc('id')->paginate(20);
        $coupons=MaGiamGia::whereIn('id',$rows->getCollection()->pluck('voucher_id'))->get()->keyBy('id');
        $rows->setCollection($rows->getCollection()->filter(fn($r)=>isset($coupons[$r->voucher_id]))->map(fn($r)=>app(VoucherWallet::class)->describe($coupons[$r->voucher_id],$request->user()))->values());
        return response()->json(['data'=>$rows]);
    }
    public function save(Request $request,int $id)
    {
        return DB::transaction(function()use($request,$id){
            KhachHang::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $coupon=MaGiamGia::whereKey($id)->lockForUpdate()->firstOrFail();
            $coupon->assertCustomer($request->user()); $coupon->discountFor(max((float)$coupon->gia_tri_toi_thieu,1));
            DB::table('saved_vouchers')->insertOrIgnore(['customer_id'=>$request->user()->id,'voucher_id'=>$id,'created_at'=>now(),'updated_at'=>now()]);
            return response()->json(['status'=>1,'message'=>'Đã lưu mã vào Ví ưu đãi; chưa sử dụng lượt.']);
        },3);
    }
}
