<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thuong_hieus', function (Blueprint $table) {
            $table->id();
            $table->string('ten_thuong_hieu');
            $table->string('slug_thuong_hieu')->unique();
            $table->string('hinh_anh')->nullable();
            $table->text('mo_ta')->nullable();
            $table->integer('trang_thai')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thuong_hieus');
    }
};
