<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThuongHieu extends Model
{
    use HasFactory;

    protected $table = 'thuong_hieus';

    protected $fillable = [
        'ten_thuong_hieu',
        'slug_thuong_hieu',
        'hinh_anh',
        'mo_ta',
        'trang_thai',
    ];

    public function sanPhams()
    {
        return $this->hasMany(SanPham::class, 'id_thuong_hieu');
    }
}
