<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chi_tiet_san_phams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_san_pham')->constrained('san_phams')->onDelete('cascade');
            $table->foreignId('id_mau_sac')->nullable()->constrained('mau_sacs')->onDelete('set null');
            $table->foreignId('id_kich_thuoc')->nullable()->constrained('kich_thuocs')->onDelete('set null');
            $table->integer('so_luong_ton')->default(0);
            $table->decimal('gia_chenh_lech', 15, 2)->default(0);
            $table->integer('trang_thai')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_san_phams');
    }
};
