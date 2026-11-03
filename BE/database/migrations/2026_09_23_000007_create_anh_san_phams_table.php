<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anh_san_phams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_san_pham')->constrained('san_phams')->onDelete('cascade');
            $table->string('duong_dan_anh');
            $table->integer('thu_tu')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anh_san_phams');
    }
};
