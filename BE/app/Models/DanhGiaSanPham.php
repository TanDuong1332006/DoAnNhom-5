<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanhGiaSanPham extends Model
{
    use HasFactory;

    protected $table = 'danh_gia_san_phams';

    protected $fillable = [
        'id_san_pham',
        'id_khach_hang',
        'so_sao',
        'tieu_de',
        'noi_dung',
        'trang_thai',
    ];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'id_san_pham');
    }

    public function khachHang()
    {
        return $this->belongsTo(KhachHang::class, 'id_khach_hang');
    }
}
