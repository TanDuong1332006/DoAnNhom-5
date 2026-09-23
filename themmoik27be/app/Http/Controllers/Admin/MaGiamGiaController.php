<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaGiamGia;
use Illuminate\Http\Request;

class MaGiamGiaController extends Controller
{
    public function getData()
    {
        $data = MaGiamGia::orderBy('id', 'desc')->get();

        return response()->json([
            'status'  => 1,
            'message' => 'Lấy dữ liệu mã giảm giá thành công',
            'data'    => $data
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'ma_code'      => 'required|unique:ma_giam_gias,ma_code|string|max:50',
            'ten_giam_gia' => 'required|string|max:255',
            'gia_tri'      => 'required|numeric|min:0',
        ]);

        MaGiamGia::create([
            'ma_code'           => strtoupper(trim($request->ma_code)),
            'ten_giam_gia'      => $request->ten_giam_gia,
            'loai_giam'         => $request->loai_giam ?? 'phan_tram',
            'gia_tri'           => $request->gia_tri,
            'gia_tri_toi_thieu' => $request->gia_tri_toi_thieu ?? 0,
            'giam_toi_da'       => $request->giam_toi_da,
            'so_luong'          => $request->so_luong ?? 100,
            'ngay_bat_dau'      => $request->ngay_bat_dau,
            'ngay_ket_thuc'     => $request->ngay_ket_thuc,
            'trang_thai'        => $request->trang_thai ?? 1,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Thêm mã giảm giá thành công',
        ], 201);
    }

    public function capNhat(Request $request)
    {
        $request->validate([
            'id'           => 'required|exists:ma_giam_gias,id',
            'ten_giam_gia' => 'required|string|max:255',
            'gia_tri'      => 'required|numeric|min:0',
        ]);

        $mgg = MaGiamGia::find($request->id);
        $mgg->update([
            'ten_giam_gia'      => $request->ten_giam_gia,
            'loai_giam'         => $request->loai_giam ?? $mgg->loai_giam,
            'gia_tri'           => $request->gia_tri,
            'gia_tri_toi_thieu' => $request->gia_tri_toi_thieu ?? $mgg->gia_tri_toi_thieu,
            'giam_toi_da'       => $request->giam_toi_da ?? $mgg->giam_toi_da,
            'so_luong'          => $request->so_luong ?? $mgg->so_luong,
            'ngay_bat_dau'      => $request->ngay_bat_dau ?? $mgg->ngay_bat_dau,
            'ngay_ket_thuc'     => $request->ngay_ket_thuc ?? $mgg->ngay_ket_thuc,
            'trang_thai'        => $request->trang_thai ?? $mgg->trang_thai,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Cập nhật mã giảm giá thành công',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:ma_giam_gias,id']);

        MaGiamGia::where('id', $request->id)->delete();

        return response()->json([
            'status'  => 1,
            'message' => 'Xóa mã giảm giá thành công',
        ]);
    }

    public function doiTrangThai(Request $request)
    {
        $request->validate(['id' => 'required|exists:ma_giam_gias,id']);

        $mgg = MaGiamGia::find($request->id);
        $mgg->trang_thai = !$mgg->trang_thai;
        $mgg->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Đổi trạng thái mã giảm giá thành công',
        ]);
    }
}
