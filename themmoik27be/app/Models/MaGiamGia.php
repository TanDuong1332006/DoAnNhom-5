<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaGiamGia extends Model
{
    use HasFactory;

    protected $casts = ['ids_khach_hang' => 'array'];

    public function assertCustomer(?KhachHang $customer): void
    {
        $audience = $this->doi_tuong ?: 'all';
        $limit = $this->gioi_han_moi_khach;
        $message = null;
        if (!in_array($audience, ['all', 'new', 'selected'], true)) $message = 'Đối tượng voucher không hợp lệ.';
        elseif (($audience !== 'all' || $limit !== null) && !$customer) $message = 'Vui lòng đăng nhập để dùng mã này.';
        elseif ($audience === 'selected' && !in_array($customer->id, array_map('intval', $this->ids_khach_hang ?? []), true)) $message = 'Mã không áp dụng cho tài khoản của bạn.';
        elseif ($audience === 'new' && DonHang::where('id_khach_hang', $customer->id)->where('trang_thai_don_hang', '!=', 'da_huy')->exists()) $message = 'Mã chỉ áp dụng cho khách chưa có đơn hàng còn hiệu lực.';
        elseif ($limit !== null && DonHang::where('id_khach_hang', $customer->id)->where('ma_giam_gia_su_dung', $this->ma_code)->where('trang_thai_don_hang', '!=', 'da_huy')->count() >= $limit) $message = 'Bạn đã dùng hết lượt mã giảm giá này.';
        if ($message) throw \Illuminate\Validation\ValidationException::withMessages(['ma_giam_gia_su_dung' => $message]);
    }

    public function discountFor(float $subtotal): float
    {
        $message = match (true) {
            (int) $this->trang_thai !== 1 => 'Mã giảm giá đang tạm khóa.',
            $this->ngay_bat_dau && now()->toDateString() < $this->ngay_bat_dau => 'Mã giảm giá chưa đến ngày sử dụng.',
            $this->ngay_ket_thuc && now()->toDateString() > $this->ngay_ket_thuc => 'Mã giảm giá đã hết hạn.',
            $this->so_luong_da_dung >= $this->so_luong => 'Mã giảm giá đã hết lượt.',
            $subtotal < $this->gia_tri_toi_thieu => 'Đơn hàng chưa đạt giá trị tối thiểu.',
            !in_array($this->loai_giam, ['phan_tram', 'co_dinh'], true) || $this->gia_tri < 0 || ($this->loai_giam === 'phan_tram' && $this->gia_tri > 100) => 'Cấu hình mã giảm giá không hợp lệ.',
            default => null,
        };
        if ($message) throw \Illuminate\Validation\ValidationException::withMessages(['ma_giam_gia_su_dung' => $message]);
        $discount = $this->loai_giam === 'phan_tram' ? $subtotal * $this->gia_tri / 100 : $this->gia_tri;
        if ($this->giam_toi_da !== null) $discount = min($discount, max(0, $this->giam_toi_da));
        return round(max(0, min($subtotal, $discount)), 2);
    }

    protected $table = 'ma_giam_gias';

    protected $fillable = [
        'ma_code',
        'ten_giam_gia',
        'loai_giam',
        'gia_tri',
        'gia_tri_toi_thieu',
        'giam_toi_da',
        'so_luong',
        'so_luong_da_dung',
        'ngay_bat_dau',
        'ngay_ket_thuc',
        'trang_thai',
        'doi_tuong', 'gioi_han_moi_khach', 'ids_khach_hang',
    ];
}
