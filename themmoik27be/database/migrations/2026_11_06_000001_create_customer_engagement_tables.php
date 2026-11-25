<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('ma_giam_gias', 'doi_tuong') || !Schema::hasColumn('don_hangs', 'ton_kho_da_giu')) throw new \RuntimeException('Cần migration voucher eligibility/reservations trước module engagement.');
        Schema::create('private_feedback', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('customer_id')->nullable()->index();
            $t->string('topic', 60); $t->text('body'); $t->string('email')->nullable(); $t->string('phone', 25)->nullable();
            $t->string('status', 20)->default('unread')->index(); $t->text('internal_note')->nullable();
            $t->text('reply')->nullable(); $t->string('reply_channel', 20)->nullable(); $t->timestamp('replied_at')->nullable(); $t->timestamps();
        });
        Schema::create('feedback_attachments', function (Blueprint $t) {
            $t->id(); $t->foreignId('feedback_id')->constrained('private_feedback')->cascadeOnDelete();
            $t->string('path'); $t->string('mime', 30); $t->unsignedInteger('bytes'); $t->timestamps();
        });
        Schema::create('customer_preferences', function (Blueprint $t) {
            $t->unsignedBigInteger('customer_id')->primary(); $t->boolean('marketing_enabled')->default(true); $t->timestamps();
        });
        Schema::create('customer_campaigns', function (Blueprint $t) {
            $t->id(); $t->uuid('request_key')->unique(); $t->string('fingerprint', 64);
            $t->unsignedBigInteger('admin_id'); $t->string('title', 160); $t->text('body'); $t->string('kind', 20);
            $t->unsignedBigInteger('voucher_id')->nullable()->index(); $t->json('filters'); $t->boolean('recheck')->default(false);
            $t->timestamp('send_at')->index(); $t->string('status', 20)->default('queued'); $t->unsignedInteger('recipient_count')->default(0); $t->timestamps();
        });
        Schema::create('campaign_recipients', function (Blueprint $t) {
            $t->id(); $t->foreignId('campaign_id')->constrained('customer_campaigns'); $t->unsignedBigInteger('customer_id')->index();
            $t->string('status', 20)->default('pending'); $t->unsignedTinyInteger('attempts')->default(0); $t->string('error', 255)->nullable();
            $t->timestamp('delivered_at')->nullable(); $t->timestamps(); $t->unique(['campaign_id','customer_id']); $t->index(['status','attempts']);
        });
        Schema::create('customer_notifications', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('customer_id'); $t->string('delivery_key', 100)->unique();
            $t->unsignedBigInteger('campaign_id')->nullable(); $t->string('title', 160); $t->text('body'); $t->string('kind', 20);
            $t->unsignedBigInteger('voucher_id')->nullable(); $t->timestamp('read_at')->nullable(); $t->timestamps();
            $t->index(['customer_id','read_at','id'], 'customer_notifications_unread');
        });
        Schema::create('voucher_grants', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('voucher_id'); $t->unsignedBigInteger('customer_id');
            $t->unsignedBigInteger('campaign_id'); $t->json('filters')->nullable(); $t->boolean('recheck')->default(false); $t->timestamps();
            $t->unique(['voucher_id','customer_id','campaign_id'], 'voucher_grants_recipient');
        });
        Schema::create('saved_vouchers', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('voucher_id'); $t->unsignedBigInteger('customer_id'); $t->timestamps();
            $t->unique(['voucher_id','customer_id']);
        });
        Schema::table('ma_giam_gias', function (Blueprint $t) {
            $t->boolean('campaign_restricted')->default(false); $t->text('mo_ta')->nullable();
        });
    }
    public function down(): void
    {
        Schema::table('ma_giam_gias', fn (Blueprint $t) => $t->dropColumn(['campaign_restricted','mo_ta']));
        foreach (['saved_vouchers','voucher_grants','customer_notifications','campaign_recipients','customer_campaigns','customer_preferences','feedback_attachments','private_feedback'] as $table) Schema::dropIfExists($table);
    }
};
