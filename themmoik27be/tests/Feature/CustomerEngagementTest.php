<?php
namespace Tests\Feature;

use App\Models\{KhachHang,User,MaGiamGia};
use App\Services\{CustomerCampaigns,LoyaltyAudience};
use Illuminate\Support\Facades\{DB,Storage,Schema};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Carbon\CarbonImmutable;

class CustomerEngagementTest extends ProductDetailDemoTest
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::statement('CREATE TABLE ma_giam_gias (id INTEGER PRIMARY KEY, ma_code TEXT UNIQUE, ten_giam_gia TEXT, loai_giam TEXT, gia_tri NUMERIC, gia_tri_toi_thieu NUMERIC DEFAULT 0, giam_toi_da NUMERIC, so_luong INTEGER DEFAULT 100, so_luong_da_dung INTEGER DEFAULT 0, ngay_bat_dau TEXT, ngay_ket_thuc TEXT, trang_thai INTEGER DEFAULT 1, doi_tuong TEXT DEFAULT \'all\', gioi_han_moi_khach INTEGER, ids_khach_hang TEXT, created_at TEXT, updated_at TEXT)');
        foreach(['ton_kho_da_giu INTEGER DEFAULT 0','voucher_da_giu INTEGER DEFAULT 0','checkout_fingerprint TEXT'] as $col) DB::statement('ALTER TABLE don_hangs ADD COLUMN '.$col);
        (require database_path('migrations/2026_11_06_000001_create_customer_engagement_tables.php'))->up();
        Storage::fake('local');
    }
    private function api(string $method,string $path,?string $token=null,array $data=[])
    {
        $this->app['auth']->forgetGuards();
        return $this->json($method,'/api/'.$path,$data,$token?['Authorization'=>'Bearer '.$token]:[]);
    }
    private function admin(): string
    {
        return User::firstOrCreate(['email'=>'eng-admin@example.test'],['name'=>'Admin','password'=>'unused'])->createToken('qa-admin')->plainTextToken;
    }
    private function customer(): string { return KhachHang::first()->createToken('qa-customer')->plainTextToken; }
    private function other(): KhachHang { return KhachHang::firstOrCreate(['email'=>'other-eng@example.test'],['ho_va_ten'=>'Other','mat_khau'=>'unused','role'=>'customer','is_khoa'=>0,'is_kich_hoat'=>1]); }
    private function coupon(): MaGiamGia
    {
        return MaGiamGia::create(['ma_code'=>'LOYAL'.Str::upper(Str::random(5)),'ten_giam_gia'=>'Tri ân','loai_giam'=>'phan_tram','gia_tri'=>10,'gia_tri_toi_thieu'=>0,'so_luong'=>10,'so_luong_da_dung'=>0,'gioi_han_moi_khach'=>1,'trang_thai'=>1]);
    }
    private function campaign(array $extra=[]): array
    {
        $payload=array_replace(['title'=>'Cảm ơn bạn','body'=>'Nội dung riêng từ SOFEP.','kind'=>'general','filters'=>['period'=>'all'],'request_key'=>(string)Str::uuid()],$extra);
        $preview=$this->api('POST','admin/chien-dich/xem-truoc',$this->admin(),$payload)->assertOk();
        $payload['preview_hash']=$preview->json('data.preview_hash');
        $created=$this->api('POST','admin/chien-dich',$this->admin(),$payload)->assertCreated();
        return [$payload,$created->json('data.id'),$preview->json('data.count')];
    }
    private function paid(int $customer,float $amount,string $created,string $status='da_giao',string $payment='da_thanh_toan'): void
    {
        DB::table('don_hangs')->insert(['id_khach_hang'=>$customer,'ma_don_hang'=>'QA_'.Str::uuid(),'tong_thanh_toan'=>$amount,'trang_thai_don_hang'=>$status,'trang_thai_thanh_toan'=>$payment,'created_at'=>$created,'updated_at'=>$created]);
    }
    private function basket(MaGiamGia $coupon): array
    {
        return ['ho_ten'=>'Test only','so_dien_thoai'=>'0900000000','dia_chi_giao'=>'Isolated fixture','phuong_thuc_thanh_toan'=>'COD','ma_giam_gia_su_dung'=>$coupon->ma_code,'tong_thanh_toan'=>1,'items'=>[['id'=>1,'id_chi_tiet_san_pham'=>50,'quantity'=>1,'gia_ban'=>1]]];
    }
    public function test_guest_feedback_is_private_and_images_require_admin(): void
    {
        $png=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');
        $image=UploadedFile::fake()->createWithContent('feedback.png',$png);
        $result=$this->api('POST','client/gop-y',null,['topic'=>'website','body'=>'<script>alert(1)</script> Góp ý riêng.','images'=>[$image]])->assertCreated();
        $id=$result->json('reference');
        $this->api('GET','admin/gop-y/'.$id,$this->customer())->assertForbidden();
        $detail=$this->api('GET','admin/gop-y/'.$id,$this->admin())->assertOk();
        $attachment=$detail->json('images.0.id');
        $this->api('GET',"admin/gop-y/$id/anh/$attachment")->assertUnauthorized();
        $this->api('GET',"admin/gop-y/$id/anh/$attachment",$this->customer())->assertForbidden();
        $this->api('GET',"admin/gop-y/$id/anh/$attachment",$this->admin())->assertOk();
        $this->api('PATCH','admin/gop-y/'.$id,$this->admin(),['status'=>'processing','internal_note'=>'Riêng cho Admin'])->assertOk();
        $this->assertSame('processing',DB::table('private_feedback')->value('status'));
        $this->api('GET','client/gop-y/'.$id,$this->customer())->assertNotFound();
        $this->api('POST','client/gop-y',null,['topic'=>'website','body'=>'Góp ý với ảnh không hợp lệ.','images'=>[UploadedFile::fake()->createWithContent('bad.svg','<svg><script>1</script></svg>')]])->assertUnprocessable();
    }
    public function test_feedback_spam_honeypot_and_file_count_are_rejected(): void
    {
        $this->api('POST','client/gop-y',null,['topic'=>'other','body'=>'Nội dung đủ dài.','website'=>'https://spam.test'])->assertUnprocessable();
        $this->api('POST','client/gop-y',null,['topic'=>'other','body'=>'Nội dung đủ dài.','images'=>array_fill(0,6,UploadedFile::fake()->createWithContent('bad.txt','not image'))])->assertUnprocessable();
        for($i=0;$i<3;$i++) $this->api('POST','client/gop-y',null,['topic'=>'service','body'=>'Góp ý đủ dài và hợp lệ.'])->assertCreated();
        $this->api('POST','client/gop-y',null,['topic'=>'service','body'=>'Lần gửi vượt giới hạn.'])->assertTooManyRequests();
    }
    public function test_customer_feedback_reply_is_private_in_app_and_email_is_disabled(): void
    {
        $token=$this->customer();
        $id=$this->api('POST','client/gop-y',$token,['topic'=>'product','body'=>'Góp ý sản phẩm cần cải thiện.'])->assertCreated()->json('reference');
        $this->assertEquals(1,DB::table('private_feedback')->value('customer_id'));
        foreach([1,2] as $_) $this->api('POST',"admin/gop-y/$id/phan-hoi",$this->admin(),['body'=>'Cảm ơn bạn đã góp ý.','channel'=>'in_app'])->assertOk();
        $this->assertSame(1,DB::table('customer_notifications')->count());
        $this->api('GET','client/thong-bao',$this->other()->createToken('other')->plainTextToken)->assertJsonCount(0,'data.data');
        $this->api('POST',"admin/gop-y/$id/phan-hoi",$this->admin(),['body'=>'Không gửi email thật.','channel'=>'email'])->assertUnprocessable();
    }
    public function test_audiences_use_vietnam_periods_and_completed_paid_orders_only(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-11-06 10:00:00','UTC'));
        $b=$this->other();
        foreach([['2026-11-06 00:00:00',100],['2026-10-31 17:00:00',200],['2026-01-01 00:00:00',300]] as [$date,$amount]) $this->paid(1,$amount,$date);
        $this->paid($b->id,400,'2026-11-01 16:59:59');
        $this->paid($b->id,50000,'2026-11-04 00:00:00','da_huy');
        $this->paid($b->id,50000,'2026-11-04 00:00:00','da_giao','chua_thanh_toan');
        $this->paid($b->id,50000,'2026-11-04 00:00:00','da_giao','hoan_tien');
        $service=app(LoyaltyAudience::class);
        foreach([['week',1,150],['month',2,350],['year',3,650]] as [$period,$orders,$max]){
            $ids=$service->query(['period'=>$period,'orders'=>['min'=>$orders],'spend'=>['max'=>$max]])->pluck('id')->all();
            $this->assertEquals([1],$ids);
        }
        $this->assertTrue($service->matches($b->id,['period'=>'week','orders'=>['max'=>0]]));
        $this->travelBack();
    }
    public function test_preview_snapshot_delivery_preferences_and_retry_are_consistent(): void
    {
        $b=$this->other();DB::table('customer_preferences')->insert(['customer_id'=>$b->id,'marketing_enabled'=>false]);
        [$payload,$id,$count]=$this->campaign();$this->assertEquals(2,$count);
        $this->api('POST','admin/chien-dich',$this->admin(),$payload)->assertCreated()->assertJsonPath('data.id',$id);
        $this->assertEquals($count,DB::table('campaign_recipients')->where('campaign_id',$id)->count());
        $service=app(CustomerCampaigns::class);$this->assertEquals(2,$service->deliverDue());$this->assertEquals(0,$service->deliverDue());
        $this->assertSame(2,DB::table('customer_notifications')->count());
        [,,$marketing]=$this->campaign(['kind'=>'promotion']);$this->assertSame(1,$marketing);
        $this->api('GET','admin/chien-dich',$this->admin())->assertOk();
    }
    public function test_changed_preview_requires_reconfirmation_and_scheduled_delivery_waits(): void
    {
        $payload=['title'=>'Schedule','body'=>'Private body','kind'=>'general','filters'=>['period'=>'all'],'request_key'=>(string)Str::uuid()];
        $preview=$this->api('POST','admin/chien-dich/xem-truoc',$this->admin(),$payload)->assertOk();
        $this->other();$payload['preview_hash']=$preview->json('data.preview_hash');
        $this->api('POST','admin/chien-dich',$this->admin(),$payload)->assertStatus(409);
        [$scheduled,$id]=$this->campaign(['send_at'=>now('Asia/Ho_Chi_Minh')->addDay()->format('Y-m-d\TH:i')]);
        $this->assertEquals(0,app(CustomerCampaigns::class)->deliverDue());
        $this->travel(2)->days();$this->assertEquals(2,app(CustomerCampaigns::class)->deliverDue());$this->travelBack();
    }
    public function test_private_voucher_grants_wallet_and_notifications_are_owned(): void
    {
        $b=$this->other();$this->paid(1,1000,now()->format('Y-m-d H:i:s'));
        $coupon=$this->coupon();[,,$count]=$this->campaign(['kind'=>'voucher','voucher_id'=>$coupon->id,'filters'=>['period'=>'all','orders'=>['min'=>1]]]);
        $this->assertSame(1,$count);app(CustomerCampaigns::class)->deliverDue();
        $aToken=$this->customer();$bToken=$b->createToken('other')->plainTextToken;
        $notification=DB::table('customer_notifications')->first();
        $this->api('GET','client/thong-bao?customer_id=1',$bToken)->assertJsonCount(0,'data.data');
        $this->api('POST','client/thong-bao/'.$notification->id.'/da-doc',$bToken)->assertNotFound();
        $this->api('POST','client/vi-uu-dai/'.$coupon->id.'/luu',$bToken)->assertUnprocessable();
        $this->api('POST','client/kiem-tra-voucher',$bToken,$this->basket($coupon))->assertUnprocessable();
        foreach([1,2] as $_) $this->api('POST','client/vi-uu-dai/'.$coupon->id.'/luu',$aToken)->assertOk();
        $this->assertSame(1,DB::table('saved_vouchers')->count());$this->assertEquals(0,$coupon->fresh()->so_luong_da_dung);
        $this->api('GET','client/vi-uu-dai',$bToken)->assertJsonCount(0,'data.data');
        $this->api('GET','client/vi-uu-dai',$aToken)->assertJsonPath('data.data.0.ma_code',$coupon->ma_code)->assertJsonPath('data.data.0.state','available');
        $this->api('POST','client/thong-bao/'.$notification->id.'/da-doc',$aToken)->assertOk();
        $this->api('GET','client/thong-bao/count',$aToken)->assertJsonPath('unread',0);
        $this->api('POST','client/kiem-tra-voucher',null,$this->basket($coupon))->assertUnprocessable();
    }
    public function test_rules_are_rechecked_only_for_grants_that_require_it(): void
    {
        $this->paid(1,1000,now()->format('Y-m-d H:i:s'));
        foreach([false,true] as $recheck){
            $coupon=$this->coupon();$this->campaign(['kind'=>'voucher','voucher_id'=>$coupon->id,'filters'=>['orders'=>['min'=>1]],'recheck'=>$recheck]);app(CustomerCampaigns::class)->deliverDue();
        }
        DB::table('don_hangs')->update(['trang_thai_don_hang'=>'da_huy']);
        $coupons=MaGiamGia::orderBy('id')->get();
        $this->api('POST','client/vi-uu-dai/'.$coupons[0]->id.'/luu',$this->customer())->assertOk();
        $this->api('POST','client/vi-uu-dai/'.$coupons[1]->id.'/luu',$this->customer())->assertUnprocessable();
    }
    public function test_checkout_reprices_private_coupon_and_cancel_restores_usage_once(): void
    {
        $coupon=$this->coupon();$this->campaign(['kind'=>'voucher','voucher_id'=>$coupon->id]);app(CustomerCampaigns::class)->deliverDue();
        $token=$this->customer();$body=$this->basket($coupon);
        $order=$this->api('POST','client/dat-hang',$token,$body)->assertCreated()->assertJsonPath('don_hang.tong_thanh_toan',30090)->assertJsonPath('don_hang.tien_giam_gia',10);
        $this->api('POST','client/kiem-tra-voucher',$token,$body)->assertUnprocessable();
        $this->assertEquals(1,$coupon->fresh()->so_luong_da_dung);
        foreach([1,2] as $_) $this->api('POST','admin/don-hang/update-status',$this->admin(),['id'=>$order->json('don_hang.id'),'trang_thai_don_hang'=>'da_huy'])->assertOk();
        $this->assertEquals(0,$coupon->fresh()->so_luong_da_dung);$this->assertEquals(4,DB::table('chi_tiet_san_phams')->where('id',50)->value('so_luong_ton'));
    }
    public function test_expired_future_exhausted_and_wrong_audience_vouchers_are_rejected(): void
    {
        $token=$this->customer();$coupon=$this->coupon();
        foreach([['ngay_ket_thuc'=>now('Asia/Ho_Chi_Minh')->subDay()->toDateString()],['ngay_ket_thuc'=>null,'ngay_bat_dau'=>now('Asia/Ho_Chi_Minh')->addDay()->toDateString()],['ngay_bat_dau'=>null,'so_luong'=>0],['so_luong'=>10,'doi_tuong'=>'selected','ids_khach_hang'=>[9999]]] as $change){
            $coupon->update($change);$this->api('POST','client/vi-uu-dai/'.$coupon->id.'/luu',$token)->assertUnprocessable();
        }
    }
    public function test_migration_rollback_preserves_existing_catalog_and_history(): void
    {
        $before=DB::table('san_phams')->count();
        $migration=require database_path('migrations/2026_11_06_000001_create_customer_engagement_tables.php');
        $migration->down();$this->assertFalse(Schema::hasTable('private_feedback'));$this->assertSame($before,DB::table('san_phams')->count());
        $this->api('GET','admin/gop-y',$this->customer())->assertForbidden();
        $this->api('GET','admin/gop-y')->assertUnauthorized();
        $this->api('POST','client/gop-y',null,['topic'=>'other','body'=>'Chưa có schema.'])->assertStatus(503);
        $migration->up();$this->assertTrue(Schema::hasTable('private_feedback'));
    }

    public function test_coupon_audience_is_intersected_with_campaign_preview_and_grant_list(): void
    {
        $b=$this->other();$coupon=$this->coupon();$coupon->update(['doi_tuong'=>'selected','ids_khach_hang'=>[1]]);
        [,,$count]=$this->campaign(['kind'=>'voucher','voucher_id'=>$coupon->id]);$this->assertSame(1,$count);
        app(CustomerCampaigns::class)->deliverDue();
        $this->assertFalse(DB::table('voucher_grants')->where('customer_id',$b->id)->exists());
        $this->api('POST','admin/ma-giam-gia/destroy',$this->admin(),['id'=>$coupon->id])->assertStatus(409);
    }
    public function test_delivery_checks_latest_marketing_preference_and_read_all_is_owned(): void
    {
        $b=$this->other();$this->campaign(['kind'=>'promotion']);
        $this->api('PUT','client/tuy-chon-thong-bao',$b->createToken('optout')->plainTextToken,['marketing_enabled'=>false])->assertOk();
        $this->assertEquals(1,app(CustomerCampaigns::class)->deliverDue());
        $this->assertEquals(1,DB::table('campaign_recipients')->where('status','skipped')->count());
        $this->api('POST','client/thong-bao/doc-tat-ca',$this->customer())->assertOk();
        $this->api('GET','client/thong-bao/count',$this->customer())->assertJsonPath('unread',0);
    }

    public function test_customer_center_endpoints_reject_guest_admin_and_legacy_seller(): void
    {
        $seller=KhachHang::create(['email'=>'legacy-eng@example.test','ho_va_ten'=>'Legacy','mat_khau'=>'unused','role'=>'seller','is_kich_hoat'=>1,'is_khoa'=>0])->createToken('legacy')->plainTextToken;
        foreach([['GET','client/thong-bao'],['GET','client/thong-bao/count'],['POST','client/thong-bao/doc-tat-ca'],['POST','client/thong-bao/1/da-doc'],['GET','client/vi-uu-dai'],['POST','client/vi-uu-dai/1/luu'],['PUT','client/tuy-chon-thong-bao']] as [$method,$path]){
            $this->api($method,$path)->assertUnauthorized();
            foreach([$this->admin(),$seller] as $token) $this->api($method,$path,$token)->assertForbidden();
        }
    }
    public function test_retry_of_failed_recipient_does_not_duplicate_an_existing_notification(): void
    {
        $this->campaign();$service=app(CustomerCampaigns::class);$service->deliverDue();
        DB::table('campaign_recipients')->update(['status'=>'failed','attempts'=>1]);
        $service->deliverDue();$this->assertSame(1,DB::table('customer_notifications')->count());
        $this->assertSame(1,DB::table('campaign_recipients')->where('status','delivered')->count());
    }
    public function test_campaign_rejects_voucher_that_expires_before_scheduled_send(): void
    {
        $coupon=$this->coupon();$coupon->update(['ngay_ket_thuc'=>now('Asia/Ho_Chi_Minh')->toDateString()]);
        $this->api('POST','admin/chien-dich/xem-truoc',$this->admin(),['title'=>'Expiry','body'=>'Scheduled UAT','kind'=>'voucher','voucher_id'=>$coupon->id,'send_at'=>now('Asia/Ho_Chi_Minh')->addDays(2)->format('Y-m-d\TH:i')])->assertUnprocessable();
    }

    public function test_customer_who_loses_eligibility_before_delivery_does_not_receive_grant(): void
    {
        $this->paid(1,1000,now()->format('Y-m-d H:i:s'));
        $coupon=$this->coupon();$this->campaign(['kind'=>'voucher','voucher_id'=>$coupon->id,'filters'=>['orders'=>['min'=>1]],'recheck'=>false]);
        DB::table('don_hangs')->update(['trang_thai_don_hang'=>'da_huy']);
        $this->assertSame(0,app(CustomerCampaigns::class)->deliverDue());
        $this->assertSame(0,DB::table('voucher_grants')->count());
        $this->assertSame(0,DB::table('customer_notifications')->count());
    }

    public function test_optional_feedback_phone_requires_real_digits(): void
    {
        $this->api('POST','client/gop-y',null,['topic'=>'service','body'=>'Góp ý riêng có số liên hệ.','phone'=>'++++++++++'])->assertUnprocessable()->assertJsonValidationErrors('phone');
        $this->api('POST','client/gop-y',null,['topic'=>'service','body'=>'Góp ý riêng có số liên hệ.','phone'=>'+84 900 000 000'])->assertCreated();
    }
}
