<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CuaHang extends Model
{
    use HasFactory;

    protected $table = 'cua_hangs';

    protected $fillable = [
        'id_chu_shop',
        'ten_cua_hang',
        'mo_ta',
        'logo',
        'dia_chi',
        'so_dien_thoai',
        'email_lien_he',
        'trang_thai',
    ];

    public function chuShop()
    {
        return $this->belongsTo(KhachHang::class, 'id_chu_shop');
    }
}
