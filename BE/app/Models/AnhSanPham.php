<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnhSanPham extends Model
{
    use HasFactory;

    protected $table = 'anh_san_phams';

    protected $fillable = [
        'id_san_pham',
        'duong_dan_anh',
        'thu_tu',
    ];

    public function sanPham()
    {
        return $this->belongsTo(SanPham::class, 'id_san_pham');
    }
}
