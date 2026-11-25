# Khôi phục catalog đầy đủ và modal Customer checkout

## Kết quả hiện tại

Mở **http://127.0.0.1:5173/** (hoặc http://localhost:5173/). Frontend này nối **http://127.0.0.1:8000/api**, Laravel dùng MySQL **doan_nhom_5** trên loopback 3306. Không dùng database UAT cho website chính.

Nguyên nhân hiển thị Áo mẫu giá 80đ đã được xác nhận qua mã Vite đang phục vụ: biến VITE_API_URL của tiến trình cũ là http://127.0.0.1:8015/api. Cổng 8015 là PHP router checkout-browser-router.php, có /__uat/status xác nhận SOFEP disposable browser fixture / SQLite uat.sqlite. Đây là demo do agent mở ở lượt trước. Fe/.env vẫn trỏ http://127.0.0.1:8000/api nhưng biến tiến trình ghi đè nó. Không có bằng chứng catalog bị mất. Không phải lỗi do số cổng 5173.

Đã sao lưu Fe/.env, CheckoutModal.vue và metadata runtime vào:
C:/Users/dn133/AppData/Local/Temp/sofep-catalog-restore-20261106-153739

Đã dừng đúng tiến trình Vite cũ, khởi động lại cổng 5173 với API 8000, sau đó kiểm tra mã đang phục vụ xác nhận URL mới. Không sửa Fe/.env hoặc Laravel .env. Laravel không có bootstrap/cache/config.php; không chạy lệnh xóa cache. Đã dừng server SQLite 8015 và server MySQL UAT 8012 do agent mở, sau kiểm thử. Backend chính 8000 và frontend chính 5173 tiếp tục chạy. Không chạm tiến trình/database Barber.

## Phân biệt database và số liệu đọc trực tiếp

| Database | Tổng sản phẩm | Đang bán | Ảnh chính local | Biến thể | Dòng gallery |
|---|---:|---:|---:|---:|---:|
| doan_nhom_5 (catalog chính) | 168 | 168 | 167 | 500 | 514 |
| sofep_uat_20261103_0552eb8e0b6d (bản sao catalog UAT) | 167 | 167 | 167 | 493 | 512 |
| sofep_uat_20261103_43ebbecf81ed (fixture nhỏ) | 6 | 6 | 0 | 6 | 0 |
| sofep_uat_20261103_bb3ce6b8e317 (fixture nhỏ) | 6 | 6 | 0 | 6 | 0 |

SQLite tạm của demo trước là database khác hoàn toàn với các database MySQL trên. Chỉ truy vấn information_schema cho doan_nhom_5 và sofep_uat_*, không liệt kê/truy vấn Barber.

Catalog chính gồm 167 sản phẩm đã hoàn thiện và sản phẩm Áo polo ID 172 hiện có thêm. Không tự tạo sản phẩm này trong nhiệm vụ; không ẩn/xóa/chuyển trạng thái để ép tổng về 167. Tất cả 168 sản phẩm đang bán thuộc danh mục đang hoạt động. Có 500 biến thể hoạt động, không có biến thể mồ côi hoặc tồn kho âm qua truy vấn kiểm tra chỉ đọc.

## Ảnh, gallery và Product Detail 2.1

Script docs/sofep-restored-catalog-audit.mjs chỉ GET catalog, chi tiết presentation=2 và ảnh, ghi báo cáo JSON vào docs. Kết quả tại docs/sofep-restored-catalog-audit.json:

- 168/168 trang chi tiết thành công.
- 168/168 ảnh chính tải được và có chữ ký JPEG/PNG/WebP hợp lệ.
- Kiểm tra cả 514 dòng gallery; tổng hợp được 275 URL ảnh duy nhất, 275/275 hợp lệ kỹ thuật.
- API trả 500 biến thể đang hoạt động.
- Không sửa ảnh, giá, gallery, biến thể, tồn kho, danh mục hoặc dữ liệu Seller.

Đây là kiểm tra HTTP/chữ ký ảnh; không phải xác nhận ảnh đúng mẫu hàng thực tế. Nhãn ảnh minh họa và cách lọc gallery đã có của Product Detail được giữ nguyên.

Đã xem bằng browser ba sản phẩm xuất hiện trên trang chủ rồi mở chi tiết:

1. Áo polo /ao-polo: 300.000đ, giá gốc 350.000đ; hai ảnh gallery, đổi ảnh 1 → 2 thành công; màu Đen/Trắng.
2. Ô Dù Cầm Tay Mini Gấp Gọn: 220.000đ, giá gốc 280.000đ; chọn Đen/M, mã biến thể #494, Mua ngay mở yêu cầu đăng nhập, giữ sản phẩm trong giỏ.
3. Ghim Cài Áo Vest Nam Nữ: 240.000đ, giá gốc 310.000đ; màu Đen/Trắng/Xanh Navy.

Quick View được kiểm tra qua bộ regression frontend hiện có; chưa kiểm thử toàn bộ thao tác Quick View bằng browser trong lượt này.

## Luồng và modal mới

Guest xem/tìm sản phẩm, chọn biến thể và thêm/sửa/xóa giỏ như trước. Nhấn Mua ngay/Thanh toán mở hộp thoại tối giản theo ảnh tham chiếu: biểu tượng túi, tiêu đề Đăng nhập để tiếp tục mua hàng, mô tả giữ giỏ, đúng ba nút xếp dọc:

- Đăng nhập: tới trang Customer hiện tại; khi thành công quay lại đường dẫn đã lưu và mở checkout, không thêm lại sản phẩm.
- Đăng ký: tới đăng ký OTP hiện tại; xác thực OTP chưa tạo phiên thì chuyển tới đăng nhập. Cờ quay lại và giỏ vẫn được giữ.
- Quay lại cửa hàng: chỉ đóng modal, không xóa giỏ hoặc tạo đơn.

