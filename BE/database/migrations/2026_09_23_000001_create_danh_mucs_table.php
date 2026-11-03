<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('danh_mucs', function (Blueprint $table) {
            $table->id();
            $table->string('ten_danh_muc');
            $table->string('slug_danh_muc')->unique();
            $table->string('hinh_anh')->nullable();
            $table->text('mo_ta')->nullable();
            $table->integer('trang_thai')->default(1); // 1: Hiển thị, 0: Tạm tắt
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('danh_mucs');
    }
};
