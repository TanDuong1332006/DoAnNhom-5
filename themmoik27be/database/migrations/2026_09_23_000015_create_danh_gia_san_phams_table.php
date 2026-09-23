<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_gia_san_phams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_san_pham')->constrained('san_phams')->onDelete('cascade');
            $table->foreignId('id_khach_hang')->constrained('khach_hangs')->onDelete('cascade');
            $table->integer('so_sao')->default(5);
            $table->string('tieu_de')->nullable();
            $table->text('noi_dung')->nullable();
            $table->integer('trang_thai')->default(1); // 1: Đã duyệt/Hiển thị, 0: Đang chờ duyệt/Ẩn
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_gia_san_phams');
    }
};
