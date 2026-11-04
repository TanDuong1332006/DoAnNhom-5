<?php

namespace App\Services;

use App\Models\{SanPham, ChiTietSanPham, DonHang, ChiTietDonHang, HoaDon, MaGiamGia, KhachHang};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class CheckoutService
{
    private function invalid(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }

    public function place(Request $request, bool $quote = false)
    {
        $data = $request->validate([
            'ho_ten' => ($quote ? 'sometimes' : 'required').'|string|max:255',
            'so_dien_thoai' => [$quote ? 'sometimes' : 'required', 'regex:/^[0-9+\s-]{9,20}$/'],
            'dia_chi_giao' => ($quote ? 'sometimes' : 'required').'|string|max:1000',
            'email' => 'nullable|email|max:255', 'ghi_chu' => 'nullable|string|max:1000',
            'phuong_thuc_thanh_toan' => 'nullable|in:COD,MB_BANK',
            'ma_giam_gia_su_dung' => 'nullable|string|max:50',
            'items' => 'required|array|min:1|max:100',
            'items.*.id' => 'required|integer|min:1',
            'items.*.id_chi_tiet_san_pham' => 'nullable|integer|min:1',
            'items.*.quantity' => 'required|integer|min:1|max:1000',
        ]);
        $token = $request->bearerToken() ? PersonalAccessToken::findToken($request->bearerToken()) : null;
        if ($request->bearerToken() && (!$token || ($token->expires_at && $token->expires_at->isPast())
            || (config('sanctum.expiration') && $token->created_at->lte(now()->subMinutes(config('sanctum.expiration')))))) {
            abort(401, 'Phiên đăng nhập đã hết hạn.');
        }
        $user = $token?->tokenable;
        if ($user && (!$user instanceof KhachHang || ($user->role ?: 'customer') !== 'customer' || $user->is_khoa || !$user->is_kich_hoat)) {
            abort(403, 'Tài khoản không được đặt hàng.');
        }

        $key = $request->header('Idempotency-Key');
        if ($key && !Str::isUuid($key)) $this->invalid('Idempotency-Key', 'Khóa đặt hàng không hợp lệ.');
        // Không để mã đơn công khai tiết lộ Idempotency-Key của checkout khách vãng lai.
        $codeOrder = 'HD_'.strtoupper(substr(hash_hmac('sha256', $key ?: (string) Str::uuid(), config('app.key')), 0, 32));
        try {
        return DB::transaction(function () use ($data, $user, $codeOrder, $quote) {
            if ($user) KhachHang::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $items = collect($data['items'])->sortBy(fn ($item) => sprintf('%020d-%020d', $item['id'], $item['id_chi_tiet_san_pham'] ?? 0));
            // Khóa cùng thứ tự trước khi kiểm tra retry; hai lần gửi cùng giỏ không trừ kho hai lần.
            SanPham::whereIn('id', $items->pluck('id')->unique())->orderBy('id')->lockForUpdate()->get();
            $existing = !$quote ? DonHang::where('ma_don_hang', $codeOrder)->first() : null;
            if ($existing) {
                abort_unless($existing->id_khach_hang == $user?->id, 409, 'Khóa đặt hàng đã được sử dụng.');
                if ($existing->checkout_fingerprint) abort_unless(hash_equals($existing->checkout_fingerprint, hash('sha256', json_encode($data))), 409, 'Nội dung đơn khác với lần gửi trước.');
                return $this->response($existing, HoaDon::where('ma_hoa_don', $codeOrder)->firstOrFail());
            }
            $lines = []; $subtotal = 0; $quantities = [];
            foreach ($items as $index => $item) {
                $field = "items.$index";
                $product = SanPham::where('id', $item['id'])->lockForUpdate()->first();
                if (!$product || $product->trang_thai != 1 || !$product->danhMuc || $product->danhMuc->trang_thai != 1) {
                    $this->invalid($field, 'Sản phẩm không còn kinh doanh.');
                }
                $variant = null;
                if (!empty($item['id_chi_tiet_san_pham'])) {
                    $variant = ChiTietSanPham::where('id', $item['id_chi_tiet_san_pham'])->where('id_san_pham', $product->id)->lockForUpdate()->first();
                    if (!$variant || $variant->trang_thai != 1
                        || ($variant->id_mau_sac && (!$variant->mauSac || $variant->mauSac->trang_thai != 1))
                        || ($variant->id_kich_thuoc && (!$variant->kichThuoc || $variant->kichThuoc->trang_thai != 1))) {
                        $this->invalid($field, 'Biến thể không hợp lệ hoặc đã ngừng bán.');
                    }
                } elseif ($product->chiTietSanPhams()->exists()) {
                    $this->invalid($field, 'Vui lòng chọn lại màu và kích thước sản phẩm.');
                }
                $stock = $variant ?: $product;
                $stockKey = $variant ? 'v'.$variant->id : 'p'.$product->id;
                $quantities[$stockKey] = ($quantities[$stockKey] ?? 0) + $item['quantity'];
                if ($stock->so_luong_ton < ($quote ? $quantities[$stockKey] : $item['quantity'])) $this->invalid($field, 'Sản phẩm không đủ tồn kho.');
                $price = round((($product->gia_khuyen_mai > 0 && $product->gia_khuyen_mai <= $product->gia_ban) ? $product->gia_khuyen_mai : $product->gia_ban) + ($variant?->gia_chenh_lech ?? 0), 2);
                if ($price < 0 || $price > 9999999999999.99) $this->invalid($field, 'Giá sản phẩm không hợp lệ.');
                $amount = round($price * $item['quantity'], 2);
                $subtotal += $amount;
                if ($subtotal > 9999999999999.99) $this->invalid('items', 'Tổng đơn hàng vượt giới hạn.');
                $lines[] = ['id_san_pham' => $product->id, 'id_chi_tiet_san_pham' => $variant?->id,
                    'ten_san_pham' => $product->ten_san_pham, 'hinh_anh' => $product->hinh_anh,
                    'so_luong' => $item['quantity'], 'gia_ban' => $price, 'thanh_tien' => $amount,
                    'ten_mau' => $variant?->mauSac?->ten_mau, 'ten_kich_thuoc' => $variant?->kichThuoc?->ten_kich_thuoc];
                // Trừ ngay trong transaction để các dòng trùng biến thể cũng kiểm tra kho đã giữ.
                if (!$quote) $stock->decrement('so_luong_ton', $item['quantity']);
            }
            $discount = 0; $code = strtoupper(trim($data['ma_giam_gia_su_dung'] ?? ''));
            if ($code !== '') {
                $coupon = MaGiamGia::where('ma_code', $code)->lockForUpdate()->first();
                if (!$coupon) $this->invalid('ma_giam_gia_su_dung', 'Mã giảm giá không tồn tại.');
                $coupon->assertCustomer($user);
                $discount = $coupon->discountFor($subtotal);
                if (!$quote) $coupon->increment('so_luong_da_dung');
            }
            $shipping = $subtotal >= 500000 ? 0 : 30000;
            $total = round(max(0, $subtotal - $discount) + $shipping, 2);
            if ($quote) return response()->json(['status' => 1, 'message' => $code ? 'Mã hợp lệ. Mức giảm được kiểm tra lại khi đặt hàng.' : 'Đã tính lại giỏ hàng.', 'data' => ['tong_tien_hang' => $subtotal, 'tien_giam_gia' => $discount, 'phi_van_chuyen' => $shipping, 'tong_thanh_toan' => $total]]);
            $order = DonHang::create([
                ...(Schema::hasColumn('don_hangs', 'ton_kho_da_giu') ? [
                    'ton_kho_da_giu' => true, 'voucher_da_giu' => $code !== '',
                    'checkout_fingerprint' => hash('sha256', json_encode($data)),
                ] : []),
                'id_khach_hang' => $user?->id, 'ma_don_hang' => $codeOrder,
                'ho_ten' => $data['ho_ten'], 'so_dien_thoai' => $data['so_dien_thoai'], 'email' => $data['email'] ?? null,
                'dia_chi_giao' => $data['dia_chi_giao'], 'ghi_chu' => $data['ghi_chu'] ?? null,
                'tong_tien_hang' => $subtotal, 'tien_giam_gia' => $discount, 'phi_van_chuyen' => $shipping,
                'ma_giam_gia_su_dung' => $code ?: null, 'tong_thanh_toan' => $total,
                'phuong_thuc_thanh_toan' => $data['phuong_thuc_thanh_toan'] ?? 'COD',
                'trang_thai_thanh_toan' => 'chua_thanh_toan', 'trang_thai_don_hang' => 'cho_xac_nhan',
            ]);
            foreach ($lines as $line) ChiTietDonHang::create(['id_don_hang' => $order->id] + $line);
            $invoice = HoaDon::create(['ma_hoa_don' => $codeOrder, 'tong_tien_hang' => $subtotal,
                'giam_gia' => $discount, 'phi_van_chuyen' => $shipping, 'tong_tien' => $total,
                'trang_thai' => 0, 'phuong_thuc_thanh_toan' => $order->phuong_thuc_thanh_toan,
                'ghi_chu' => 'Đơn demo SOFEP; không xác nhận thanh toán thật.']);
            return $this->response($order, $invoice);
        }, 3);
        } catch (\Illuminate\Database\QueryException $error) {
            // Hai request dùng cùng khóa nhưng giỏ khác nhau có thể không cùng khóa product.
            if (!$quote && $key && (string) $error->getCode() === '23000') {
                $existing = DonHang::where('ma_don_hang', $codeOrder)->first();
                if ($existing) {
                    abort_unless($existing->id_khach_hang == $user?->id && $existing->checkout_fingerprint
                        && hash_equals($existing->checkout_fingerprint, hash('sha256', json_encode($data))), 409, 'Khóa đặt hàng đã được dùng cho nội dung khác.');
                    return $this->response($existing, HoaDon::where('ma_hoa_don', $codeOrder)->firstOrFail());
                }
            }
            throw $error;
        }
    }

    private function response(DonHang $order, HoaDon $invoice)
    {
        return response()->json(['status' => 1, 'message' => 'Tạo đơn hàng demo thành công!',
                'don_hang' => $order, 'hoa_don' => $invoice, 'vietqr_url' => null, 'payment_token' => DemoPayment::token($order->ma_don_hang),
                'ngan_hang' => ['ten_ngan_hang' => 'Mô phỏng demo', 'so_tai_khoan' => '',
                    'chu_tai_khoan' => 'SOFEP DEMO', 'noi_dung_chuyen_khoan' => $order->ma_don_hang, 'so_tien' => $order->tong_thanh_toan]], 201);
    }
}
