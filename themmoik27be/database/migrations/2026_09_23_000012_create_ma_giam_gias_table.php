<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ma_giam_gias', function (Blueprint $table) {
            $table->id();
            $table->string('ma_code')->unique();
            $table->string('ten_giam_gia');
            $table->string('loai_giam')->default('phan_tram'); // phan_tram hoặc co_dinh
            $table->decimal('gia_tri', 15, 2);
            $table->decimal('gia_tri_toi_thieu', 15, 2)->default(0);
            $table->decimal('giam_toi_da', 15, 2)->nullable();
            $table->integer('so_luong')->default(100);
            $table->integer('so_luong_da_dung')->default(0);
            $table->date('ngay_bat_dau')->nullable();
            $table->date('ngay_ket_thuc')->nullable();
            $table->integer('trang_thai')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ma_giam_gias');
    }
};
