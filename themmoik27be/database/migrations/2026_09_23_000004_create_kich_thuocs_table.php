<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kich_thuocs', function (Blueprint $table) {
            $table->id();
            $table->string('ten_kich_thuoc'); // XS, S, M, L, XL, XXL, 38, 39...
            $table->text('mo_ta')->nullable();
            $table->integer('trang_thai')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kich_thuocs');
    }
};
