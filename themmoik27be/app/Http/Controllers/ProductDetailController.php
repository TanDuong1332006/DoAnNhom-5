<?php

namespace App\Http\Controllers;

use App\Models\DanhGiaSanPham;
use App\Models\SanPham;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductDetailController extends Controller
{
    // Trang chi tiết chỉ đọc: không tăng lượt xem hoặc thay đổi dữ liệu sản phẩm.
    public function show(string $slug)
    {
        $product = SanPham::catalog()->with(['anhSanPhams' => fn ($q) => $q->orderBy('thu_tu')->orderBy('id')])
            ->where(function ($q) use ($slug) {
                $q->where('slug_san_pham', $slug);
                if (ctype_digit($slug)) $q->orWhere('id', (int) $slug);
            })->firstOrFail();

        $summary = (object) ['total' => 0, 'average' => null];
        $reviewList = [];
        if (Schema::hasColumns('danh_gia_san_phams', ['id', 'id_san_pham', 'id_khach_hang', 'trang_thai', 'so_sao', 'tieu_de', 'noi_dung', 'created_at'])) {
            $reviews = DanhGiaSanPham::where('id_san_pham', $product->id)->where('trang_thai', 1)
                ->whereIn('so_sao', [1, 2, 3, 4, 5])->whereHas('khachHang');
            $summary = (clone $reviews)->selectRaw('COUNT(*) AS total, AVG(so_sao) AS average')->first();
            $reviewList = (clone $reviews)->select('id', 'id_khach_hang', 'so_sao', 'tieu_de', 'noi_dung', 'created_at')
                ->with('khachHang:id,ho_va_ten')->orderByDesc('id')->limit(20)->get();
        }

        $sold = null;
        if (Schema::hasColumns('don_hangs', ['trang_thai_don_hang', 'trang_thai_thanh_toan'])
            && Schema::hasColumns('chi_tiet_don_hangs', ['id_don_hang', 'id_san_pham', 'so_luong'])) {
            $sold = (int) DB::table('chi_tiet_don_hangs as detail')->join('don_hangs as order', 'order.id', '=', 'detail.id_don_hang')
                ->where('detail.id_san_pham', $product->id)->where('detail.so_luong', '>', 0)
                ->where('order.trang_thai_don_hang', 'da_giao')->where('order.trang_thai_thanh_toan', 'da_thanh_toan')
                ->sum('detail.so_luong');
        }

        return response()->json(['status' => 1, 'data' => [
            'san_pham' => $product,
            'san_pham_lien_quan' => SanPham::catalog()->where('id_danh_muc', $product->id_danh_muc)->where('id', '!=', $product->id)->limit(4)->get(),
            'reviews' => $reviewList,
            'rating' => ['count' => (int) $summary->total, 'average' => $summary->total ? round((float) $summary->average, 1) : null],
            'sold_count' => $sold,
            'sold_definition' => 'Số lượng trong đơn đã giao và đã thanh toán, không gồm đơn hủy/hoàn tiền.',
            'specifications' => null,
            'size_guide' => null,
        ]]);
    }
}
