<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChiTietSanPham;
use App\Models\ChiTietDonHang;
use App\Models\AnhSanPham;
use App\Models\SanPham;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ChiTietSanPhamController extends Controller
{
    public function getData(Request $request)
    {
        $request->validate(['id_san_pham' => 'sometimes|integer|exists:san_phams,id']);
        $query = ChiTietSanPham::with(['sanPham', 'mauSac', 'kichThuoc']);
        if ($request->filled('id_san_pham')) {
            $query->where('id_san_pham', $request->id_san_pham);
        }
        return response()->json([
            'status' => 1,
            'data' => $query->orderByDesc('id')->get(),
            'supports_color_image' => Schema::hasColumn('chi_tiet_san_phams', 'id_anh_san_pham'),
        ]);
    }

    private function saveVariant(Request $request, bool $updating)
    {
        $data = $request->validate([
            'id' => $updating ? 'required|integer|exists:chi_tiet_san_phams,id' : 'prohibited',
            'id_san_pham' => $updating ? 'sometimes|integer|exists:san_phams,id' : 'required|integer|exists:san_phams,id',
            'id_mau_sac' => 'nullable|integer|exists:mau_sacs,id',
            'id_kich_thuoc' => 'nullable|integer|exists:kich_thuocs,id',
            'id_anh_san_pham' => 'sometimes|nullable|integer|min:1',
            'so_luong_ton' => 'required|integer|min:0|max:2147483647',
            'gia_chenh_lech' => 'sometimes|required|numeric|decimal:0,2|min:-9999999999999.99|max:9999999999999.99',
            'trang_thai' => 'sometimes|integer|in:0,1',
        ]);

        return DB::transaction(function () use ($data, $updating) {
            $variant = $updating ? ChiTietSanPham::findOrFail($data['id']) : null;
            $product = SanPham::lockForUpdate()->findOrFail($variant?->id_san_pham ?? $data['id_san_pham']);
            if ($variant && isset($data['id_san_pham']) && $data['id_san_pham'] != $variant->id_san_pham) {
                throw ValidationException::withMessages(['id_san_pham' => 'Không thể chuyển biến thể sang sản phẩm khác.']);
            }
            $color = array_key_exists('id_mau_sac', $data) ? $data['id_mau_sac'] : $variant?->id_mau_sac;
            $size = array_key_exists('id_kich_thuoc', $data) ? $data['id_kich_thuoc'] : $variant?->id_kich_thuoc;
            if (array_key_exists('id_anh_san_pham', $data)) {
                if (!Schema::hasColumn('chi_tiet_san_phams', 'id_anh_san_pham')) {
                    throw ValidationException::withMessages(['id_anh_san_pham' => 'Chưa bật lưu ảnh theo màu. Có thể lưu biến thể khi bỏ chọn ảnh.']);
                }
                if ($data['id_anh_san_pham'] !== null && (!$color || !AnhSanPham::whereKey($data['id_anh_san_pham'])
                    ->where('id_san_pham', $product->id)->lockForUpdate()->first())) {
                    throw ValidationException::withMessages(['id_anh_san_pham' => 'Chọn một ảnh thuộc sản phẩm và gắn với một màu.']);
                }
            } elseif ($variant && $variant->id_mau_sac != $color && $variant->id_anh_san_pham) {
                $data['id_anh_san_pham'] = $product->chiTietSanPhams()->where('id_mau_sac', $color)->first()?->id_anh_san_pham;
            }
            $delta = $data['gia_chenh_lech'] ?? $variant?->gia_chenh_lech ?? 0;
            $base = $product->gia_khuyen_mai !== null && $product->gia_khuyen_mai > 0 ? $product->gia_khuyen_mai : $product->gia_ban;
            if ($base + $delta < 0 || $base + $delta > 9999999999999.99) {
                throw ValidationException::withMessages(['gia_chenh_lech' => 'Giá sau chênh lệch vượt phạm vi cho phép.']);
            }
            $duplicate = ChiTietSanPham::where('id_san_pham', $product->id)
                ->where('id_mau_sac', $color)->where('id_kich_thuoc', $size)
                ->when($variant, fn ($q) => $q->where('id', '!=', $variant->id))->exists();
            if ($duplicate) {
                throw ValidationException::withMessages(['id_mau_sac' => 'Tổ hợp màu và kích thước đã tồn tại.']);
            }
            if ($variant && ($variant->id_mau_sac != $color || $variant->id_kich_thuoc != $size)
                && ChiTietDonHang::where('id_chi_tiet_san_pham', $variant->id)->exists()) {
                return response()->json(['status' => 0, 'message' => 'Không đổi màu/size của biến thể có lịch sử đơn hàng.'], 409);
            }
            unset($data['id']);
            $data['id_san_pham'] = $product->id;
            $data['id_mau_sac'] = $color;
            $data['id_kich_thuoc'] = $size;
            if ($variant) {
                $variant->update($data);
            } else {
                if ($color && !array_key_exists('id_anh_san_pham', $data)) {
                    $image = $product->chiTietSanPhams()->where('id_mau_sac', $color)->first()?->id_anh_san_pham;
                    if ($image) $data['id_anh_san_pham'] = $image;
                }
                ChiTietSanPham::create($data + ['gia_chenh_lech' => 0, 'trang_thai' => 1]);
            }
            if ($color && array_key_exists('id_anh_san_pham', $data)) {
                $product->chiTietSanPhams()->where('id_mau_sac', $color)->update(['id_anh_san_pham' => $data['id_anh_san_pham']]);
            }
            $stock = $product->chiTietSanPhams()->where('trang_thai', 1)->sum('so_luong_ton');
            if ($stock > 2147483647) {
                throw ValidationException::withMessages(['so_luong_ton' => 'Tổng tồn kho vượt giới hạn cho phép.']);
            }
            $product->update(['so_luong_ton' => $stock]);
            return response()->json(['status' => 1, 'message' => $updating ? 'Cập nhật biến thể thành công' : 'Thêm biến thể thành công'], $updating ? 200 : 201);
        });
    }

    public function create(Request $request)
    {
        return $this->saveVariant($request, false);
    }

    public function capNhat(Request $request)
    {
        return $this->saveVariant($request, true);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|integer|exists:chi_tiet_san_phams,id']);
        return DB::transaction(function () use ($request) {
            $variant = ChiTietSanPham::findOrFail($request->id);
            $product = SanPham::lockForUpdate()->findOrFail($variant->id_san_pham);
            if (ChiTietDonHang::where('id_chi_tiet_san_pham', $variant->id)->exists()) {
                return response()->json(['status' => 0, 'message' => 'Biến thể có lịch sử đơn hàng, hãy ngừng bán thay vì xóa.'], 409);
            }
            $variant->delete();
            $product->update(['so_luong_ton' => $product->chiTietSanPhams()->where('trang_thai', 1)->sum('so_luong_ton')]);
            return response()->json(['status' => 1, 'message' => 'Xóa biến thể thành công']);
        });
    }
}
