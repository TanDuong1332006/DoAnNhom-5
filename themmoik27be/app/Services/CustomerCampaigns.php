<?php
namespace App\Services;

use App\Models\{KhachHang, MaGiamGia, DonHang};
use Illuminate\Support\Facades\DB;
use Carbon\CarbonImmutable;

class CustomerCampaigns
{
    public function preview(array $data): array
    {
        $voucher = !empty($data['voucher_id']) ? MaGiamGia::findOrFail($data['voucher_id']) : null;
        $query = app(LoyaltyAudience::class)->query($data['filters'] ?? [], $data['kind'] !== 'general');
        if ($voucher) {
            abort_unless($voucher->trang_thai == 1 && $voucher->so_luong_da_dung < $voucher->so_luong, 422, 'Voucher đang tạm khóa hoặc đã hết lượt.');
            $deliveryDate = empty($data['send_at']) ? CarbonImmutable::now('Asia/Ho_Chi_Minh')->toDateString() : CarbonImmutable::parse($data['send_at'], 'Asia/Ho_Chi_Minh')->toDateString();
            abort_if($voucher->ngay_ket_thuc && $deliveryDate > $voucher->ngay_ket_thuc, 422, 'Voucher hết hạn trước thời điểm gửi.');
            $audience = $voucher->doi_tuong ?: 'all';
            abort_unless(in_array($audience, ['all','new','selected']), 422, 'Đối tượng voucher không hợp lệ.');
            if ($audience === 'selected') $query->whereIn('khach_hangs.id', $voucher->ids_khach_hang ?? []);
            if ($audience === 'new') $query->whereNotExists(fn ($q) => $q->selectRaw('1')->from('don_hangs')
                ->whereColumn('don_hangs.id_khach_hang','khach_hangs.id')->where('trang_thai_don_hang','!=','da_huy'));
            if ($voucher->gioi_han_moi_khach) $query->whereRaw('(SELECT COUNT(*) FROM don_hangs WHERE don_hangs.id_khach_hang=khach_hangs.id AND ma_giam_gia_su_dung=? AND trang_thai_don_hang<>?) < ?', [$voucher->ma_code,'da_huy',$voucher->gioi_han_moi_khach]);
        }
        $ids = $query->orderBy('khach_hangs.id')->pluck('id')->map(fn ($id) => (int) $id)->all();
        $hash = hash_hmac('sha256', json_encode([$ids, $data['filters'] ?? [], $data['kind'], $voucher?->getAttributes()]), config('app.key'));
        return ['count' => count($ids), 'preview_hash' => $hash, 'ids' => $ids];
    }
    public function create(array $data, int $admin): object
    {
        $fingerprint = hash('sha256', json_encode(collect($data)->except(['preview_hash'])->all()));
        try {
        return DB::transaction(function () use ($data, $admin, $fingerprint) {
            $existing = DB::table('customer_campaigns')->where('request_key', $data['request_key'])->lockForUpdate()->first();
            if ($existing) {
                abort_unless((int) $existing->admin_id === $admin, 409);
                abort_unless(hash_equals($existing->fingerprint, $fingerprint), 409, 'Khóa gửi đã dùng cho nội dung khác.');
                return $existing;
            }
            // Khóa voucher trước khi đóng băng tập người nhận và giới hạn mã.
            $voucher = !empty($data['voucher_id']) ? MaGiamGia::whereKey($data['voucher_id'])->lockForUpdate()->firstOrFail() : null;
            $preview = $this->preview($data);
            abort_unless(hash_equals($preview['preview_hash'], $data['preview_hash']), 409, 'Đối tượng/voucher đã thay đổi. Vui lòng xem trước lại.');
            if ($voucher) {
                abort_if(!$voucher->campaign_restricted && ($voucher->so_luong_da_dung > 0 || DonHang::where('ma_giam_gia_su_dung', $voucher->ma_code)->exists()), 409, 'Hãy chọn voucher chưa sử dụng để phát riêng.');
                abort_unless($voucher->trang_thai == 1, 422, 'Voucher đang tạm khóa.');
                $voucher->update(['campaign_restricted' => true]);
            }
            $sendAt = empty($data['send_at']) ? CarbonImmutable::now('UTC') : CarbonImmutable::parse($data['send_at'], 'Asia/Ho_Chi_Minh')->utc();
            $id = DB::table('customer_campaigns')->insertGetId([
                'request_key' => $data['request_key'], 'fingerprint' => $fingerprint, 'admin_id' => $admin,
                'title' => $data['title'], 'body' => $data['body'], 'kind' => $data['kind'], 'voucher_id' => $voucher?->id,
                'filters' => json_encode($data['filters'] ?? []), 'recheck' => $data['recheck'] ?? false,
                'send_at' => $sendAt->format('Y-m-d H:i:s'), 'status' => $preview['count'] ? 'queued' : 'completed',
                'recipient_count' => $preview['count'], 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach (array_chunk($preview['ids'], 500) as $chunk) DB::table('campaign_recipients')->insert(array_map(fn ($customer) => [
                'campaign_id' => $id, 'customer_id' => $customer, 'status' => 'pending', 'attempts' => 0, 'created_at' => now(), 'updated_at' => now(),
            ], $chunk));
            return DB::table('customer_campaigns')->find($id);
        }, 3);
        } catch (\Illuminate\Database\QueryException $error) {
            if ((string) $error->getCode() === '23000') {
                $existing = DB::table('customer_campaigns')->where('request_key', $data['request_key'])->first();
                if ($existing) {
                    abort_unless((int) $existing->admin_id === $admin && hash_equals($existing->fingerprint, $fingerprint), 409, 'Khóa gửi đã được sử dụng.');
                    return $existing;
                }
            }
            throw $error;
        }
    }
    // Durable outbox: lệnh/scheduler nhận tối đa một lô; khóa recipient và unique key chống retry/worker trùng.
    public function deliverDue(int $limit = 100): int
    {
        $ids = DB::table('campaign_recipients as r')->join('customer_campaigns as c','c.id','=','r.campaign_id')
            ->where('c.send_at','<=',now('UTC'))->whereIn('r.status',['pending','failed'])->where('r.attempts','<',3)
            ->orderBy('r.id')->limit(max(1,min($limit,500)))->pluck('r.id');
        $delivered = 0;
        foreach ($ids as $id) {
            try {
                $delivered += DB::transaction(function () use ($id) {
                    $r = DB::table('campaign_recipients')->where('id',$id)->lockForUpdate()->first();
                    if (!$r || !in_array($r->status,['pending','failed']) || $r->attempts >= 3) return 0;
                    $c = DB::table('customer_campaigns')->find($r->campaign_id);
                    $customer = KhachHang::whereKey($r->customer_id)->lockForUpdate()->first();
                    $optOut = $c->kind !== 'general' && DB::table('customer_preferences')->where('customer_id',$r->customer_id)->where('marketing_enabled',false)->exists();
                    if (!$customer || ($customer->role ?: 'customer') !== 'customer' || $customer->is_khoa || !$customer->is_kich_hoat || $optOut) {
                        DB::table('campaign_recipients')->where('id',$id)->update(['status'=>'skipped','updated_at'=>now()]); return 0;
                    }
                    $filters=json_decode($c->filters ?: '{}',true);
                    if ((!empty($filters['orders']) || !empty($filters['spend'])) && !app(LoyaltyAudience::class)->matches($customer->id,$filters)) {
                        DB::table('campaign_recipients')->where('id',$id)->update(['status'=>'skipped','updated_at'=>now()]); return 0;
                    }
                    if ($c->voucher_id) {
                        // Cùng thứ tự khóa với checkout: customer rồi voucher.
                        $voucher=MaGiamGia::whereKey($c->voucher_id)->lockForUpdate()->firstOrFail();
                        $usable=$voucher->trang_thai == 1 && $voucher->so_luong_da_dung < $voucher->so_luong
                            && (!$voucher->ngay_ket_thuc || now('Asia/Ho_Chi_Minh')->toDateString() <= $voucher->ngay_ket_thuc);
                        try { $voucher->assertBaseCustomer($customer); }
                        catch (\Illuminate\Validation\ValidationException $error) { $usable=false; }
                        if (!$usable) { DB::table('campaign_recipients')->where('id',$id)->update(['status'=>'skipped','updated_at'=>now()]); return 0; }
                        DB::table('voucher_grants')->insertOrIgnore(['voucher_id'=>$c->voucher_id,'customer_id'=>$customer->id,'campaign_id'=>$c->id,
                            'filters'=>$c->filters,'recheck'=>$c->recheck,'created_at'=>now(),'updated_at'=>now()]);
                    }
                    DB::table('customer_notifications')->insertOrIgnore(['customer_id'=>$customer->id,'delivery_key'=>'campaign:'.$id,
                        'campaign_id'=>$c->id,'title'=>$c->title,'body'=>$c->body,'kind'=>$c->kind,'voucher_id'=>$c->voucher_id,'created_at'=>now(),'updated_at'=>now()]);
                    DB::table('campaign_recipients')->where('id',$id)->update(['status'=>'delivered','attempts'=>$r->attempts+1,'error'=>null,'delivered_at'=>now(),'updated_at'=>now()]);
                    return 1;
                },3);
            } catch (\Throwable $error) {
                DB::table('campaign_recipients')->where('id',$id)->whereIn('status',['pending','failed'])->update([
                    'status'=>'failed','attempts'=>DB::raw('attempts + 1'),'error'=>'Không thể giao thông báo; thử lại tối đa 3 lần.','updated_at'=>now()]);
                report($error);
            }
        }
        foreach (DB::table('customer_campaigns')->where('status','queued')->pluck('id') as $id) {
            $pending = DB::table('campaign_recipients')->where('campaign_id',$id)->whereIn('status',['pending','failed'])->where('attempts','<',3)->exists();
            if (!$pending) DB::table('customer_campaigns')->where('id',$id)->update(['status'=>'completed','updated_at'=>now()]);
        }
        return $delivered;
    }
}
