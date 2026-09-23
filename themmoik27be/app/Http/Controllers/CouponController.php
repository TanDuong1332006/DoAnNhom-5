<?php
namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'code'     => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon) {
            return response()->json(['status' => 0, 'message' => 'Mã giảm giá không tồn tại.']);
        }

        if (!$coupon->isValid()) {
            return response()->json(['status' => 0, 'message' => 'Mã giảm giá đã hết hạn hoặc không còn hiệu lực.']);
        }

        if ($request->subtotal < $coupon->min_order) {
            return response()->json([
                'status'  => 0,
                'message' => 'Đơn hàng cần đạt tối thiểu ' . number_format($coupon->min_order) . 'đ để dùng mã này.',
            ]);
        }

        $discount = $coupon->calculateDiscount((float) $request->subtotal);

        return response()->json([
            'status'   => 1,
            'message'  => 'Áp dụng mã giảm giá thành công!',
            'discount' => $discount,
            'coupon'   => $coupon,
        ]);
    }
}
