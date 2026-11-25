<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('khach_hangs', 'role')) {
            Schema::table('khach_hangs', fn (Blueprint $table) => $table->string('role', 20)->default('customer'));
        }
    }

    public function down(): void
    {
        // Forward-only: role có thể tồn tại từ schema legacy trước migration này.
        // Không xóa quyền của tài khoản hiện hữu khi rollback.
    }
};
