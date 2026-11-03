<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiaChiNhanHang extends Model
{
    use HasFactory;

    protected $table = 'dia_chi_nhan_hangs';

    protected $fillable = [
        'id_khach_hang',
        'ten_nguoi_nhan',
        'so_dien_thoai',
        'dia_chi_chi_tiet',
        'phuong_xa',
        'quan_huyen',
        'tinh_thanh',
        'is_mac_dinh',
    ];

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'id_khach_hang');
    }
}
