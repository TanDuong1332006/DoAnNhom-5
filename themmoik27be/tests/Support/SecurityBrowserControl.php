<?php

// Chỉ điều khiển fixture UAT đã xác minh; không nhận database/email tùy ý từ CLI.
require __DIR__.'/SecurityBrowserBoot.php';
$session = json_decode(file_get_contents(trim(file_get_contents(sys_get_temp_dir().'/sofep-n5147-browser-session-path.txt'))), true);
$app = securityBrowserBoot($session);
$mode = $argv[1] ?? '';
if ($mode === 'expire-mobile-registration') {
    $n = App\Models\KhachHang::where('email', $session['mobile'])->where('is_kich_hoat', 0)->update(['otp_expires_at' => now()->subSecond()]);
    echo json_encode(['uat_only' => true, 'fixture_rows_expired' => $n]).PHP_EOL; exit;
}
$mail = array_map(fn ($l) => json_decode($l, true), file($session['root'].'/mailbox.jsonl', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
$request = function ($label, $path, $data, $expected, $token = null, $method = 'POST') use ($app, $session) {
    $app['auth']->forgetGuards();
    $r = Illuminate\Http\Request::create('/api/'.$path, $method, $data, [], [], ['HTTP_ACCEPT' => 'application/json', 'REMOTE_ADDR' => '127.0.0.1']);
    if ($token) $r->headers->set('Authorization', 'Bearer '.$token);
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class); $response = $kernel->handle($r); $kernel->terminate($r, $response);
    $item = ['case' => $label, 'path' => $path, 'expected' => $expected, 'actual' => $response->getStatusCode(), 'passed' => $response->getStatusCode() === $expected];
    file_put_contents(__DIR__.'/../../../docs/N5-147/browser-api-regression.jsonl', json_encode($item).PHP_EOL, FILE_APPEND | LOCK_EX);
    if (!$item['passed']) throw new RuntimeException('UAT case failed: '.$label);
    return json_decode($response->getContent(), true);
};
$customer = App\Models\KhachHang::where('email', $session['customer'])->firstOrFail();
if ($mode === 'replay-registration') {
    $m = array_values(array_filter($mail, fn ($m) => in_array($session['customer'], $m['to'], true) && str_contains($m['subject'], 'đăng ký')))[0];
    preg_match('/(?<!\d)\d{6}(?!\d)/', $m['text'] ?? '', $match);
    $request('used_registration_otp', 'xac-thuc-otp', ['email' => $customer->email, 'otp' => $match[0]], 422);
    echo 'registration replay rejected'.PHP_EOL; exit;
}
if ($mode === 'reset-and-regression') {
    $old = $customer->createToken('pre-reset-probe')->plainTextToken;
    $m = array_values(array_filter($mail, fn ($m) => in_array($session['customer'], $m['to'], true) && str_contains($m['subject'], 'quên mật khẩu')));
    $m = end($m); preg_match('/(?<!\d)\d{6}(?!\d)/', strip_tags(($m['text'] ?? '').' '.($m['html'] ?? '')), $match);
    if (!$match) throw new RuntimeException('No reset code in simulated mailbox.');
    $data = ['email' => $customer->email, 'otp' => $match[0], 'password' => 'Uat-browser-147-reset!', 'password_confirmation' => 'Uat-browser-147-reset!'];
    $request('pre_reset_token_valid', 'kiem-tra-dang-nhap', [], 200, $old, 'GET');
    $request('password_reset_from_simulated_email', 'forgot-password/reset-password', $data, 200);
    $request('old_token_revoked', 'kiem-tra-dang-nhap', [], 401, $old, 'GET');
    $request('reset_otp_replay', 'forgot-password/reset-password', $data, 400);
    $login = $request('login_new_password', 'dang-nhap', ['email' => $customer->email, 'mat_khau' => $data['password']], 200);
    $request('customer_admin_api_denied', 'admin/danh-muc/get-data', [], 403, $login['token'], 'GET');
    $request('guest_checkout_api_denied', 'client/dat-hang', [], 401);
    $admin = App\Models\User::where('email', $session['admin'])->firstOrFail()->createToken('uat-admin-probe')->plainTextToken;
    $request('admin_api_allowed', 'admin/don-hang/get-data', [], 200, $admin, 'GET');
    $order = App\Models\DonHang::where('id_khach_hang', $customer->id)->latest('id')->firstOrFail();
    echo json_encode(['database' => $session['database'], 'order_id' => $order->id,
        'payment' => $order->trang_thai_thanh_toan, 'method' => $order->phuong_thuc_thanh_toan,
        'total' => $order->tong_thanh_toan, 'stock' => App\Models\ChiTietSanPham::findOrFail($session['variant_id'])->so_luong_ton]).PHP_EOL; exit;
}
throw new RuntimeException('Unknown UAT fixture operation.');
