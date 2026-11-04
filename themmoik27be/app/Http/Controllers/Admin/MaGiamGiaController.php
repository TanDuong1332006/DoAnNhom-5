<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaGiamGiaRequest;
use App\Models\MaGiamGia;
use App\Models\DonHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MaGiamGiaController extends Controller
{
    public function getData()
    {
        return response()->json(['status' => 1, 'message' => 'Lấy dữ liệu mã giảm giá thành công', 'data' => MaGiamGia::orderByDesc('id')->get(),
            'supports_eligibility' => Schema::hasColumn('ma_giam_gias', 'doi_tuong')]);
    }

    public function create(MaGiamGiaRequest $request)
    {
        MaGiamGia::create($request->validated());
        return response()->json(['status' => 1, 'message' => 'Thêm mã giảm giá thành công'], 201);
    }

    public function capNhat(MaGiamGiaRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $coupon = MaGiamGia::whereKey($request->id)->lockForUpdate()->firstOrFail();
            abort_if($request->so_luong < $coupon->so_luong_da_dung, 422, 'Tổng lượt không được nhỏ hơn số lượt đã sử dụng.');
            $data = $request->validated(); unset($data['id']);
            $coupon->update($data);
            return response()->json(['status' => 1, 'message' => 'Cập nhật mã giảm giá thành công']);
        });
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => 'required|integer|exists:ma_giam_gias,id']);
        return DB::transaction(function () use ($request) {
            $coupon = MaGiamGia::whereKey($request->id)->lockForUpdate()->firstOrFail();
            abort_if($coupon->so_luong_da_dung > 0 || DonHang::where('ma_giam_gia_su_dung', $coupon->ma_code)->exists(), 409, 'Mã có lịch sử sử dụng; hãy tạm khóa thay vì xóa.');
            $coupon->delete();
            return response()->json(['status' => 1, 'message' => 'Đã xóa mã chưa sử dụng']);
        });
    }

    public function doiTrangThai(Request $request)
    {
        $request->validate(['id' => 'required|integer|exists:ma_giam_gias,id']);
        return DB::transaction(function () use ($request) {
            $coupon = MaGiamGia::whereKey($request->id)->lockForUpdate()->firstOrFail();
            $coupon->update(['trang_thai' => $coupon->trang_thai == 1 ? 0 : 1]);
            return response()->json(['status' => 1, 'message' => 'Đã đổi trạng thái mã giảm giá']);
        });
    }
}
