<?php
namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private function getOrCreateCart(Request $request): Cart
    {
        $customer = $request->user();
        $cart = Cart::firstOrCreate(['customer_id' => $customer->id]);
        return $cart;
    }

    public function index(Request $request)
    {
        $cart = $this->getOrCreateCart($request);
        $items = CartItem::with('product')
            ->where('cart_id', $cart->id)
            ->get();

        $subtotal = $items->sum(fn($item) => $item->price * $item->quantity);

        return response()->json([
            'status' => 1,
            'data' => [
                'cart_id'  => $cart->id,
                'items'    => $items,
                'subtotal' => $subtotal,
                'count'    => $items->sum('quantity'),
            ],
        ]);
    }

    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1|max:99',
        ], [
            'product_id.required' => 'Vui lòng chọn sản phẩm.',
            'product_id.exists'   => 'Sản phẩm không tồn tại.',
            'quantity.required'   => 'Vui lòng nhập số lượng.',
            'quantity.min'        => 'Số lượng phải ít nhất là 1.',
        ]);

        $product = Product::findOrFail($request->product_id);

        if (!$product->is_active) {
            return response()->json(['status' => 0, 'message' => 'Sản phẩm không còn kinh doanh.']);
        }
        if ($product->quantity < $request->quantity) {
            return response()->json(['status' => 0, 'message' => 'Sản phẩm không đủ số lượng trong kho.']);
        }

        $cart = $this->getOrCreateCart($request);
        $price = $product->sale_price ?? $product->price;

        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingItem) {
            $newQty = $existingItem->quantity + $request->quantity;
            if ($newQty > $product->quantity) {
                return response()->json(['status' => 0, 'message' => 'Vượt quá số lượng tồn kho.']);
            }
            $existingItem->update(['quantity' => $newQty]);
        } else {
            CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
                'quantity'   => $request->quantity,
                'price'      => $price,
            ]);
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Đã thêm sản phẩm vào giỏ hàng!',
        ]);
    }

    public function updateItem(Request $request)
    {
        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'quantity'     => 'required|integer|min:1|max:99',
        ]);

        $item = CartItem::findOrFail($request->cart_item_id);
        $cart = $this->getOrCreateCart($request);

        if ($item->cart_id !== $cart->id) {
            return response()->json(['status' => 0, 'message' => 'Không có quyền truy cập.']);
        }

        $product = $item->product;
        if ($request->quantity > $product->quantity) {
            return response()->json(['status' => 0, 'message' => 'Vượt quá số lượng tồn kho.']);
        }

        $item->update(['quantity' => $request->quantity]);

        return response()->json(['status' => 1, 'message' => 'Đã cập nhật giỏ hàng.']);
    }

    public function removeItem(Request $request)
    {
        $request->validate(['cart_item_id' => 'required|exists:cart_items,id']);

        $item = CartItem::findOrFail($request->cart_item_id);
        $cart = $this->getOrCreateCart($request);

        if ($item->cart_id !== $cart->id) {
            return response()->json(['status' => 0, 'message' => 'Không có quyền truy cập.']);
        }

        $item->delete();
        return response()->json(['status' => 1, 'message' => 'Đã xoá sản phẩm khỏi giỏ hàng.']);
    }

    public function clear(Request $request)
    {
        $cart = $this->getOrCreateCart($request);
        CartItem::where('cart_id', $cart->id)->delete();
        return response()->json(['status' => 1, 'message' => 'Đã xoá toàn bộ giỏ hàng.']);
    }
}
