<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhMuc;
use App\Models\DonHang;
use App\Models\KhachHang;
use App\Models\SanPham;
use App\Models\ChiTietDonHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ThongKeController extends Controller
{
    public function getTongQuan()
    {
        $tong_doanh_thu = DonHang::where('trang_thai_don_hang', '!=', 'da_huy')
            ->sum('tong_thanh_toan');

        $tong_don_hang = DonHang::count();
        $don_hang_moi = DonHang::where('trang_thai_don_hang', 'cho_xac_nhan')->count();
        $tong_san_pham = SanPham::count();
        $tong_khach_hang = KhachHang::count();

        $don_hang_gan_day = DonHang::with('khachHang')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $top_san_pham = ChiTietDonHang::select(
                'ten_san_pham',
                DB::raw('SUM(so_luong) as tong_so_luong'),
                DB::raw('SUM(thanh_tien) as tong_doanh_thu')
            )
            ->groupBy('ten_san_pham')
            ->orderBy('tong_so_luong', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'status'  => 1,
            'message' => 'Lấy thống kê tổng quan thành công',
            'data'    => [
                'tong_doanh_thu'   => (float)$tong_doanh_thu,
                'tong_don_hang'    => $tong_don_hang,
                'don_hang_moi'     => $don_hang_moi,
                'tong_san_pham'    => $tong_san_pham,
                'tong_khach_hang'  => $tong_khach_hang,
                'don_hang_gan_day' => $don_hang_gan_day,
                'top_san_pham'     => $top_san_pham,
            ]
        ]);
    }

    public function getDoanhThuTheoThang(Request $request)
    {
        $nam = $request->nam ?? date('Y');

        $data = [];
        for ($thang = 1; $thang <= 12; $thang++) {
            $tong = DonHang::whereYear('created_at', $nam)
                ->whereMonth('created_at', $thang)
                ->where('trang_thai_don_hang', '!=', 'da_huy')
                ->sum('tong_thanh_toan');

            $so_don = DonHang::whereYear('created_at', $nam)
                ->whereMonth('created_at', $thang)
                ->where('trang_thai_don_hang', '!=', 'da_huy')
                ->count();

            $data[] = [
                'thang'          => 'T' . $thang,
                'thang_full'     => 'Tháng ' . $thang,
                'tong_doanh_thu' => (float)$tong,
                'so_don_hang'    => $so_don,
            ];
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Lấy doanh thu theo tháng thành công',
            'nam'     => $nam,
            'data'    => $data
        ]);
    }

    public function getDoanhThuTheoNam(Request $request)
    {
        $tu_nam = $request->tu_nam ?? (date('Y') - 4);
        $den_nam = $request->den_nam ?? date('Y');

        $data = [];
        for ($y = $tu_nam; $y <= $den_nam; $y++) {
            $tong = DonHang::whereYear('created_at', $y)
                ->where('trang_thai_don_hang', '!=', 'da_huy')
                ->sum('tong_thanh_toan');

            $data[] = [
                'nam'            => $y,
                'tong_doanh_thu' => (float)$tong,
            ];
        }

        return response()->json([
            'status'  => 1,
            'message' => 'Lấy doanh thu theo năm thành công',
            'data'    => $data
        ]);
    }

    public function getSanPhamTheoDanhMuc()
    {
        $data = DanhMuc::withCount('sanPhams')
            ->orderBy('san_phams_count', 'desc')
            ->get(['id', 'ten_danh_muc']);

        return response()->json([
            'status'  => 1,
            'message' => 'Lấy thống kê sản phẩm theo danh mục thành công',
            'data'    => $data
        ]);
    }
}
