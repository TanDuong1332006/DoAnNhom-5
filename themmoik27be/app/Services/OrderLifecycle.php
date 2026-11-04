<?php

namespace App\Services;

use App\Models\{DonHang, SanPham, ChiTietSanPham, MaGiamGia, HoaDon};
use Illuminate\Support\Facades\DB;

class OrderLifecycle
{
    public function update(int $id, string $status, ?string $payment = null): DonHang
    {
        return DB::transaction(function () use ($id, $status, $payment) {
            $order = DonHang::whereKey($id)->lockForUpdate()->firstOrFail();
            $allowed = [
                'cho_xac_nhan' => ['da_xac_nhan', 'da_huy'],
                'da_xac_nhan' => ['dang_giao', 'da_huy'],
                'dang_giao' => ['da_giao'], 'da_giao' => [], 'da_huy' => [],
            ];
            abort_unless($status === $order->trang_thai_don_hang || in_array($status, $allowed[$order->trang_thai_don_hang] ?? [], true), 409, 'Không thể chuyển trạng thái đơn hàng theo yêu cầu.');
            abort_if($payment && $payment !== $order->trang_thai_thanh_toan && $order->phuong_thuc_thanh_toan !== 'COD', 409, 'Đơn mô phỏng phải thanh toán qua chức năng mô phỏng.');
            abort_if($payment === 'chua_thanh_toan' && $order->trang_thai_thanh_toan === 'da_thanh_toan', 409, 'Không thể bỏ ghi nhận thanh toán đã có.');
            abort_if($payment === 'hoan_tien' && $order->trang_thai_thanh_toan !== 'hoan_tien', 409, 'Hoàn tiền cần quy trình riêng.');
            if ($status === 'da_huy' && $order->trang_thai_don_hang !== 'da_huy') {
                abort_if($order->trang_thai_thanh_toan !== 'chua_thanh_toan' || ($payment && $payment !== 'chua_thanh_toan'), 409, 'Đơn đã thanh toán cần xử lý hoàn tiền riêng trước khi hủy.');
                abort_unless($order->ton_kho_da_giu, 409, 'Đơn lịch sử chưa xác minh giữ kho; không tự hoàn tồn kho.');
                foreach ($order->chiTiets()->orderBy('id_san_pham')->orderBy('id_chi_tiet_san_pham')->get() as $line) {
                    SanPham::whereKey($line->id_san_pham)->lockForUpdate()->firstOrFail();
                    $stock = $line->id_chi_tiet_san_pham
                        ? ChiTietSanPham::whereKey($line->id_chi_tiet_san_pham)->where('id_san_pham', $line->id_san_pham)->lockForUpdate()->firstOrFail()
                        : SanPham::whereKey($line->id_san_pham)->firstOrFail();
                    $stock->increment('so_luong_ton', $line->so_luong);
                }
                if ($order->voucher_da_giu && $order->ma_giam_gia_su_dung) {
                    $coupon = MaGiamGia::where('ma_code', $order->ma_giam_gia_su_dung)->lockForUpdate()->firstOrFail();
                    if ($coupon->so_luong_da_dung > 0) $coupon->decrement('so_luong_da_dung');
                }
                $order->ton_kho_da_giu = false; $order->voucher_da_giu = false;
            }
            $order->trang_thai_don_hang = $status;
            if ($payment) $order->trang_thai_thanh_toan = $payment;
            abort_if($status === 'da_giao' && $order->trang_thai_thanh_toan !== 'da_thanh_toan', 409, 'Chỉ hoàn tất đơn sau khi ghi nhận thanh toán.');
            $order->save();
            if ($payment === 'da_thanh_toan') HoaDon::where('ma_hoa_don', $order->ma_don_hang)->update(['trang_thai' => 1]);
            return $order;
        }, 3);
    }
}
