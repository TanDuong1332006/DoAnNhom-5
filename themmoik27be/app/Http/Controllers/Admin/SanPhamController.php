<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SanPham;
use App\Models\ChiTietDonHang;
use App\Http\Requests\SanPhamRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SanPhamController extends Controller
{
    public function getData(Request $request)
    {
        if ($request->boolean('search_options')) {
            return response()->json(['status' => 1, 'data' => SanPham::select('id', 'ten_san_pham', 'ma_san_pham')->orderByDesc('id')->get()]);
        }
        $data = SanPham::with(['danhMuc', 'thuongHieu', 'chiTietSanPhams.mauSac', 'chiTietSanPhams.kichThuoc', 'anhSanPhams'])
            ->orderBy('id', 'desc')
            ->get();
        foreach ($data as $product) {
            if ($product->chiTietSanPhams->isNotEmpty()) {
                $product->so_luong_ton = $product->chiTietSanPhams->where('trang_thai', 1)->sum('so_luong_ton');
            }
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Lấy dữ liệu sản phẩm thành công',
            'data'    => $data
        ]);
    }

    public function create(SanPhamRequest $request)
    {
        $data = $request->validated();

        $slug = $data['slug_san_pham'] ?? (Str::slug(mb_substr($request->ten_san_pham, 0, 200)) ?: 'san-pham');
        $check = SanPham::where('slug_san_pham', $slug)->first();
        if ($check) {
            $slug = $slug . '-' . Str::lower(Str::random(8));
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
            'trang_thai'      => $request->trang_thai ?? 0,
        ]);

        return response()->json([
            'status'  => 1,
            'message' => 'Thêm mới sản phẩm thành công',
            'data'    => $sp
        ], 201);
    }

    public function capNhat(SanPhamRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($request, $data) {
            $sp = SanPham::lockForUpdate()->findOrFail($request->id);
            $slug = $data['slug_san_pham'] ?? $sp->slug_san_pham;
            $check = SanPham::where('slug_san_pham', $slug)->where('id', '!=', $request->id)->first();
            if ($check) {
                $slug = $slug . '-' . Str::lower(Str::random(8));
            }

            unset($data['id']);
            $data['slug_san_pham'] = $slug;
            if (empty($data['ma_san_pham'])) {
                unset($data['ma_san_pham']);
            }
            if ($sp->chiTietSanPhams()->exists()) {
                $data['so_luong_ton'] = $sp->chiTietSanPhams()->where('trang_thai', 1)->sum('so_luong_ton');
            }
            $sale = array_key_exists('gia_khuyen_mai', $data) ? $data['gia_khuyen_mai'] : $sp->gia_khuyen_mai;
            if ($sale !== null && $sale > $data['gia_ban']) {
                throw \Illuminate\Validation\ValidationException::withMessages(['gia_khuyen_mai' => 'Giá khuyến mãi không được vượt giá bán.']);
            }
            $base = $sale > 0 ? $sale : $data['gia_ban'];
            $minDelta = $sp->chiTietSanPhams()->min('gia_chenh_lech') ?? 0;
            $maxDelta = $sp->chiTietSanPhams()->max('gia_chenh_lech') ?? 0;
            if ($base + $minDelta < 0 || $base + $maxDelta > 9999999999999.99) {
                throw \Illuminate\Validation\ValidationException::withMessages(['gia_ban' => 'Giá sản phẩm không phù hợp với chênh lệch biến thể hiện có.']);
            }
            $sp->update($data);

            return response()->json([
                'status'  => 1,
                'message' => 'Cập nhật sản phẩm thành công',
            ]);
        });
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|exists:san_phams,id']);

        return DB::transaction(function () use ($request) {
            $product = SanPham::lockForUpdate()->findOrFail($request->id);
            $variants = $product->chiTietSanPhams()->pluck('id');
            if (ChiTietDonHang::where('id_san_pham', $product->id)
                ->orWhereIn('id_chi_tiet_san_pham', $variants)->exists()) {
                return response()->json(['status' => 0, 'message' => 'Sản phẩm có lịch sử đơn hàng, hãy ẩn thay vì xóa.'], 409);
            }
            $product->delete(); // FK hiện có quản lý các bản ghi phụ thuộc.
            return response()->json(['status' => 1, 'message' => 'Xóa sản phẩm thành công']);
        });

    }

    public function doiTrangThai(Request $request)
    {
        $request->validate(['id' => 'required|exists:san_phams,id']);

        $sp = SanPham::find($request->id);
        $sp->trang_thai = $sp->trang_thai == 1 ? 0 : 1;
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