Màn hình auth không còn header thanh toán/QR hoặc nút đóng thứ tư. Đã xem bố cục desktop và mobile 390x844. Có trạng thái kiểm tra phiên, focus-visible và giảm animation theo prefers-reduced-motion.

Đã chặn thêm trường hợp khôi phục đơn cũ trên mounted: Guest không gọi API trạng thái thanh toán trước khi được backend xác minh Customer. API đặt hàng/thanh toán vẫn bắt buộc Sanctum + RequireCustomer; DemoPayment vẫn kiểm tra chủ đơn. Giá/voucher/tồn kho và idempotency giữ nguyên ở server. COD/failed không tự chuyển đã thanh toán; success mô phỏng mới cập nhật, không thay trạng thái xử lý đơn.

## File thay đổi trong lượt này

- Fe/src/components/Client/CheckoutModal.vue: modal theo ảnh, đúng ba nút; Quay lại cửa hàng chỉ đóng; chặn gọi payment khi khôi phục đơn mà chưa xác thực; điền profile từ phiên Customer.
- Fe/tests/checkout-flow.test.mjs: cập nhật mock khôi phục phiên; bổ sung Guest không gọi payment, giữ giỏ khi quay lại, Customer tiếp tục, OTP yêu cầu đăng nhập tiếp.
- Fe/tests/admin-login.test.mjs: thêm storage phiên và kiểm tra Customer quay lại checkout, giữ giỏ.
- docs/sofep-restored-catalog-audit.mjs + .json (mới): kiểm tra chỉ đọc toàn catalog/ảnh.
- docs/sofep-auth-catalog-mysql-uat.mjs + .json (mới): kiểm thử ghi chỉ trên UAT đã xác minh tên/purpose, không tạo/migrate/seed database.
- docs/SOFEP_CATALOG_RESTORE_HANDOFF.md (mới): báo cáo này.
- docs/SOFEP_AUTH_CHECKOUT_HANDOFF.md: thêm thông báo hướng dẫn demo fixture cũ đã được thay bằng catalog chính.

Các source backend bảo vệ checkout ở lượt trước được giữ nguyên. Các tài liệu/thay đổi có sẵn khác không bị hoàn tác. Không cài dependency, không đổi lockfile, không commit/push/deploy.

## Kiểm thử

| Kiểm tra | Kết quả |
|---|---|
| node --test Fe/tests/*.test.mjs từ gốc repo | 49/49 đạt |
| npm run build trong Fe | Đạt |
| php -d extension=pdo_sqlite vendor/bin/phpunit --no-progress | 49 test: 47 đạt, 2 exporter fixture tùy chọn bỏ qua; 923 assertions |
| Guest gọi ba API tạo đơn/mô phỏng/trạng thái trên backend chính 8000 | Cả ba trả 401; không tạo đơn |
| Audit HTTP toàn catalog chính, chỉ đọc | 168 detail, 168 ảnh chính, 275 ảnh duy nhất hợp lệ |
| node docs/sofep-auth-catalog-mysql-uat.mjs <session UAT> | Đạt trên sofep_uat_20261103_0552eb8e0b6d |
| Browser | Trang chủ 168, ba chi tiết, gallery, chọn màu/size, Guest Mua ngay, quay lại giữ giỏ, modal desktop/mobile |
| git diff --check | Đạt |

MySQL UAT tạo đơn ID 2 và 3 trong database UAT hiện có. Đã kiểm tra Guest 401; Admin 403; lấy Customer ID từ server; tính giá/voucher; gửi lại cùng Idempotency-Key trả cùng đơn; failed chưa thanh toán; success hai lần vẫn chỉ giữ kho một lần; COD chưa thanh toán; Admin thấy cả hai đơn. Đăng xuất token test khi xong. Không có ghi vào doan_nhom_5, không migrate/seed/xóa database nào.

OTP được test tự động với API giả ở frontend và Mail fake/SQLite trong backend. Chưa gửi email OTP thật hoặc hoàn tất đăng nhập/OTP đến thanh toán bằng browser trên catalog chính, vì không dùng tài khoản thật/không tạo đơn thử ở đó. Test Customer khác và Seller bị từ chối nằm trong backend regression cô lập. Chưa chạy tải đồng thời trên MySQL.

## Mở và khởi động lại đúng website

Website đầy đủ: **http://127.0.0.1:5173/** hoặc **http://localhost:5173/**.

Nếu trang cũ còn giữ dữ liệu/module trong tab đang mở, dùng Ctrl+F5. Các dòng sản phẩm UAT từng có trong localStorage của giỏ không bị tự xóa; có thể bỏ riêng dòng cũ bằng nút xóa trong giỏ. Chúng không đại diện catalog đang phục vụ.

Nếu server dừng, mở hai terminal PowerShell riêng:

```powershell
cd D:/do_an_nhom/code_chinh/DoAnNhom-5-GITHUB/themmoik27be
php artisan serve --host=127.0.0.1 --port=8000
```

```powershell
cd D:/do_an_nhom/code_chinh/DoAnNhom-5-GITHUB/Fe
$env:VITE_API_URL = 'http://127.0.0.1:8000/api'
npm run dev -- --host 127.0.0.1 --port 5173 --strictPort
```

Không khởi động lại fixture 8015 hoặc đặt VITE_API_URL về API UAT nếu đang muốn xem catalog chính. Biến trên chỉ thuộc terminal hiện tại; không thay đổi .env.
