<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('hoa_dons')) {
            Schema::create('hoa_dons', function (Blueprint $table) {
                $table->id();
                $table->string('ma_hoa_don')->unique();
                $table->decimal('tong_tien_hang', 15, 2)->default(0);
                $table->decimal('giam_gia', 15, 2)->default(0);
                $table->decimal('phi_van_chuyen', 15, 2)->default(0);
                $table->decimal('tong_tien', 15, 2)->default(0);
                $table->tinyInteger('trang_thai')->default(0); // 0 = Chưa thanh toán, 1 = Đã thanh toán
                $table->string('phuong_thuc_thanh_toan')->default('MB_BANK');
                $table->text('ghi_chu')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('giao_dichs')) {
            Schema::create('giao_dichs', function (Blueprint $table) {
                $table->id();
                $table->string('description');
                $table->string('ma_hoa_don')->nullable();
                $table->decimal('creditAmount', 15, 2);
                $table->string('refNo')->unique();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('giao_dichs');
        Schema::dropIfExists('hoa_dons');
    }
};
