<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $stats = [
            'total_products'   => Product::count(),
            'total_customers'  => Customer::count(),
            'total_orders'     => Order::count(),
            'orders_today'     => Order::whereDate('created_at', $today)->count(),
            'revenue_today'    => Order::whereDate('created_at', $today)->where('status', 3)->sum('total'),
            'revenue_month'    => Order::where('created_at', '>=', $thisMonth)->where('status', 3)->sum('total'),
            'pending_orders'   => Order::where('status', 0)->count(),
            'low_stock_products' => Product::where('quantity', '<=', 5)->where('is_active', 1)->count(),
        ];

        $recent_orders = Order::with(['customer'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'status' => 1,
            'stats'  => $stats,
            'recent_orders' => $recent_orders,
        ]);
    }
}
