<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use App\Models\HoaDon;
use App\Models\GiaoDich;
use App\Services\DemoPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class giaoDichController extends Controller
{
    public function getData()
    {
        return response()->json(['status' => 0, 'success' => false, 'message' => 'SOFEP demo không kết nối hoặc đồng bộ ngân hàng thật.'], 403);
    }

    public function kiemTraThanhToan(Request $request, $ma_hoa_don)
    {
        $order = DonHang::where('ma_don_hang', $ma_hoa_don)->firstOrFail();
        DemoPayment::authorize($request, $order);
        return response()->json(['status' => 1, 'ma_hoa_don' => $ma_hoa_don,
            'da_thanh_toan' => $order->trang_thai_thanh_toan === 'da_thanh_toan',
            'don_hang' => $order, 'hoa_don' => HoaDon::where('ma_hoa_don', $ma_hoa_don)->first()]);
    }

    public function moPhongThanhToan(Request $request)
    {
        abort_unless(app()->environment(['local', 'testing']), 403, 'Mô phỏng chỉ dùng trong môi trường local/testing.');
        $request->validate(['ma_hoa_don' => 'required|string|max:255']);
        return DB::transaction(function () use ($request) {
            $order = DonHang::where('ma_don_hang', $request->ma_hoa_don)->lockForUpdate()->firstOrFail();
            DemoPayment::authorize($request, $order);
            abort_if($order->trang_thai_don_hang === 'da_huy' || $order->phuong_thuc_thanh_toan !== 'MB_BANK', 409, 'Đơn hàng không thể mô phỏng thanh toán.');
            if ($order->trang_thai_thanh_toan !== 'da_thanh_toan') {
                $invoice = HoaDon::where('ma_hoa_don', $order->ma_don_hang)->lockForUpdate()->firstOrFail();
                $invoice->update(['trang_thai' => 1]);
                $order->update(['trang_thai_thanh_toan' => 'da_thanh_toan', 'trang_thai_don_hang' => 'da_xac_nhan']);
            }
            return response()->json(['status' => 1, 'success' => true, 'message' => 'Đã ghi nhận thanh toán mô phỏng; không có giao dịch tiền thật.']);
        });
    }

    public function layDanhSachGiaoDich()
    {
        return response()->json(['status' => 1, 'data' => GiaoDich::orderByDesc('id')->get()]);
    }
}
