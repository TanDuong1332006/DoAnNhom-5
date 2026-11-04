<?php

namespace App\Http\Controllers;

use App\Models\DanhMuc;
use App\Models\ThuongHieu;
use App\Models\SanPham;
use App\Models\DonHang;
use App\Models\ChiTietDonHang;
use App\Models\KhachHang;
use App\Models\MaGiamGia;
use App\Models\HoaDon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function getDanhMucs()
    {
        $data = DanhMuc::select('id', 'ten_danh_muc', 'slug_danh_muc', 'hinh_anh', 'mo_ta')
            ->where('trang_thai', 1)
            ->withCount(['sanPhams' => fn ($query) => $query->where('trang_thai', 1)])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['status' => 1, 'data' => $data]);
    }

    public function getTuyChonSanPham()
    {
        return response()->json([
            'status' => 1,
            'data' => [
                'mau_sacs' => \App\Models\MauSac::select('id', 'ten_mau', 'ma_mau')
                    ->where('trang_thai', 1)->orderBy('id', 'desc')->get(),
                'kich_thuocs' => \App\Models\KichThuoc::select('id', 'ten_kich_thuoc', 'mo_ta')
                    ->where('trang_thai', 1)->orderBy('id')->get(),
            ],
        ]);
    }

    public function getHomeData()
    {
        $danh_mucs = DanhMuc::where('trang_thai', 1)->take(8)->get();
        $thuong_hieus = ThuongHieu::where('trang_thai', 1)->take(6)->get();
        $san_pham_noi_bat = SanPham::catalog()
            ->where('trang_thai', 1)
            ->where('is_noi_bat', 1)
            ->take(8)
            ->get();
        $san_pham_moi = SanPham::catalog()
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
        $query = SanPham::catalog()->where('trang_thai', 1);

        // Lọc theo danh mục (ID hoặc Slug)
        if ($request->has('danh_muc') && $request->danh_muc && $request->danh_muc !== 'tat-ca') {
            $dm = $request->danh_muc;
            if (is_numeric($dm)) {
                $query->where('id_danh_muc', $dm);
            } else {
                $query->whereHas('danhMuc', function ($q) use ($dm) {
                    $q->where('slug_danh_muc', $dm)->orWhere('ten_danh_muc', 'like', '%' . $dm . '%');
                });
            }
        } elseif ($request->has('id_danh_muc') && $request->id_danh_muc && $request->id_danh_muc !== 'tat-ca') {
            $query->where('id_danh_muc', $request->id_danh_muc);
        }

        if ($request->has('id_thuong_hieu') && $request->id_thuong_hieu) {
            $query->where('id_thuong_hieu', $request->id_thuong_hieu);
        }

        if ($request->has('tu_khoa') && $request->tu_khoa) {
            $query->where('ten_san_pham', 'like', '%' . $request->tu_khoa . '%');
        }

        // Lọc Flash Sale
        if ($request->boolean('flash_sale') || $request->input('flash_sale') == 1) {
            $query->where(function ($q) {
                $q->where('is_flash_sale', 1)
                  ->orWhere(function ($sub) {
                      $sub->whereNotNull('gia_khuyen_mai')->where('gia_khuyen_mai', '>', 0);
                  });
            });
        }

        // Lọc Sản phẩm mới / Nổi bật
        if ($request->boolean('moi_nhat') || $request->input('moi_nhat') == 1) {
            $query->where('is_noi_bat', 1);
        }

        $request->validate(['per_page' => 'sometimes|integer|min:1|max:100']);
        $perPage = (int) $request->input('per_page', 50);
        if ($request->boolean('all')) {
            $san_phams = $query->orderBy('id', 'desc')->get();
        } else {
            $san_phams = $query->orderBy('id', 'desc')->paginate($perPage);
        }

        return response()->json([
            'status' => 1,
            'data'   => $san_phams
        ]);
    }

    public function getChiTietSanPham($slug, Request $request)
    {
        // Chế độ 2.1 chỉ đọc; Quick View cũ giữ nguyên hợp đồng API.
        if ($request->query('presentation') === '2') {
            return app(ProductDetailController::class)->show((string) $slug);
        }
        $san_pham = SanPham::catalog()->with(['anhSanPhams' => fn ($q) => $q->orderBy('thu_tu'), 'danhGias' => fn ($q) => $q->where('trang_thai', 1)->with('khachHang:id,ho_va_ten')])
            ->where(function ($query) use ($slug) {
                $query->where('slug_san_pham', $slug)
                      ->orWhere('id', $slug);
            })
            ->where('trang_thai', 1)
            ->first();

        if (!$san_pham) {
            return response()->json([
                'status'  => 0,
                'message' => 'Không tìm thấy sản phẩm'
            ], 404);
        }

        $san_pham->increment('luot_xem');

        $san_pham_lien_quan = SanPham::catalog()->where('id_danh_muc', $san_pham->id_danh_muc)
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

    public function datHang(Request $request)
    {
        return app(\App\Services\CheckoutService::class)->place($request);
    }

    public function kiemTraVoucher(Request $request)
    {
        return app(\App\Services\CheckoutService::class)->place($request, true);
    }
}
