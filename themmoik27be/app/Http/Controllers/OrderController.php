<?php
namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('items')
            ->where('customer_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['status' => 1, 'data' => $orders]);
    }

    public function show(Request $request, $id)
    {
        $order = Order::with(['items', 'items.product'])
            ->where('customer_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json(['status' => 1, 'data' => $order]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:100',
            'email'          => 'required|email',
            'phone'          => 'required|regex:/^[0-9]{9,11}$/',
            'address'        => 'required|string|max:255',
            'payment_method' => 'required|in:1,2',
            'note'           => 'nullable|string|max:500',
            'coupon_code'    => 'nullable|string',
        ], [
            'name.required'           => 'Vui lòng nhập tên người nhận.',
            'email.required'          => 'Vui lòng nhập email.',
            'phone.required'          => 'Vui lòng nhập số điện thoại.',
            'address.required'        => 'Vui lòng nhập địa chỉ giao hàng.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
        ]);

        $customer = $request->user();
        $cart = Cart::where('customer_id', $customer->id)->first();

        if (!$cart) {
            return response()->json(['status' => 0, 'message' => 'Giỏ hàng trống.']);
        }

        $cartItems = CartItem::with('product')->where('cart_id', $cart->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['status' => 0, 'message' => 'Giỏ hàng trống.']);
        }

        // Kiểm tra tồn kho
        foreach ($cartItems as $item) {
            if (!$item->product || !$item->product->is_active) {
                return response()->json(['status' => 0, 'message' => "Sản phẩm '{$item->product->name}' không còn kinh doanh."]);
            }
            if ($item->quantity > $item->product->quantity) {
                return response()->json(['status' => 0, 'message' => "Sản phẩm '{$item->product->name}' không đủ hàng."]);
            }
        }

        // Tính toán
        $subtotal = $cartItems->sum(fn($item) => $item->price * $item->quantity);
        $shippingFee = $subtotal >= 500000 ? 0 : 30000; // Miễn ship từ 500k
        $discount = 0;
        $couponCode = null;

        // Áp dụng coupon
        if ($request->filled('coupon_code')) {
            $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();
            if ($coupon && $coupon->isValid() && $subtotal >= $coupon->min_order) {
                $discount = $coupon->calculateDiscount($subtotal);
                $couponCode = $coupon->code;
            }
        }

        $total = max(0, $subtotal + $shippingFee - $discount);

        // Tạo đơn hàng
        $order = Order::create([
            'customer_id'    => $customer->id,
            'code'           => 'DH' . strtoupper(Str::random(8)),
            'name'           => $request->name,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'address'        => $request->address,
            'subtotal'       => $subtotal,
            'discount'       => $discount,
            'shipping_fee'   => $shippingFee,
            'total'          => $total,
            'payment_method' => $request->payment_method,
            'status'         => 0,
            'coupon_code'    => $couponCode,
            'note'           => $request->note,
        ]);

        // Tạo order items và trừ tồn kho
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id'      => $order->id,
                'product_id'    => $item->product_id,
                'product_name'  => $item->product->name,
                'product_image' => $item->product->image,
                'price'         => $item->price,
                'quantity'      => $item->quantity,
                'subtotal'      => $item->price * $item->quantity,
            ]);

            // Trừ tồn kho
            Product::where('id', $item->product_id)->decrement('quantity', $item->quantity);
        }

        // Tăng used_count coupon
        if ($couponCode) {
            Coupon::where('code', $couponCode)->increment('used_count');
        }

        // Xoá giỏ hàng
        CartItem::where('cart_id', $cart->id)->delete();

        return response()->json([
            'status'  => 1,
            'message' => 'Đặt hàng thành công!',
            'data'    => $order->load('items'),
        ]);
    }
}
