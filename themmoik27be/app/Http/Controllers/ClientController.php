<?php

namespace App\Http\Controllers;

use App\Models\DanhMuc;
use App\Models\ThuongHieu;
use App\Models\SanPham;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\KhachHang;
use App\Models\MaGiamGia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function getHomeData()
    {
        $danh_mucs = DanhMuc::where('trang_thai', 1)->take(8)->get();
        $thuong_hieus = ThuongHieu::where('trang_thai', 1)->take(6)->get();
        $san_pham_noi_bat = SanPham::with(['danhMuc', 'thuongHieu'])
            ->where('trang_thai', 1)
            ->where('is_noi_bat', 1)
            ->take(8)
            ->get();
        $san_pham_moi = SanPham::with(['danhMuc', 'thuongHieu'])
            ->where('trang_thai', 1)
            ->orderBy('id', 'desc')
            ->take(8)
            ->get();

        return response()->json([
            'status' => 1,
            'data'   => [
                'danh_mucs'        => $danh_mucs,
                'thuong_hieus'     => $thuong_hieus,
                'san_pham_noi_bat' => $san_pham_noi_bat,
                'san_pham_moi'     => $san_pham_moi,
            ]
        ]);
    }

    public function getSanPhams(Request $request)
    {
        $query = SanPham::with(['danhMuc', 'thuongHieu'])->where('trang_thai', 1);

        if ($request->has('id_danh_muc') && $request->id_danh_muc) {
            $query->where('id_danh_muc', $request->id_danh_muc);
        }

        if ($request->has('id_thuong_hieu') && $request->id_thuong_hieu) {
            $query->where('id_thuong_hieu', $request->id_thuong_hieu);
        }

        if ($request->has('tu_khoa') && $request->tu_khoa) {
            $query->where('ten_san_pham', 'like', '%' . $request->tu_khoa . '%');
        }

        $san_phams = $query->orderBy('id', 'desc')->paginate(12);

        return response()->json([
            'status' => 1,
            'data'   => $san_phams
        ]);
    }

    public function getChiTietSanPham($slug)
    {
        $san_pham = SanPham::with(['danhMuc', 'thuongHieu', 'chiTietSanPhams.mauSac', 'chiTietSanPhams.kichThuoc', 'anhSanPhams', 'danhGias.khachHang'])
            ->where('slug_san_pham', $slug)
            ->first();

        if (!$san_pham) {
            return response()->json([
                'status'  => 0,
                'message' => 'Không tìm thấy sản phẩm'
            ], 404);
        }

        $san_pham->increment('luot_xem');

        $san_pham_lien_quan = SanPham::where('id_danh_muc', $san_pham->id_danh_muc)
            ->where('id', '!=', $san_pham->id)
            ->where('trang_thai', 1)
            ->take(4)
            ->get();

        return response()->json([
            'status' => 1,
            'data'   => [
                'san_pham'           => $san_pham,
                'san_pham_lien_quan' => $san_pham_lien_quan
            ]
        ]);
    }

    public function dangKy(Request $request)
    {
        $request->validate([
            'ho_va_ten' => 'required|string|max:255',
            'email'     => 'required|email|unique:khach_hangs,email',
            'mat_khau'  => 'required|string|min:6',
        ]);

        $khachHang = KhachHang::create([
            'ho_va_ten'    => $request->ho_va_ten,
            'email'        => $request->email,
            'so_dien_thoai'=> $request->so_dien_thoai,
            'mat_khau'     => Hash::make($request->mat_khau),
            'is_kich_hoat' => 1,
            'is_khoa'      => 0,
        ]);

        $token = $khachHang->createToken('customer_token')->plainTextToken;

        return response()->json([
            'status'  => 1,
            'message' => 'Đăng ký thành công',
            'token'   => $token,
            'user'    => $khachHang
        ], 201);
    }

    public function dangNhap(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'mat_khau' => 'required|string',
        ]);

        $khachHang = KhachHang::where('email', $request->email)->first();

        if (!$khachHang || !Hash::check($request->mat_khau, $khachHang->mat_khau)) {
            return response()->json([
                'status'  => 0,
                'message' => 'Email hoặc mật khẩu không chính xác'
            ], 401);
        }

        if ($khachHang->is_khoa == 1) {
            return response()->json([
                'status'  => 0,
                'message' => 'Tài khoản của bạn đã bị khóa'
            ], 403);
        }

        $token = $khachHang->createToken('customer_token')->plainTextToken;

        return response()->json([
            'status'  => 1,
            'message' => 'Đăng nhập thành công',
            'token'   => $token,
            'user'    => $khachHang
        ]);
    }
}
