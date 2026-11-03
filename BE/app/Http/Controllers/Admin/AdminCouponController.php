<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class AdminCouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::orderBy('created_at', 'desc')->get();
        return response()->json(['status' => 1, 'data' => $coupons]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'      => 'required|string|unique:coupons,code|max:20',
            'type'      => 'required|in:percent,fixed',
            'value'     => 'required|numeric|min:0',
            'min_order' => 'nullable|numeric|min:0',
            'max_uses'  => 'nullable|integer|min:1',
            'expires_at'=> 'nullable|date|after:now',
        ], ['code.unique' => 'Mã coupon này đã tồn tại.']);

        $coupon = Coupon::create([
            'code'      => strtoupper($request->code),
            'type'      => $request->type,
            'value'     => $request->value,
            'min_order' => $request->get('min_order', 0),
            'max_uses'  => $request->max_uses,
            'expires_at'=> $request->expires_at,
            'is_active' => $request->get('is_active', 1),
        ]);

        return response()->json(['status' => 1, 'message' => 'Tạo mã giảm giá thành công!', 'data' => $coupon]);
    }

    public function update(Request $request)
    {
        $request->validate(['id' => 'required|exists:coupons,id']);
        $coupon = Coupon::findOrFail($request->id);
        $coupon->update($request->only(['value', 'min_order', 'max_uses', 'expires_at', 'is_active']));
        return response()->json(['status' => 1, 'message' => 'Cập nhật mã giảm giá thành công!', 'data' => $coupon]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:coupons,id']);
        Coupon::findOrFail($request->id)->delete();
        return response()->json(['status' => 1, 'message' => 'Xoá mã giảm giá thành công!']);
    }
}
