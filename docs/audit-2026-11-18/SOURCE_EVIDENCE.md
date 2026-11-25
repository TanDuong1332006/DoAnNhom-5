# Trích đoạn bằng chứng source ngày 18/11/2026

Số dòng thuộc working tree, không phải HEAD độc lập. Không chứa dữ liệu môi trường/bí mật.

## themmoik27be/app/Http/Controllers/Api/ForgotPasswordController.php

```text
24: $otp = rand(100000, 999999); // Random 6 số
43: $cachedOtp = Cache::get('otp_reset_' . $request->email);
62: $cachedOtp = Cache::get('otp_reset_' . $request->email);
71: $user->save();
74: Cache::forget('otp_reset_' . $request->email);
```

## themmoik27be/app/Http/Controllers/AuthController.php

```text
58: 'otp_expires_at' => now()->addMinutes(10),
92: public function xacThucOtp(Request $request)
116: !$customer->otp_expires_at ||
117: now()->greaterThan($customer->otp_expires_at)
137: $customer->otp_expires_at = null;
185: $token = $customer->createToken('khach_hang_token')->plainTextToken;
208: $token = $admin->createToken('admin_token')->plainTextToken;
```

## themmoik27be/app/Services/OrderLifecycle.php

```text
22: abort_if($payment === 'hoan_tien' && $order->trang_thai_thanh_toan !== 'hoan_tien', 409, 'Hoàn tiền cần quy trình riêng.');
25: abort_unless($order->ton_kho_da_giu, 409, 'Đơn lịch sử chưa xác minh giữ kho; không tự hoàn tồn kho.');
37: $order->ton_kho_da_giu = false; $order->voucher_da_giu = false;
```

## Fe/src/components/Client/TimKiem.vue

```text
174: :class="sortBy === 'ban_chay' ? 'active-sort-pill' : 'inactive-sort-pill'"
501: const response=await axios.get('http://127.0.0.1:8000/api/client/san-phams?all=1');
529: if (this.sortBy === 'ban_chay') {
530: return arr.sort((a, b) => (b.luot_xem || 0) - (a.luot_xem || 0));
```

## themmoik27be/app/Models/SanPham.php

```text
32: public function scopeCatalog($query)
34: return $query->where('trang_thai', 1)->withCount('chiTietSanPhams')->with([
36: 'chiTietSanPhams' => fn ($q) => $q->where('trang_thai', 1)
37: ->where(fn ($c) => $c->whereNull('id_mau_sac')->orWhereHas('mauSac', fn ($m) => $m->where('trang_thai', 1)))
38: ->where(fn ($c) => $c->whereNull('id_kich_thuoc')->orWhereHas('kichThuoc', fn ($m) => $m->where('trang_thai', 1)))
```

## themmoik27be/app/Http/Middleware/AdminMiddleware.php

```text
14: if (!$user || !($user instanceof \App\Models\User)) {
```

## themmoik27be/config/sanctum.php

```text
53: 'expiration' => null,
```

## themmoik27be/app/Http/Controllers/giaoDichController.php

```text
30: abort_unless(app()->environment(['local', 'testing']), 403, 'Mô phỏng chỉ dùng trong môi trường local/testing.');
```
