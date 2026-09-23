<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('don_hangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_khach_hang')->nullable()->constrained('khach_hangs')->onDelete('set null');
            $table->string('ma_don_hang')->unique();
            $table->string('ho_ten');
            $table->string('so_dien_thoai');
            $table->string('email')->nullable();
            $table->text('dia_chi_giao');
            $table->decimal('tong_tien_hang', 15, 2);
            $table->decimal('phi_van_chuyen', 15, 2)->default(0);
            $table->decimal('tien_giam_gia', 15, 2)->default(0);
            $table->string('ma_giam_gia_su_dung')->nullable();
            $table->decimal('tong_thanh_toan', 15, 2);
            $table->string('phuong_thuc_thanh_toan')->default('COD'); // COD, VNPAY, MOMO, NGAN_HANG
            $table->string('trang_thai_thanh_toan')->default('chua_thanh_toan'); // chua_thanh_toan, da_thanh_toan, hoan_tien
            $table->string('trang_thai_don_hang')->default('cho_xac_nhan'); // cho_xac_nhan, da_xac_nhan, dang_giao, da_giao, da_huy
            $table->text('ghi_chu')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('don_hangs');
    }
};
