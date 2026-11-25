<?php
namespace App\Services;
use App\Models\{MaGiamGia,KhachHang,DonHang};
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class VoucherWallet
{
    public function describe(MaGiamGia $voucher, KhachHang $customer): array
    {
        $data=$voucher->only(['id','ma_code','ten_giam_gia','mo_ta','loai_giam','gia_tri','giam_toi_da','gia_tri_toi_thieu','ngay_bat_dau','ngay_ket_thuc','so_luong','so_luong_da_dung','gioi_han_moi_khach','trang_thai']);
        $data['used_count']=DonHang::where('id_khach_hang',$customer->id)->where('ma_giam_gia_su_dung',$voucher->ma_code)->where('trang_thai_don_hang','!=','da_huy')->count();
        $data['state']='available'; $data['reason']='Có thể chọn mã; giá trị giỏ hàng sẽ được kiểm tra tại checkout.';
        try { $voucher->assertCustomer($customer); $voucher->discountFor(max((float)$voucher->gia_tri_toi_thieu,1)); }
        catch(ValidationException $e) {
            $data['reason']=collect($e->errors())->flatten()->first();
            $data['state']=$voucher->ngay_ket_thuc && now('Asia/Ho_Chi_Minh')->toDateString()>$voucher->ngay_ket_thuc ? 'expired'
                : ($voucher->gioi_han_moi_khach && $data['used_count'] >= $voucher->gioi_han_moi_khach ? 'used' : 'ineligible');
        }
        $data['conditions']=($voucher->campaign_restricted ? 'Chỉ khách được cấp mã. ' : '')
            .match ($voucher->doi_tuong ?: 'all') {'new'=>'Chỉ khách chưa có đơn còn hiệu lực. ', 'selected'=>'Áp dụng cho Customer được chọn. ', default=>'Theo điều kiện tài khoản hợp lệ. '}
            .'Đơn tối thiểu '.number_format((float)$voucher->gia_tri_toi_thieu,0,',','.').'đ; '
            .($voucher->gioi_han_moi_khach ? 'tối đa '.$voucher->gioi_han_moi_khach.' lượt/khách.' : 'theo số lượt còn lại.');
        if ($voucher->campaign_restricted) {
            $data['grant_conditions'] = DB::table('voucher_grants')->where('voucher_id',$voucher->id)->where('customer_id',$customer->id)->get(['filters','recheck'])->map(function ($grant) {
                $filters=json_decode($grant->filters ?: '{}',true);
                $parts=['Kỳ: '.(['all'=>'tất cả thời gian','week'=>'tuần hiện tại','month'=>'tháng hiện tại','year'=>'năm hiện tại'][$filters['period'] ?? 'all'])];
                foreach(['orders'=>'Số đơn hoàn tất','spend'=>'Thực chi'] as $key=>$label) {
                    if(isset($filters[$key]['min'])) $parts[]=$label.' ≥ '.number_format($filters[$key]['min'],0,',','.');
                    if(isset($filters[$key]['max'])) $parts[]=$label.' ≤ '.number_format($filters[$key]['max'],0,',','.');
                }
                return implode('; ',$parts).($grant->recheck ? '. Kiểm tra lại điều kiện khi lưu/checkout.' : '. Cấp theo điều kiện tại thời điểm giao mã.');
            })->unique()->values()->all();
        } else $data['grant_conditions']=[];
        return $data;
    }
}
