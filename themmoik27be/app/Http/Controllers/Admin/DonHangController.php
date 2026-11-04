<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use Illuminate\Http\Request;

class DonHangController extends Controller
{
    public function getData()
    {
        $data = DonHang::with(['chiTiets', 'khachHang'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status'  => 1,
            'message' => 'Lấy dữ liệu đơn hàng thành công',
            'data'    => $data
        ]);
    }

    public function capNhatTrangThai(Request $request)
    {
        $request->validate([
            'id'                  => 'required|exists:don_hangs,id',
            'trang_thai_don_hang' => 'required|in:cho_xac_nhan,da_xac_nhan,dang_giao,da_giao,da_huy',
            'trang_thai_thanh_toan' => 'nullable|in:chua_thanh_toan,da_thanh_toan,hoan_tien',
        ]);

        app(\App\Services\OrderLifecycle::class)->update((int) $request->id, $request->trang_thai_don_hang, $request->trang_thai_thanh_toan);

        return response()->json([
            'status'  => 1,
            'message' => 'Cập nhật trạng thái đơn hàng thành công',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:don_hangs,id']);

        return response()->json(['status' => 0, 'message' => 'Giữ nguyên lịch sử đơn hàng. Hãy hủy đơn chưa thanh toán nếu đủ điều kiện.'], 409);
    }
}
