<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dia_chi_nhan_hangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_khach_hang')->constrained('khach_hangs')->onDelete('cascade');
            $table->string('ten_nguoi_nhan');
            $table->string('so_dien_thoai');
            $table->string('dia_chi_chi_tiet');
            $table->string('phuong_xa')->nullable();
            $table->string('quan_huyen')->nullable();
            $table->string('tinh_thanh')->nullable();
            $table->integer('is_mac_dinh')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dia_chi_nhan_hangs');
    }
};
