<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class KhachHang extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'khach_hangs';

    protected $fillable = [
        'ho_va_ten',
        'email',
        'so_dien_thoai',
        'mat_khau',
        'dia_chi',
        'ngay_sinh',
        'gioi_tinh',
        'avatar',
        'is_kich_hoat',
        'is_khoa',
    ];

    protected $hidden = [
        'mat_khau',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        return $this->mat_khau;
    }

    public function donHangs()
    {
        return $this->hasMany(DonHang::class, 'id_khach_hang');
    }

    public function diaChis()
    {
        return $this->hasMany(DiaChiNhanHang::class, 'id_khach_hang');
    }

    public function gioHang()
    {
        return $this->hasOne(GioHang::class, 'id_khach_hang');
    }
}
