# Bộ ảnh SOFEP demo chuyển máy

Chỉ dành cho UAT local, không nhập vào database thật. Bộ ảnh gồm 167 ảnh AI có SHA-256 khớp manifest nguồn tạo; không phải ảnh chụp SKU. Giao diện bán hàng phải giữ placeholder đối với ảnh chưa xác minh. Dùng `/__demo/images` để xem rõ ảnh demo và kiểm tra khôi phục, không coi đây là nghiệm thu ảnh hàng thật.

## Chuẩn bị và import

1. Clone/giải nén source sạch, dùng PHP/MySQL tương thích lockfile hiện có. Cung cấp dependency Laravel khớp `composer.lock` theo quy trình của nhóm; không chạy `composer setup`. Bộ gói không chứa vendor, `.env`, tài khoản, token hoặc mật khẩu. Công cụ yêu cầu thư mục Laravel sạch không có `.env`/config cache và một thư mục runtime mới.
2. Giải nén gói demo ra thư mục riêng. Khai báo thông tin kết nối MySQL local trong biến môi trường của tiến trình `SOFEP_DEMO_DB_HOST`, `SOFEP_DEMO_DB_PORT`, `SOFEP_DEMO_DB_USERNAME`, `SOFEP_DEMO_DB_PASSWORD`. Mặc định host `127.0.0.1`, port `3306`, username `root`, password trống; chỉ dùng mặc định nếu phù hợp máy của bạn. Không lưu hoặc gửi credential cùng gói. User MySQL cần quyền CREATE database mới.
3. Chạy `php import.php <đường-dẫn-tuyệt-đối-themmoik27be-sạch> <thư-mục-runtime-mới>`. Script tự tạo database tên ngẫu nhiên `sofep_image_restore_YYYYMMDD_<random>`, xác minh connection, chạy migration từ source sạch CHỈ vào database mới, import catalog allowlist và copy ảnh vào storage runtime riêng. Không nhận tên database nguồn/đích do người dùng nhập; không DROP/TRUNCATE/DELETE, không chạy lại vào database có sẵn.
4. Đặt biến tiến trình `SOFEP_DEMO_SESSION` tới `<runtime>/session.json`; giữ các biến kết nối MySQL của bước 2 trong tiến trình server. Chạy `php -S 127.0.0.1:8038 server.php` tại thư mục gói. Có thể đổi port; tránh dùng port đang phục vụ app thật. Redirect stdout/stderr vào file runtime nếu chạy nền, giữ cửa sổ ẩn khi khởi động qua PowerShell.
5. Chạy `node verify.mjs http://127.0.0.1:8038 <đường-dẫn-report.json>`, rồi mở `http://127.0.0.1:8038/__demo/images`. API Laravel gốc `/api/product-images/...`, `/api/client/san-phams?all=1`, `/api/client/demo/san-pham/{id}` được kiểm tra qua HTTP thật. Route `__demo` chỉ là harness loopback, không thêm vào backend source hay triển khai production.

## Catalog và dữ liệu loại trừ

Gói chỉ chứa danh mục, thương hiệu, màu, size, sản phẩm, gallery và biến thể. Schema Laravel có thể tạo bảng tài khoản/đơn/token rỗng; không import bất kỳ bản ghi nào vào các bảng đó. Dữ liệu catalog dùng URL tương đối, không có origin localhost cố định. Runtime cấu hình database/cache/session/storage/mail riêng trong bộ nhớ, không ghi `.env` và không gửi email.

167 ảnh chính/gallery AI dùng để kiểm thử truyền file. Sản phẩm 172 dùng placeholder vì ảnh upload và URL ngoài chưa xác minh quyền dùng. 9 liên kết ảnh biến thể của sản phẩm này được để null trong DEMO; không thay đổi database chính. Có thêm đúng 1 biến thể tổng hợp không màu/size để kiểm thử quan hệ ảnh Eloquent, đánh dấu `fixture_variant.synthetic=true` trong manifest; không phải hàng bán thật.

19 file minh họa cũ ngoài manifest AI và 1 ảnh upload của sản phẩm 172 không kèm gói; Unsplash và URL website ngoài cũng không tải xuống/đóng gói. Chờ PO kiểm tra nguồn, quyền sử dụng và SKU. Các file nguồn và 23 URL ảnh lịch sử đơn giữ nguyên.

## Checksum và offline

`manifest.json` chứa SHA-256 từng ảnh, placeholder và `catalog.json`. Import kiểm tra checksum trước mọi thao tác tạo database. Đối chiếu checksum ZIP với file `.sha256` khi nhận gói; checksum là kiểm tra toàn vẹn, không phải chữ ký chứng thực người phát hành.

Trang preview có CSP chỉ cho ảnh cùng origin; catalog/manifest chỉ dùng đường dẫn tương đối. Server local và MySQL phải tiếp tục chạy khi tắt Internet. Điều đó khác với dừng server hoặc ngắt loopback. Kết quả `verify` chỉ xác nhận HTTP và tài nguyên local; báo cáo riêng nếu chưa thực sự ngắt Internet trong browser.

Gói không tự commit/push/deploy, không thay chính sách placeholder production và không tự dọn dữ liệu UAT. Không dùng công cụ này để sửa lịch sử đơn hoặc xóa ảnh cũ. Để thay placeholder trong catalog thật cần PO cung cấp ảnh đúng SKU/biến thể và phê duyệt riêng việc cập nhật dữ liệu.
