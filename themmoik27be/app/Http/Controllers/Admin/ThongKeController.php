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
        $tong_doanh_thu = $this->completedPaid()
            ->sum('tong_thanh_toan');

        $tong_don_hang = DonHang::count();
        $don_hang_moi = DonHang::where('trang_thai_don_hang', 'cho_xac_nhan')->count();
        $tong_san_pham = SanPham::where('trang_thai', 1)->count();
        $tong_khach_hang = KhachHang::where(fn ($q) => $q->where('role', 'customer')->orWhereNull('role'))->where('is_khoa', 0)->where('is_kich_hoat', 1)->count();

        $don_hang_gan_day = DonHang::with('khachHang')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        $top_san_pham = ChiTietDonHang::select(
                'ten_san_pham',
                DB::raw('SUM(so_luong) as tong_so_luong'),
                DB::raw('SUM(thanh_tien) as tong_doanh_thu')
            )
            ->whereIn('id_don_hang', $this->completedPaid()->select('id'))
            ->groupBy('id_san_pham', 'ten_san_pham')
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

    private function completedPaid()
    {
        return DonHang::where('trang_thai_don_hang', 'da_giao')->where('trang_thai_thanh_toan', 'da_thanh_toan');
    }

    public function getDoanhThuTheoThang(Request $request)
    {
        $request->validate(['nam' => 'sometimes|integer|min:1900|max:2100']);
        $year = (int) ($request->nam ?? date('Y'));
        $month = DB::getDriverName() === 'sqlite' ? "CAST(strftime('%m', created_at) AS INTEGER)" : 'MONTH(created_at)';
        $rows = $this->completedPaid()->where('created_at', '>=', "$year-01-01")->where('created_at', '<', ($year + 1).'-01-01')
            ->selectRaw("$month AS period, SUM(tong_thanh_toan) AS revenue, COUNT(*) AS total")
            ->groupByRaw($month)->get()->keyBy('period');
        $data = [];
        foreach (range(1, 12) as $number) $data[] = ['thang' => 'T'.$number, 'thang_full' => 'Tháng '.$number,
            'tong_doanh_thu' => (float) ($rows[$number]->revenue ?? 0), 'so_don_hang' => (int) ($rows[$number]->total ?? 0)];
        return response()->json(['status' => 1, 'message' => 'Lấy doanh thu theo tháng thành công', 'nam' => $year, 'data' => $data]);
    }

    public function getDoanhThuTheoNam(Request $request)
    {
        $request->validate(['tu_nam' => 'sometimes|integer|min:1900|max:2100', 'den_nam' => 'sometimes|integer|min:1900|max:2100']);
        $from = (int) ($request->tu_nam ?? date('Y') - 4); $to = (int) ($request->den_nam ?? date('Y'));
        abort_if($to < $from || $to - $from > 50, 422, 'Khoảng thống kê phải từ 0 đến 50 năm.');
        $year = DB::getDriverName() === 'sqlite' ? "CAST(strftime('%Y', created_at) AS INTEGER)" : 'YEAR(created_at)';
        $rows = $this->completedPaid()->where('created_at', '>=', "$from-01-01")->where('created_at', '<', ($to + 1).'-01-01')
            ->selectRaw("$year AS period, SUM(tong_thanh_toan) AS revenue")->groupByRaw($year)->get()->keyBy('period');
        $data = [];
        foreach (range($from, $to) as $number) $data[] = ['nam' => $number, 'tong_doanh_thu' => (float) ($rows[$number]->revenue ?? 0)];
        return response()->json(['status' => 1, 'message' => 'Lấy doanh thu theo năm thành công', 'data' => $data]);
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
