<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    // Chỉ công bố dữ liệu đơn của Customer đã xác thực, không trả fingerprint/token.
    private const FIELDS = ['id', 'ma_don_hang', 'ho_ten', 'so_dien_thoai', 'dia_chi_giao',
        'tong_tien_hang', 'phi_van_chuyen', 'tien_giam_gia', 'tong_thanh_toan',
        'phuong_thuc_thanh_toan', 'trang_thai_thanh_toan', 'trang_thai_don_hang', 'created_at'];

    public function index(Request $request)
    {
        $data = $request->validate(['page' => 'sometimes|integer|min:1', 'per_page' => 'sometimes|integer|min:1|max:50']);
        $orders = DonHang::where('id_khach_hang', $request->user()->id)
            ->orderByDesc('id')->paginate($data['per_page'] ?? 10, self::FIELDS);

        return response()->json(['status' => 1, 'data' => $orders]);
    }

    public function show(Request $request, int $id)
    {
        $order = DonHang::where('id_khach_hang', $request->user()->id)
            ->select(self::FIELDS)->findOrFail($id);
        $order->load(['chiTiets' => fn ($query) => $query->select('id', 'id_don_hang',
            'ten_san_pham', 'ten_mau', 'ten_kich_thuoc', 'gia_ban', 'so_luong', 'thanh_tien')]);

        return response()->json(['status' => 1, 'data' => $order]);
    }
}
