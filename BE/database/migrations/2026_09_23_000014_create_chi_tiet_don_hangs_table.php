<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chi_tiet_don_hangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_don_hang')->constrained('don_hangs')->onDelete('cascade');
            $table->foreignId('id_san_pham')->nullable()->constrained('san_phams')->onDelete('set null');
            $table->foreignId('id_chi_tiet_san_pham')->nullable()->constrained('chi_tiet_san_phams')->onDelete('set null');
            $table->string('ten_san_pham');
            $table->string('hinh_anh')->nullable();
            $table->string('ten_mau')->nullable();
            $table->string('ten_kich_thuoc')->nullable();
            $table->decimal('gia_ban', 15, 2);
            $table->integer('so_luong')->default(1);
            $table->decimal('thanh_tien', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_don_hangs');
    }
};
