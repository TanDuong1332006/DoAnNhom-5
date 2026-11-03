<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use App\Models\ChiTietSanPham;
use App\Models\AnhSanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SanPhamController extends Controller
{
    public function getData()
    {
        $data = SanPham::with(['danhMuc', 'thuongHieu', 'chiTietSanPhams.mauSac', 'chiTietSanPhams.kichThuoc', 'anhSanPhams'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status'  => 1,
            'message' => 'Lấy dữ liệu sản phẩm thành công',
            'data'    => $data
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'ten_san_pham' => 'required|string|max:255',
            'id_danh_muc'  => 'required|exists:danh_mucs,id',
            'gia_ban'      => 'required|numeric|min:0',
        ]);

        $slug = Str::slug($request->ten_san_pham);
        $check = SanPham::where('slug_san_pham', $slug)->first();
        if ($check) {
            $slug = $slug . '-' . time();
        }

        $ma_san_pham = $request->ma_san_pham;
        if (!$ma_san_pham) {
            $ma_san_pham = 'SP' . strtoupper(Str::random(6));
        }

        $sp = SanPham::create([
            'id_danh_muc'     => $request->id_danh_muc,
            'id_thuong_hieu'  => $request->id_thuong_hieu,
            'ma_san_pham'     => $ma_san_pham,
            'ten_san_pham'    => $request->ten_san_pham,
            'slug_san_pham'   => $slug,
            'gia_ban'         => $request->gia_ban,
            'gia_khuyen_mai'  => $request->gia_khuyen_mai,
            'hinh_anh'        => $request->hinh_anh,
            'mo_ta_ngan'      => $request->mo_ta_ngan,
            'mo_ta_chi_tiet'  => $request->mo_ta_chi_tiet,
            'so_luong_ton'    => $request->so_luong_ton ?? 0,
            'is_noi_bat'      => $request->is_noi_bat ?? 0,
            'is_flash_sale'   => $request->is_flash_sale ?? 0,
            'trang_thai'      => $request->trang_thai ?? 1,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Thêm mới sản phẩm thành công',
            'data'    => $sp
        ], 201);
    }

    public function capNhat(Request $request)
    {
        $request->validate([
            'id'           => 'required|exists:san_phams,id',
            'ten_san_pham' => 'required|string|max:255',
            'id_danh_muc'  => 'required|exists:danh_mucs,id',
            'gia_ban'      => 'required|numeric|min:0',
        ]);

        $sp = SanPham::find($request->id);
        $slug = Str::slug($request->ten_san_pham);
        $check = SanPham::where('slug_san_pham', $slug)->where('id', '!=', $request->id)->first();
        if ($check) {
            $slug = $slug . '-' . time();
        }

        $sp->update([
            'id_danh_muc'     => $request->id_danh_muc,
            'id_thuong_hieu'  => $request->id_thuong_hieu,
            'ma_san_pham'     => $request->ma_san_pham ?? $sp->ma_san_pham,
            'ten_san_pham'    => $request->ten_san_pham,
            'slug_san_pham'   => $slug,
            'gia_ban'         => $request->gia_ban,
            'gia_khuyen_mai'  => $request->gia_khuyen_mai,
            'hinh_anh'        => $request->hinh_anh ?? $sp->hinh_anh,
            'mo_ta_ngan'      => $request->mo_ta_ngan,
            'mo_ta_chi_tiet'  => $request->mo_ta_chi_tiet,
            'so_luong_ton'    => $request->so_luong_ton ?? $sp->so_luong_ton,
            'is_noi_bat'      => $request->is_noi_bat ?? $sp->is_noi_bat,
            'is_flash_sale'   => $request->is_flash_sale ?? $sp->is_flash_sale,
            'trang_thai'      => $request->trang_thai ?? $sp->trang_thai,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Cập nhật sản phẩm thành công',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:san_phams,id']);

        SanPham::where('id', $request->id)->delete();

        return response()->json([
            'status'  => 1,
            'message' => 'Xóa sản phẩm thành công',
        ]);
    }

    public function doiTrangThai(Request $request)
    {
        $request->validate(['id' => 'required|exists:san_phams,id']);

        $sp = SanPham::find($request->id);
        $sp->trang_thai = !$sp->trang_thai;
        $sp->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Đổi trạng thái sản phẩm thành công',
        ]);
    }

    public function doiNoiBat(Request $request)
    {
        $request->validate(['id' => 'required|exists:san_phams,id']);

        $sp = SanPham::find($request->id);
        $sp->is_noi_bat = !$sp->is_noi_bat;
        $sp->save();

        return response()->json([
            'status'  => 1,
            'message' => 'Cập nhật trạng thái nổi bật thành công',
        ]);
    }
}
