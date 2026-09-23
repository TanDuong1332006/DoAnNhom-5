<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KichThuoc extends Model
{
    use HasFactory;

    protected $table = 'kich_thuocs';

    protected $fillable = [
        'ten_kich_thuoc',
        'mo_ta',
        'trang_thai',
    ];

    public function chiTietSanPhams()
    {
        return $this->hasMany(ChiTietSanPham::class, 'id_kich_thuoc');
    }
}
