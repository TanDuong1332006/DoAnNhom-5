<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('luot_xem_san_phams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_khach_hang')->constrained('khach_hangs')->cascadeOnDelete();
            $table->foreignId('id_san_pham')->constrained('san_phams')->cascadeOnDelete();
            $table->unsignedInteger('so_luot_xem')->default(0);
            $table->timestamps();
            $table->unique(['id_khach_hang', 'id_san_pham']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('luot_xem_san_phams');
    }
};
