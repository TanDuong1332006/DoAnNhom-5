<?php
namespace App\Services;

use App\Models\KhachHang;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class LoyaltyAudience
{
    // Chỉ tính đơn đã giao và đã thanh toán; kỳ dựa trên ngày tạo đơn, hiển thị rõ trong UI.
    public function query(array $filters, bool $marketing = false)
    {
        $stats = DB::table('don_hangs')->select('id_khach_hang')
            ->selectRaw('COUNT(*) AS orders_count, SUM(tong_thanh_toan) AS spend_total')
            ->where('trang_thai_don_hang', 'da_giao')->where('trang_thai_thanh_toan', 'da_thanh_toan');
        $now = CarbonImmutable::now('Asia/Ho_Chi_Minh');
        $period = $filters['period'] ?? 'all';
        if ($period !== 'all') {
            [$start, $end] = match ($period) {
                'week' => [$now->startOfWeek(), $now->startOfWeek()->addWeek()],
                'month' => [$now->startOfMonth(), $now->startOfMonth()->addMonth()],
                'year' => [$now->startOfYear(), $now->startOfYear()->addYear()],
            };
            $stats->where('created_at', '>=', $start->utc()->format('Y-m-d H:i:s'))
                ->where('created_at', '<', $end->utc()->format('Y-m-d H:i:s'));
        }
        $stats->groupBy('id_khach_hang');
        $q = KhachHang::query()->where(fn ($q) => $q->where('role', 'customer')->orWhereNull('role'))
            ->where('is_kich_hoat', 1)->where('is_khoa', 0)
            ->leftJoinSub($stats, 'loyalty', 'loyalty.id_khach_hang', '=', 'khach_hangs.id')
            ->select('khach_hangs.id');
        foreach (['orders' => 'orders_count', 'spend' => 'spend_total'] as $key => $column) {
            foreach (['min' => '>=', 'max' => '<='] as $bound => $operator) {
                if (isset($filters[$key][$bound])) $q->whereRaw("COALESCE(loyalty.$column,0) $operator ?", [$filters[$key][$bound]]);
            }
        }
        if ($marketing) $q->whereNotExists(fn ($q) => $q->selectRaw('1')->from('customer_preferences')
            ->whereColumn('customer_preferences.customer_id', 'khach_hangs.id')->where('marketing_enabled', false));
        return $q;
    }
    public function matches(int $customer, array $filters): bool
    {
        return $this->query($filters)->where('khach_hangs.id', $customer)->exists();
    }
}
