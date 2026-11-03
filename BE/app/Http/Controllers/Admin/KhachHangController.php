<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhachHang;
use Illuminate\Http\Request;

class KhachHangController extends Controller
{
    public function getData()
    {
        $data = KhachHang::withCount('donHangs')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status'  => 1,
            'message' => 'Lấy dữ liệu khách hàng thành công',
            'data'    => $data
        ]);
    }

    public function doiTrangThai(Request $request)
    {
        $request->validate(['id' => 'required|exists:khach_hangs,id']);

        $kh = KhachHang::find($request->id);
        $kh->is_kich_hoat = !$kh->is_kich_hoat;
        $kh->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Cập nhật trạng thái kích hoạt thành công',
        ]);
    }

    public function khoaTaiKhoan(Request $request)
    {
        $request->validate(['id' => 'required|exists:khach_hangs,id']);

        $kh = KhachHang::find($request->id);
        $kh->is_khoa = !$kh->is_khoa;
        $kh->save();

        return response()->json([
            'status'  => 1,
            'message' => $kh->is_khoa ? 'Đã khóa tài khoản khách hàng' : 'Đã mở khóa tài khoản khách hàng',
        ]);
    }
}
