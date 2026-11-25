<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EngagementReady
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(Schema::hasTable('private_feedback') && Schema::hasTable('saved_vouchers')
            && Schema::hasColumn('ma_giam_gias', 'campaign_restricted'), 503,
            'Tính năng đang chờ kích hoạt. Vui lòng thử lại sau.');
        return $next($request);
    }
}
