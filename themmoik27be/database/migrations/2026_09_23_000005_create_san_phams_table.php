<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('san_phams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_danh_muc')->constrained('danh_mucs')->onDelete('cascade');
            $table->foreignId('id_thuong_hieu')->nullable()->constrained('thuong_hieus')->onDelete('set null');
            $table->string('ma_san_pham')->unique();
            $table->string('ten_san_pham');
            $table->string('slug_san_pham')->unique();
            $table->decimal('gia_ban', 15, 2);
            $table->decimal('gia_khuyen_mai', 15, 2)->nullable();
            $table->string('hinh_anh')->nullable();
            $table->text('mo_ta_ngan')->nullable();
            $table->longText('mo_ta_chi_tiet')->nullable();
            $table->integer('so_luong_ton')->default(0);
            $table->integer('is_noi_bat')->default(0);
            $table->integer('is_flash_sale')->default(0);
            $table->integer('luot_xem')->default(0);
            $table->integer('trang_thai')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('san_phams');
    }
};
