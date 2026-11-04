<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chi_tiet_san_phams', function (Blueprint $table) {
            $table->foreignId('id_anh_san_pham')->nullable()->constrained('anh_san_phams')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chi_tiet_san_phams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_anh_san_pham');
        });
    }
};
