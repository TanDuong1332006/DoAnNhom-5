> Cập nhật: website chính đã được khôi phục về catalog MySQL doan_nhom_5 qua API 8000. Mở http://127.0.0.1:5173/. Server fixture 8015 đã dừng. Các hướng dẫn fixture trong phần dưới là lịch sử demo cũ; xem [bàn giao catalog hiện tại](SOFEP_CATALOG_RESTORE_HANDOFF.md) để chạy đúng website đầy đủ.

# Bàn giao authenticated checkout và thanh toán demo

## Nguyên nhân và thay đổi

Trước bản vá, POST /api/client/dat-hang không có auth:sanctum; CheckoutService cho phép user null. DemoPayment cho Admin đi qua và cho phép payment_token thay quyền sở hữu. Frontend mở form checkout không xác minh phiên.

Guest vẫn xem sản phẩm, chọn biến thể, thêm/sửa giỏ bằng cartStore hiện có. Khi mở checkout/Mua ngay, modal gọi API xác minh phiên Customer; Guest nhận lựa chọn Đăng nhập / Đăng ký / Quay lại giỏ hàng. sessionStorage chỉ lưu đường dẫn quay lại; localStorage giữ giỏ và variant ID. Đăng ký OTP hiện có không tạo phiên; sau OTP vẫn đăng nhập, rồi quay lại đường dẫn cũ và tự mở checkout. Không thêm lại sản phẩm khi quay về. Trước tạo đơn, frontend xác minh lại phiên; lỗi 401/403 ở tạo đơn, polling hoặc mô phỏng chuyển về yêu cầu đăng nhập. Giỏ chưa đặt vẫn giữ nguyên. Đơn đã tạo được tiếp tục qua mã đơn trong sessionStorage; không lưu payment_token tại đó nữa.

## Endpoint

- POST /api/client/dat-hang: auth:sanctum + RequireCustomer; server gắn id_khach_hang từ phiên, bỏ qua ID do client khai báo.
- POST /api/giao-dich/mo-phong: auth:sanctum + RequireCustomer + kiểm tra chủ đơn.
- GET /api/kiem-tra-thanh-toan/{ma_hoa_don}: cùng kiểm tra Customer và chủ đơn.
- /api/giao-dich: auth:sanctum + admin; đồng bộ ngân hàng thật vẫn bị từ chối.
- Các route Admin hiện hữu giữ middleware Admin. Seller cũ không được Customer checkout.

payment_token cũ còn trong response để giữ tương thích cấu trúc, nhưng không còn cấp quyền truy cập. Mô phỏng chỉ chạy local/testing.

## Trạng thái và tính toàn vẹn

Tạo đơn MB_BANK hoặc COD đều chua_thanh_toan. Mô phỏng success xác minh chủ đơn, phương thức MB_BANK, đơn chưa hủy, hóa đơn có tổng tiền/phương thức khớp; khóa đơn và hóa đơn trong transaction rồi ghi nhận thanh toán. ket_qua=failed trả success=false, giữ nguyên trạng thái. Gửi lại success không trừ tồn kho hoặc voucher thêm. Thanh toán không tự chuyển trạng thái xử lý đơn; Admin vẫn quản lý vòng đời đơn. UI đọc trạng thái đã thanh toán từ API kiểm tra đơn sau mô phỏng thành công.

Giữ nguyên tính giá, voucher, kiểm tra biến thể/tồn kho, giữ kho trong transaction và Idempotency-Key của CheckoutService. Không có giao dịch tiền thật.

## File sửa/tạo

- Fe/src/components/Client/CheckoutModal.vue: chặn Guest, xác minh phiên, quay lại auth, trạng thái server, nút mô phỏng thất bại.
- Fe/src/components/DangNhap/index.vue: Customer quay lại đường dẫn checkout đã lưu; Admin vẫn về /admin.
- Fe/tests/checkout-flow.test.mjs: fixture phiên, kiểm tra phiên hết hạn và ý định quay lại.
- themmoik27be/routes/api.php: middleware cho tạo đơn/thanh toán/đồng bộ.
- themmoik27be/app/Http/Middleware/RequireCustomer.php (mới): chỉ Customer đang kích hoạt, không khóa.
- themmoik27be/app/Services/CheckoutService.php: dùng danh tính Sanctum, bắt buộc phiên khi tạo đơn.
- themmoik27be/app/Services/DemoPayment.php: quyền sở hữu bắt buộc, bỏ fallback token và bypass Admin.
- themmoik27be/app/Http/Controllers/giaoDichController.php: mô phỏng thất bại, kiểm tra hóa đơn, tách trạng thái đơn và thanh toán.
- themmoik27be/tests/Feature/CheckoutIntegrityTest.php: hồi quy Customer, 401/403, chủ đơn, failed/success/retry/COD, xuất fixture demo tùy chọn.
- themmoik27be/tests/Feature/ProductDetailDemoTest.php: test checkout dùng Customer thật trong fixture.
- themmoik27be/tests/Support/checkout-browser-router.php (mới): API demo loopback có rào chắn fixture SQLite riêng, từ chối Admin và đăng ký/email thật.
- docs/SOFEP_AUTH_CHECKOUT_HANDOFF.md (mới): tài liệu này.

