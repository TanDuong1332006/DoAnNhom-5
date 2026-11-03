<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KichThuoc;
use Illuminate\Http\Request;

class KichThuocController extends Controller
{
    public function getData()
    {
        $data = KichThuoc::orderBy('id', 'asc')->get();

        return response()->json([
            'status'  => 1,
            'message' => 'Lấy dữ liệu kích thước thành công',
            'data'    => $data
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'ten_kich_thuoc' => 'required|string|max:50',
        ]);

        KichThuoc::create([
            'ten_kich_thuoc' => $request->ten_kich_thuoc,
            'mo_ta'          => $request->mo_ta,
            'trang_thai'     => $request->trang_thai ?? 1,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Thêm mới kích thước thành công',
        ], 201);
    }

    public function capNhat(Request $request)
    {
        $request->validate([
            'id'             => 'required|exists:kich_thuocs,id',
            'ten_kich_thuoc' => 'required|string|max:50',
        ]);

        $kichThuoc = KichThuoc::find($request->id);
        $kichThuoc->update([
            'ten_kich_thuoc' => $request->ten_kich_thuoc,
            'mo_ta'          => $request->mo_ta,
            'trang_thai'     => $request->trang_thai ?? $kichThuoc->trang_thai,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Cập nhật kích thước thành công',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:kich_thuocs,id']);

        KichThuoc::where('id', $request->id)->delete();

        return response()->json([
            'status'  => 1,
            'message' => 'Xóa kích thước thành công',
        ]);
    }

    public function doiTrangThai(Request $request)
    {
        $request->validate(['id' => 'required|exists:kich_thuocs,id']);

        $kichThuoc = KichThuoc::find($request->id);
        $kichThuoc->trang_thai = !$kichThuoc->trang_thai;
        $kichThuoc->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Đổi trạng thái kích thước thành công',
        ]);
    }
}
