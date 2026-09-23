<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaGiamGia extends Model
{
    use HasFactory;

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
    ];
}
