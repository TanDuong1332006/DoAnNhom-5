<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChiTietSanPham extends Model
{
    use HasFactory;

    protected $table = 'chi_tiet_san_phams';

    protected $fillable = [
        'id_san_pham',
        'id_mau_sac',
        'id_kich_thuoc',
        'so_luong_ton',
        'gia_chenh_lech',
        'trang_thai',
    ];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'id_san_pham');
    }

    public function mauSac()
    {
        return $this->belongsTo(MauSac::class, 'id_mau_sac');
    }

    public function kichThuoc()
    {
        return $this->belongsTo(KichThuoc::class, 'id_kich_thuoc');
    }
}
