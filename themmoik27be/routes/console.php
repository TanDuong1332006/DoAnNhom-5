<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


Artisan::command('sofep:deliver-campaigns {--limit=100}', function () {
    if (!\Illuminate\Support\Facades\Schema::hasTable('customer_campaigns')) { $this->warn('Customer engagement chưa được kích hoạt.'); return; }
    $this->info('Delivered: '.app(\App\Services\CustomerCampaigns::class)->deliverDue((int)$this->option('limit')));
})->purpose('Giao một lô thông báo nội bộ đã đến hạn, không gửi email/SMS');
\Illuminate\Support\Facades\Schedule::command('sofep:deliver-campaigns --limit=100')->everyMinute()->timezone('Asia/Ho_Chi_Minh')->withoutOverlapping();
