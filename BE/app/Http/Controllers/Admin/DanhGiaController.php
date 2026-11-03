<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhGiaSanPham;
use Illuminate\Http\Request;

class DanhGiaController extends Controller
{
    public function getData()
    {
        $data = DanhGiaSanPham::with(['sanPham', 'khachHang'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status'  => 1,
            'message' => 'Lấy dữ liệu đánh giá thành công',
            'data'    => $data
        ]);
    }

    public function doiTrangThai(Request $request)
    {
        $request->validate(['id' => 'required|exists:danh_gia_san_phams,id']);

        $dg = DanhGiaSanPham::find($request->id);
        $dg->trang_thai = !$dg->trang_thai;
        $dg->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Đổi trạng thái duyệt đánh giá thành công',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:danh_gia_san_phams,id']);

        DanhGiaSanPham::where('id', $request->id)->delete();

        return response()->json([
            'status'  => 1,
            'message' => 'Xóa đánh giá thành công',
        ]);
    }
}
