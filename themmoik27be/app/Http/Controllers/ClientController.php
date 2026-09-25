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

    public function getChiTietSanPham($slug)
    {
        $san_pham = SanPham::with(['danhMuc', 'thuongHieu', 'chiTietSanPhams.mauSac', 'chiTietSanPhams.kichThuoc', 'anhSanPhams', 'danhGias.khachHang'])
            ->where(function ($query) use ($slug) {
                $query->where('slug_san_pham', $slug)
                      ->orWhere('id', $slug);
            })
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

    public function datHang(Request $request)
    {
        $request->validate([
            'ho_ten'                 => 'required|string|max:255',
            'so_dien_thoai'          => 'required|string|max:20',
            'dia_chi_giao'           => 'required|string',
            'tong_thanh_toan'        => 'required|numeric|min:0',
            'phuong_thuc_thanh_toan' => 'nullable|string',
        ]);

        // Tạo mã đơn hàng định dạng HD_xxxxx để khớp với regex MB Bank
        $soNgauNhien = rand(1000, 9999);
        $ma_don_hang = 'HD_' . $soNgauNhien;
        while (DonHang::where('ma_don_hang', $ma_don_hang)->exists() || HoaDon::where('ma_hoa_don', $ma_don_hang)->exists()) {
            $ma_don_hang = 'HD_' . rand(1000, 9999);
        }

        $phuongThuc = $request->phuong_thuc_thanh_toan ?: 'MB_BANK';
        $tongTien = (float)$request->tong_thanh_toan;

        $donHang = DonHang::create([
            'id_khach_hang'          => $request->id_khach_hang,
            'ma_don_hang'            => $ma_don_hang,
            'ho_ten'                 => $request->ho_ten,
            'so_dien_thoai'          => $request->so_dien_thoai,
            'email'                  => $request->email,
            'dia_chi_giao'           => $request->dia_chi_giao,
            'tong_tien_hang'         => $request->tong_tien_hang ?: $tongTien,
            'phi_van_chuyen'         => $request->phi_van_chuyen ?: 0,
            'tien_giam_gia'          => $request->tien_giam_gia ?: 0,
            'ma_giam_gia_su_dung'    => $request->ma_giam_gia_su_dung,
            'tong_thanh_toan'        => $tongTien,
            'phuong_thuc_thanh_toan' => $phuongThuc,
            'trang_thai_thanh_toan'  => 'chua_thanh_toan',
            'trang_thai_don_hang'    => 'cho_xac_nhan',
            'ghi_chu'                => $request->ghi_chu ?: 'Đơn hàng mua trực tuyến trên website URBN WEAR',
        ]);

        // Tạo chi tiết đơn hàng nếu có danh sách items
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $item) {
                ChiTietDonHang::create([
                    'id_don_hang'       => $donHang->id,
                    'id_san_pham'       => $item['id'] ?? 1,
                    'ten_san_pham'      => $item['ten_san_pham'] ?? 'Sản phẩm',
                    'hinh_anh'          => $item['hinh_anh'] ?? null,
                    'so_luong'          => $item['quantity'] ?? 1,
                    'gia_ban'           => $item['gia_ban'] ?? 0,
                    'thanh_tien'        => ($item['gia_ban'] ?? 0) * ($item['quantity'] ?? 1),
                    'ten_kich_thuoc'    => $item['size'] ?? null,
                    'ten_mau'           => $item['color'] ?? null,
                ]);
            }
        }

        // Tạo bản ghi tương ứng trong bảng hoa_dons
        $hoaDon = HoaDon::create([
            'ma_hoa_don'             => $ma_don_hang,
            'tong_tien_hang'         => $request->tong_tien_hang ?: $tongTien,
            'giam_gia'               => $request->tien_giam_gia ?: 0,
            'phi_van_chuyen'         => $request->phi_van_chuyen ?: 0,
            'tong_tien'              => $tongTien,
            'trang_thai'             => 0, // 0 = Chưa thanh toán
            'phuong_thuc_thanh_toan' => $phuongThuc,
            'ghi_chu'                => $request->ghi_chu ?: 'Khách hàng thanh toán qua VietQR MB Bank',
        ]);

        $stkMb = env('MB_ACCOUNT_NO', '0862296097');
        $vietqr_url = "https://img.vietqr.io/image/MB-{$stkMb}-compact2.png?amount={$tongTien}&addInfo={$ma_don_hang}&accountName=MB%20BANK";

        return response()->json([
            'status'     => 1,
            'message'    => 'Tạo đơn hàng thành công!',
            'don_hang'   => $donHang,
            'hoa_don'    => $hoaDon,
            'vietqr_url' => $vietqr_url,
            'ngan_hang'  => [
                'ten_ngan_hang'         => 'MB Bank (Ngân hàng Quân Đội)',
                'so_tai_khoan'          => $stkMb,
                'chu_tai_khoan'         => 'MB BANK OFFICIAL',
                'noi_dung_chuyen_khoan' => $ma_don_hang,
                'so_tien'               => $tongTien
            ]
        ], 201);
    }
}
