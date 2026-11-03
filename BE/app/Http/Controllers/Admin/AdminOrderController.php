<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['customer', 'items'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . $request->search . '%')
                  ->orWhere('name', 'like', '%' . $request->search . '%');
        }

        $orders = $query->paginate(15);
        return response()->json(['status' => 1, 'data' => $orders]);
    }

    public function show($id)
    {
        $order = Order::with(['customer', 'items', 'items.product'])->findOrFail($id);
        return response()->json(['status' => 1, 'data' => $order]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id'     => 'required|exists:orders,id',
            'status' => 'required|in:0,1,2,3,4',
        ]);

        $order = Order::findOrFail($request->id);
        $order->update(['status' => $request->status]);

        $labels = ['Chờ xác nhận', 'Đang xử lý', 'Đang giao hàng', 'Đã giao hàng', 'Đã huỷ'];

        return response()->json([
            'status'  => 1,
            'message' => 'Cập nhật trạng thái đơn hàng thành công: ' . $labels[$request->status],
            'data'    => $order,
        ]);
    }
}
