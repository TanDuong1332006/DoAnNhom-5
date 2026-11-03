<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SanPham extends Model
{
    use HasFactory;

    protected $table = 'san_phams';

    protected $fillable = [
        'id_danh_muc',
        'id_thuong_hieu',
        'ma_san_pham',
        'ten_san_pham',
        'slug_san_pham',
        'gia_ban',
        'gia_khuyen_mai',
        'hinh_anh',
        'mo_ta_ngan',
        'mo_ta_chi_tiet',
        'so_luong_ton',
        'is_noi_bat',
        'is_flash_sale',
        'luot_xem',
        'trang_thai',
    ];

    public function danhMuc()
    {
        return $this->belongsTo(DanhMuc::class, 'id_danh_muc');
    }

    public function thuongHieu()
    {
        return $this->belongsTo(ThuongHieu::class, 'id_thuong_hieu');
    }

    public function chiTietSanPhams()
    {
        return $this->hasMany(ChiTietSanPham::class, 'id_san_pham');
    }

    public function anhSanPhams()
    {
        return $this->hasMany(AnhSanPham::class, 'id_san_pham');
    }

    public function danhGias()
    {
        return $this->hasMany(DanhGiaSanPham::class, 'id_san_pham');
    }
}
