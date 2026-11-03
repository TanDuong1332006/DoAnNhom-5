<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonHang extends Model
{
    use HasFactory;

    protected $table = 'don_hangs';

    protected $fillable = [
        'id_khach_hang',
        'ma_don_hang',
        'ho_ten',
        'so_dien_thoai',
        'email',
        'dia_chi_giao',
        'tong_tien_hang',
        'phi_van_chuyen',
        'tien_giam_gia',
        'ma_giam_gia_su_dung',
        'tong_thanh_toan',
        'phuong_thuc_thanh_toan',
        'trang_thai_thanh_toan',
        'trang_thai_don_hang',
        'ghi_chu',
    ];

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'id_khach_hang');
    }

    public function chiTiets()
    {
        return $this->hasMany(ChiTietDonHang::class, 'id_don_hang');
    }
}
