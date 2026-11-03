<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mau_sacs', function (Blueprint $table) {
            $table->id();
            $table->string('ten_mau');
            $table->string('ma_mau')->nullable(); // Hex color code e.g. #FF0000
            $table->integer('trang_thai')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mau_sacs');
    }
};
