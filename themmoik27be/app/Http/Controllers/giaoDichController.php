<?php

namespace App\Http\Controllers;

use App\Models\DonHang;
use App\Models\GiaoDich;
use App\Models\HoaDon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class giaoDichController extends Controller
{
    /**
     * Đồng bộ dữ liệu giao dịch từ API MB Bank
     * Route: GET|POST /api/giao-dich
     */
    public function getData()
    {
        $client = new Client([
            'timeout' => 8,
            'verify'  => false
        ]);

        $username   = env('MB_USERNAME', '0862296097');
        $password   = env('MB_PASSWORD', '');
        $accountNo  = env('MB_ACCOUNT_NO', '0862296097');
        $dayBegin   = Carbon::today()->format('d/m/Y');
        $dayEnd     = Carbon::today()->format('d/m/Y');

        $payload = [
            "USERNAME"  => $username,
            "PASSWORD"  => $password,
            "DAY_BEGIN" => $dayBegin,
            "DAY_END"   => $dayEnd,
            "NUMBER_MB" => $accountNo
        ];

        // Danh sách endpoint dự phòng theo tài liệu và môi trường
        $urls = [
            'http://103.173.254.8:2603/mb',
            'https://mb.dzfullstack.com/api/transactions',
            'https://api-mb.midstack.io.vn/api/transactions'
        ];

        $responseBody = null;
        $usedUrl = null;
        $lastError = null;

        foreach ($urls as $url) {
            try {
                $response = $client->post($url, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept'       => 'application/json'
                    ],
                    'json' => $payload
                ]);

                if ($response->getStatusCode() === 200) {
                    $responseBody = json_decode($response->getBody(), true);
                    $usedUrl = $url;
                    break;
                }
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                continue;
            }
        }

        // Xử lý các giao dịch nhận được từ API
        $da_xu_ly = [];
        $giao_dich_moi = 0;

        if ($responseBody) {
            $transactions = $responseBody['data']['transactionHistoryList'] 
                            ?? $responseBody['data'] 
                            ?? [];

            if (is_array($transactions)) {
                foreach ($transactions as $index => $value) {
                    if (!isset($value['description'])) {
                        continue;
                    }

                    // Tách mã hóa đơn từ nội dung chuyển khoản bằng Regex
                    if (preg_match('/\bHD\s*_?-?\s*(\d+)\b/i', $value['description'], $matches)) {
                        $value['ma_hoa_don'] = 'HD_' . $matches[1];
                    } else {
                        $value['ma_hoa_don'] = null;
                    }

                    $creditAmount = (int)($value['creditAmount'] ?? 0);
                    $refNo = $value['refNo'] ?? ($value['docId'] ?? (time() . '_' . $index));

                    // Lưu vào bảng giao_dichs nếu chưa tồn tại
                    $giaoDich = GiaoDich::firstOrCreate(
                        ['refNo' => $refNo],
                        [
                            'description'  => $value['description'],
                            'ma_hoa_don'   => $value['ma_hoa_don'],
                            'creditAmount' => $creditAmount,
                        ]
                    );

                    if ($giaoDich->wasRecentlyCreated) {
                        $giao_dich_moi++;
                    }

                    // Tự động đối soát và kích hoạt đơn hàng / hóa đơn
                    if ($value['ma_hoa_don'] && $creditAmount > 0) {
                        // 1. Cập nhật bảng hoa_dons
                        $hoa_don = HoaDon::where('ma_hoa_don', $value['ma_hoa_don'])
                            ->where('trang_thai', 0)
                            ->first();

                        if ($hoa_don && $creditAmount >= $hoa_don->tong_tien) {
                            $hoa_don->trang_thai = 1;
                            $hoa_don->save();
                            $da_xu_ly[] = $hoa_don->ma_hoa_don;
                        }

                        // 2. Cập nhật bảng don_hangs
                        $don_hang = DonHang::where('ma_don_hang', $value['ma_hoa_don'])
                            ->where('trang_thai_thanh_toan', '!=', 'da_thanh_toan')
                            ->first();

                        if ($don_hang && $creditAmount >= $don_hang->tong_thanh_toan) {
                            $don_hang->trang_thai_thanh_toan = 'da_thanh_toan';
                            $don_hang->trang_thai_don_hang   = 'da_xac_nhan';
                            $don_hang->save();
                            $da_xu_ly[] = $don_hang->ma_don_hang;
                        }
                    }
                }
            }

            return response()->json([
                'success'       => true,
                'status'        => 1,
                'message'       => 'Đã quét và đồng bộ giao dịch MB Bank thành công!',
                'used_url'      => $usedUrl,
                'giao_dich_moi' => $giao_dich_moi,
                'don_da_xu_ly'  => array_unique($da_xu_ly),
                'data'          => $responseBody
            ]);
        }

        // Trường hợp API MB bên ngoài chưa bật hoặc đang chờ kích hoạt
        return response()->json([
            'success'   => false,
            'status'    => 0,
            'message'   => 'Không thể kết nối đến máy chủ MB Bank API: ' . ($lastError ?? 'Timeout'),
            'tip'       => 'Tài khoản MB Bank cần được Admin/Thầy kích hoạt theo hướng dẫn trong file docs.',
            'mock_test' => 'Có thể sử dụng API /api/giao-dich/mo-phong để kiểm thử thanh toán ngay.'
        ], 200);
    }

    /**
     * API kiểm tra trạng thái thanh toán của 1 hóa đơn/đơn hàng
     * Route: GET /api/kiem-tra-thanh-toan/{ma_hoa_don}
     */
    public function kiemTraThanhToan($ma_hoa_don)
    {
        $hoa_don = HoaDon::where('ma_hoa_don', $ma_hoa_don)->first();
        $don_hang = DonHang::where('ma_don_hang', $ma_hoa_don)->first();

        $da_thanh_toan = false;
        if ($hoa_don && $hoa_don->trang_thai == 1) {
            $da_thanh_toan = true;
        }
        if ($don_hang && $don_hang->trang_thai_thanh_toan === 'da_thanh_toan') {
            $da_thanh_toan = true;
        }

        return response()->json([
            'status'        => 1,
            'ma_hoa_don'    => $ma_hoa_don,
            'da_thanh_toan' => $da_thanh_toan,
            'hoa_don'       => $hoa_don,
            'don_hang'      => $don_hang
        ]);
    }

    /**
     * API mô phỏng thanh toán thành công (dành cho chấm điểm, demo & test local)
     * Route: POST /api/giao-dich/mo-phong
     */
    public function moPhongThanhToan(Request $request)
    {
        $request->validate([
            'ma_hoa_don' => 'required|string',
        ]);

        $ma_hoa_don = $request->ma_hoa_don;
        $so_tien    = (int)($request->so_tien ?? 249000);

        // Lưu vào bảng giao_dichs
        $giaoDich = GiaoDich::create([
            'description'  => "MB BANK TEST - CHUYEN KHOAN {$ma_hoa_don} THANH TOAN DON HANG",
            'ma_hoa_don'   => $ma_hoa_don,
            'creditAmount' => $so_tien,
            'refNo'        => 'MB_MOCK_' . time() . '_' . rand(100, 999)
        ]);

        // Cập nhật hóa đơn
        HoaDon::where('ma_hoa_don', $ma_hoa_don)->update(['trang_thai' => 1]);

        // Cập nhật đơn hàng
        DonHang::where('ma_don_hang', $ma_hoa_don)->update([
            'trang_thai_thanh_toan' => 'da_thanh_toan',
            'trang_thai_don_hang'   => 'da_xac_nhan'
        ]);

        return response()->json([
            'status'     => 1,
            'success'    => true,
            'message'    => "Mô phỏng thanh toán MB Bank thành công cho đơn {$ma_hoa_don}!",
            'giao_dich'  => $giaoDich
        ]);
    }

    /**
     * Lấy danh sách toàn bộ giao dịch MB Bank đã lưu
     * Route: GET /api/admin/giao-dich/get-data
     */
    public function layDanhSachGiaoDich()
    {
        $giao_dichs = GiaoDich::orderBy('id', 'desc')->get();

        return response()->json([
            'status' => 1,
            'data'   => $giao_dichs
        ]);
    }
}

