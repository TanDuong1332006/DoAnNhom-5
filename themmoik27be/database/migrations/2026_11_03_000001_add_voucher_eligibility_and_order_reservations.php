<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ma_giam_gias', function (Blueprint $table) {
            $table->string('doi_tuong')->default('all');
            $table->unsignedInteger('gioi_han_moi_khach')->nullable();
            $table->json('ids_khach_hang')->nullable();
        });
        Schema::table('don_hangs', function (Blueprint $table) {
            // Không suy đoán tồn kho đã trừ cho đơn lịch sử.
            $table->boolean('ton_kho_da_giu')->default(false);
            $table->boolean('voucher_da_giu')->default(false);
            $table->string('checkout_fingerprint', 64)->nullable();
            $table->index(['id_khach_hang', 'ma_giam_gia_su_dung', 'trang_thai_don_hang'], 'orders_voucher_customer_status');
        });
    }

    public function down(): void
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            $table->dropIndex('orders_voucher_customer_status');
            $table->dropColumn(['ton_kho_da_giu', 'voucher_da_giu', 'checkout_fingerprint']);
        });
        Schema::table('ma_giam_gias', fn (Blueprint $table) => $table->dropColumn(['doi_tuong', 'gioi_han_moi_khach', 'ids_khach_hang']));
    }
};
