<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('khach_hangs', function (Blueprint $table) {
            $table->id();
            $table->string('ho_va_ten');
            $table->string('email')->unique();
            $table->string('so_dien_thoai')->nullable();
            $table->string('mat_khau');
            $table->string('dia_chi')->nullable();
            $table->date('ngay_sinh')->nullable();
            $table->integer('gioi_tinh')->default(1); // 1: Nam, 2: Nữ, 3: Khác
            $table->string('avatar')->nullable();
            $table->integer('is_kich_hoat')->default(1);
            $table->integer('is_khoa')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('khach_hangs');
    }
};
