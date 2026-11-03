<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('khach_hangs', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('email');

            $table->string('otp_hash')->nullable()->after('is_kich_hoat');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_hash');
        });
    }

    public function down(): void
    {
        Schema::table('khach_hangs', function (Blueprint $table) {
            $table->dropUnique(['google_id']);

            $table->dropColumn([
                'google_id',
                'otp_hash',
                'otp_expires_at',
            ]);
        });
    }
};