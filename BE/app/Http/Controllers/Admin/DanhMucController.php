<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhMuc;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DanhMucController extends Controller
{
    public function getData()
    {
        $data = DanhMuc::withCount('sanPhams')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status'  => 1,
            'message' => 'Lấy dữ liệu danh mục thành công',
            'data'    => $data
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'ten_danh_muc' => 'required|string|max:255',
        ]);

        $slug = Str::slug($request->ten_danh_muc);
        $check = DanhMuc::where('slug_danh_muc', $slug)->first();
        if ($check) {
            $slug = $slug . '-' . time();
        }

        DanhMuc::create([
            'ten_danh_muc'  => $request->ten_danh_muc,
            'slug_danh_muc' => $slug,
            'hinh_anh'      => $request->hinh_anh,
            'mo_ta'         => $request->mo_ta,
            'trang_thai'    => $request->trang_thai ?? 1,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Thêm mới danh mục thành công',
        ], 201);
    }

    public function capNhat(Request $request)
    {
        $request->validate([
            'id'           => 'required|exists:danh_mucs,id',
            'ten_danh_muc' => 'required|string|max:255',
        ]);

        $danhMuc = DanhMuc::find($request->id);
        $slug = Str::slug($request->ten_danh_muc);
        $check = DanhMuc::where('slug_danh_muc', $slug)->where('id', '!=', $request->id)->first();
        if ($check) {
            $slug = $slug . '-' . time();
        }

        $danhMuc->update([
            'ten_danh_muc'  => $request->ten_danh_muc,
            'slug_danh_muc' => $slug,
            'hinh_anh'      => $request->hinh_anh ?? $danhMuc->hinh_anh,
            'mo_ta'         => $request->mo_ta,
            'trang_thai'    => $request->trang_thai ?? $danhMuc->trang_thai,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Cập nhật danh mục thành công',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:danh_mucs,id']);

        DanhMuc::where('id', $request->id)->delete();

        return response()->json([
            'status'  => 1,
            'message' => 'Xóa danh mục thành công',
        ]);
    }

    public function doiTrangThai(Request $request)
    {
        $request->validate(['id' => 'required|exists:danh_mucs,id']);

        $danhMuc = DanhMuc::find($request->id);
        $danhMuc->trang_thai = !$danhMuc->trang_thai;
        $danhMuc->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Đổi trạng thái danh mục thành công',
        ]);
    }
}
