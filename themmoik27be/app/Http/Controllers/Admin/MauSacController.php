<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MauSac;
use Illuminate\Http\Request;

class MauSacController extends Controller
{
    public function getData()
    {
        $data = MauSac::orderBy('id', 'desc')->get();

        return response()->json([
            'status'  => 1,
            'message' => 'Lấy dữ liệu màu sắc thành công',
            'data'    => $data
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'ten_mau' => 'required|string|max:100',
        ]);

        MauSac::create([
            'ten_mau'    => $request->ten_mau,
            'ma_mau'     => $request->ma_mau ?? '#000000',
            'trang_thai' => $request->trang_thai ?? 1,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Thêm mới màu sắc thành công',
        ], 201);
    }

    public function capNhat(Request $request)
    {
        $request->validate([
            'id'      => 'required|exists:mau_sacs,id',
            'ten_mau' => 'required|string|max:100',
        ]);

        $mauSac = MauSac::find($request->id);
        $mauSac->update([
            'ten_mau'    => $request->ten_mau,
            'ma_mau'     => $request->ma_mau ?? $mauSac->ma_mau,
            'trang_thai' => $request->trang_thai ?? $mauSac->trang_thai,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Cập nhật màu sắc thành công',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:mau_sacs,id']);

        MauSac::where('id', $request->id)->delete();

        return response()->json([
            'status'  => 1,
            'message' => 'Xóa màu sắc thành công',
        ]);
    }

    public function doiTrangThai(Request $request)
    {
        $request->validate(['id' => 'required|exists:mau_sacs,id']);

        $mauSac = MauSac::find($request->id);
        $mauSac->trang_thai = !$mauSac->trang_thai;
        $mauSac->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Đổi trạng thái màu sắc thành công',
        ]);
    }
}
