<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThuongHieu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ThuongHieuController extends Controller
{
    public function getData()
    {
        $data = ThuongHieu::withCount('sanPhams')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status'  => 1,
            'message' => 'Lấy dữ liệu thương hiệu thành công',
            'data'    => $data
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'ten_thuong_hieu' => 'required|string|max:255',
        ]);

        $slug = Str::slug($request->ten_thuong_hieu);
        $check = ThuongHieu::where('slug_thuong_hieu', $slug)->first();
        if ($check) {
            $slug = $slug . '-' . time();
        }

        ThuongHieu::create([
            'ten_thuong_hieu'  => $request->ten_thuong_hieu,
            'slug_thuong_hieu' => $slug,
            'hinh_anh'         => $request->hinh_anh,
            'mo_ta'            => $request->mo_ta,
            'trang_thai'       => $request->trang_thai ?? 1,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Thêm mới thương hiệu thành công',
        ], 201);
    }

    public function capNhat(Request $request)
    {
        $request->validate([
            'id'              => 'required|exists:thuong_hieus,id',
            'ten_thuong_hieu' => 'required|string|max:255',
        ]);

        $thuongHieu = ThuongHieu::find($request->id);
        $slug = Str::slug($request->ten_thuong_hieu);
        $check = ThuongHieu::where('slug_thuong_hieu', $slug)->where('id', '!=', $request->id)->first();
        if ($check) {
            $slug = $slug . '-' . time();
        }

        $thuongHieu->update([
            'ten_thuong_hieu'  => $request->ten_thuong_hieu,
            'slug_thuong_hieu' => $slug,
            'hinh_anh'         => $request->hinh_anh ?? $thuongHieu->hinh_anh,
            'mo_ta'            => $request->mo_ta,
            'trang_thai'       => $request->trang_thai ?? $thuongHieu->trang_thai,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Cập nhật thương hiệu thành công',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:thuong_hieus,id']);

        ThuongHieu::where('id', $request->id)->delete();

        return response()->json([
            'status'  => 1,
            'message' => 'Xóa thương hiệu thành công',
        ]);
    }

    public function doiTrangThai(Request $request)
    {
        $request->validate(['id' => 'required|exists:thuong_hieus,id']);

        $thuongHieu = ThuongHieu::find($request->id);
        $thuongHieu->trang_thai = !$thuongHieu->trang_thai;
        $thuongHieu->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Đổi trạng thái thương hiệu thành công',
        ]);
    }
}
