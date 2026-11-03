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
            'trang_thai_don_hang' => 'required|string',
        ]);

        $dh = DonHang::find($request->id);
        $dh->trang_thai_don_hang = $request->trang_thai_don_hang;
        if ($request->has('trang_thai_thanh_toan')) {
            $dh->trang_thai_thanh_toan = $request->trang_thai_thanh_toan;
        }
        $dh->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Cập nhật trạng thái đơn hàng thành công',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:don_hangs,id']);

        DonHang::where('id', $request->id)->delete();

        return response()->json([
            'status'  => 1,
            'message' => 'Xóa đơn hàng thành công',
        ]);
    }
}
