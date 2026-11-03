<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoaDon extends Model
{
    use HasFactory;

    protected $table = 'hoa_dons';

    protected $fillable = [
        'ma_hoa_don',
        'tong_tien_hang',
        'giam_gia',
        'phi_van_chuyen',
        'tong_tien',
        'trang_thai',
        'phuong_thuc_thanh_toan',
        'ghi_chu',
    ];
}