## Kiểm tra đã chạy

- node --test tests/*.test.mjs trong Fe: 44/44 đạt.
- npm run build trong Fe: đạt.
- php -l cho 8 file PHP sửa/tạo: đạt.
- php -d extension=pdo_sqlite vendor/bin/phpunit --no-progress trong themmoik27be: 49 test, 47 đạt, 2 bỏ qua (hai exporter fixture tùy chọn), 923 assertions. PHP báo extension pdo_sqlite đã được nạp; không ảnh hưởng kết quả.
- Export fixture checkout riêng bằng --filter test_export_checkout_fixture_for_browser và SOFEP_UAT_DIR: đạt 1 test, 4 assertions.
- HTTP UAT trên SQLite fixture riêng: đăng nhập Customer, tạo đơn với ID khách giả bị bỏ qua, đơn ban đầu chưa thanh toán, failed giữ chưa thanh toán, success hai lần vẫn đã thanh toán và trạng thái đơn cho_xac_nhan.
- Browser: Guest xem chi tiết, mở giỏ và checkout bị chặn, chuyển sang trang đăng nhập; modal xem ở desktop và mobile 390x844 không tràn ngang.
- git diff --check: đạt.

Chưa kiểm chứng: đăng ký OTP/email thật, luồng đăng nhập đến thanh toán hoàn chỉnh bằng browser, MySQL/concurrency thực tế. Test SQLite không chứng minh đầy đủ khóa transaction trên MySQL. Demo router chủ động chặn đăng ký để không gửi email ngoài môi trường test. Không chạy migrate/seeder, không sửa .env/dependency, không commit/push/deploy hoặc chạm database thật.

## Trải nghiệm Windows

Demo hiện chạy tại http://127.0.0.1:5173/san-pham/ao-mau với API http://127.0.0.1:8015/api trên SQLite tạm. Chọn màu Đen, size M, số lượng 1, Mua ngay. Nếu giỏ có sản phẩm từ demo cũ, chọn bỏ/chọn lại đúng sản phẩm fixture trước khi đặt.

Tài khoản fixture: customer@sofep.example.test. Mật khẩu ngẫu nhiên nằm trong trường password của session.json tại thư mục được ghi trong %TEMP%/sofep-checkout-demo-path.txt. Không đưa mật khẩu vào Git hoặc báo cáo. Có thể mở file này bằng trình soạn thảo để xem tại máy.

Sau đăng nhập, modal tự mở lại, chọn giao hàng, tạo đơn; thử Mô phỏng thất bại rồi Mô phỏng thanh toán. Tổng tiền fixture rất nhỏ và không đại diện catalog thật. Không chuyển tiền.

Nếu cần khởi động lại demo hiện tại, mở hai PowerShell:

```powershell
cd D:/do_an_nhom/code_chinh/DoAnNhom-5-GITHUB/themmoik27be
$env:SOFEP_UAT_DIR = (Get-Content "$env:TEMP/sofep-checkout-demo-path.txt").Trim()
php -S 127.0.0.1:8015 tests/Support/checkout-browser-router.php
```

```powershell
cd D:/do_an_nhom/code_chinh/DoAnNhom-5-GITHUB/Fe
$env:VITE_API_URL = 'http://127.0.0.1:8015/api'
npm run dev -- --host 127.0.0.1 --port 5173 --strictPort
```

Các biến trên chỉ áp dụng cho tiến trình PowerShell hiện tại, không sửa .env. Router từ chối chạy nếu không có manifest fixture đúng hoặc nếu có config cache. Dừng server bằng Ctrl+C khi xong. Thư mục fixture cần còn tồn tại để khởi động lại.
