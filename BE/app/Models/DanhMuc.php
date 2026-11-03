<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DanhMuc extends Model
{
    use HasFactory;

    protected $table = 'danh_mucs';

    protected $fillable = [
        'ten_danh_muc',
        'slug_danh_muc',
        'hinh_anh',
        'mo_ta',
        'trang_thai',
    ];

    public function sanPhams()
    {
        return $this->hasMany(SanPham::class, 'id_danh_muc');
    }
}
