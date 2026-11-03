<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chi_tiet_gio_hangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_gio_hang')->constrained('gio_hangs')->onDelete('cascade');
            $table->foreignId('id_san_pham')->constrained('san_phams')->onDelete('cascade');
            $table->foreignId('id_chi_tiet_san_pham')->nullable()->constrained('chi_tiet_san_phams')->onDelete('cascade');
            $table->integer('so_luong')->default(1);
            $table->decimal('gia_ban', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_gio_hangs');
    }
};
