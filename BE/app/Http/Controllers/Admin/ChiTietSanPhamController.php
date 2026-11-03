<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChiTietSanPham;
use Illuminate\Http\Request;

class ChiTietSanPhamController extends Controller
{
    public function getData(Request $request)
    {
        $query = ChiTietSanPham::with(['sanPham', 'mauSac', 'kichThuoc']);

        if ($request->has('id_san_pham')) {
            $query->where('id_san_pham', $request->id_san_pham);
        }

        $data = $query->orderBy('id', 'desc')->get();

        return response()->json([
            'status'  => 1,
            'message' => 'Lấy dữ liệu biến thể sản phẩm thành công',
            'data'    => $data
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'id_san_pham'  => 'required|exists:san_phams,id',
            'so_luong_ton' => 'required|numeric|min:0',
        ]);

        ChiTietSanPham::create([
            'id_san_pham'    => $request->id_san_pham,
            'id_mau_sac'     => $request->id_mau_sac,
            'id_kich_thuoc'  => $request->id_kich_thuoc,
            'so_luong_ton'   => $request->so_luong_ton,
            'gia_chenh_lech' => $request->gia_chenh_lech ?? 0,
            'trang_thai'     => $request->trang_thai ?? 1,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Thêm biến thể thành công',
        ], 201);
    }

    public function capNhat(Request $request)
    {
        $request->validate([
            'id'           => 'required|exists:chi_tiet_san_phams,id',
            'so_luong_ton' => 'required|numeric|min:0',
        ]);

        $ct = ChiTietSanPham::find($request->id);
        $ct->update([
            'id_mau_sac'     => $request->id_mau_sac ?? $ct->id_mau_sac,
            'id_kich_thuoc'  => $request->id_kich_thuoc ?? $ct->id_kich_thuoc,
            'so_luong_ton'   => $request->so_luong_ton,
            'gia_chenh_lech' => $request->gia_chenh_lech ?? $ct->gia_chenh_lech,
            'trang_thai'     => $request->trang_thai ?? $ct->trang_thai,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Cập nhật biến thể thành công',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:chi_tiet_san_phams,id']);

        ChiTietSanPham::where('id', $request->id)->delete();

        return response()->json([
            'status'  => 1,
            'message' => 'Xóa biến thể thành công',
        ]);
    }
}
