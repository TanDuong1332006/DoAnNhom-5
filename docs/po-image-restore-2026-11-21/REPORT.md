# Khôi phục ảnh website PO 5173/8000 — 21/11/2026

## Kết quả

**PASS trên chính `http://localhost:5173` → backend 8000, database `doan_nhom_5`.** Browser tải thành công **167 ID sản phẩm có ảnh minh họa**, ID **172** tiếp tục placeholder. Không dùng UAT để thay thế bằng chứng website PO.

Không cần sửa database hoặc Storage chính. Catalog 168 sản phẩm, 514 dòng gallery, 500 biến thể giữ nguyên. **187 file ảnh giữ nguyên checksum; toàn bộ hash dòng đơn/đơn hàng và 23 URL/41 tham chiếu ảnh lịch sử đơn không đổi. Hai file `.env` FE/BE giữ nguyên checksum.** Xem [preservation.json](preservation.json).

## Nguyên nhân đã xác nhận

1. `Fe/src/services/productImages.js` trước sửa chỉ cho phép minh họa khi `VITE_DEMO_ILLUSTRATIONS === 'true'`.
2. UAT đã đặt cờ này trong cấu hình Vite riêng. `Fe/.env` của PO chỉ khai báo `VITE_API_URL=http://127.0.0.1:8000/api`, không có cờ minh họa. Do đó các filename `illustration-…` bị resolver trả placeholder mặc định.
3. Đã tái hiện trên website PO sau khi backend sẵn sàng: API trả 168 sản phẩm nhưng **173 ảnh DOM quan sát được đều là placeholder**, gồm các thẻ lặp và ảnh giỏ hàng có sẵn. 173 không phải số sản phẩm. Xem `browser-before.json` và module Vite trước sửa `vite-module-before.txt`.
4. Khi bắt đầu, chưa có listener 5173/8000. Đã chạy Vite hiện tại và `php artisan serve --host=127.0.0.1 --port=8000 --no-reload` từ repository, không thay `.env` hoặc dữ liệu. Lượt mở trang trước khi backend sẵn sàng có lỗi tải catalog; reload sau khi backend sẵn sàng xác nhận riêng lỗi bộ lọc placeholder vẫn còn.

Laravel thực tế dùng MySQL `doan_nhom_5`, Storage public đúng thư mục repo; không có config cache. CORS đang cho phép rõ `http://localhost:5173`, API catalog/ảnh trả header đúng origin. Cả `localhost:8000` và `127.0.0.1:8000` truy cập catalog 200. Xem `environment-probe.json`.

**Không có bằng chứng cần chỉnh URL/file hoặc dữ liệu chính.** Browser cache không phải nguyên nhân của lượt tái hiện: module mới vẫn chặn khi thiếu cờ; sau sửa, reload tải ảnh bằng URL gốc. Không chạy clear cache Laravel, không xóa browser/localStorage của PO.

## Bản sửa nhỏ

- `Fe/src/services/productImages.js`: mặc định cho phép ảnh minh họa cho catalog giả lập PO đã duyệt; cấu hình `VITE_DEMO_ILLUSTRATIONS=false` vẫn có thể tắt. Main/gallery/ảnh theo màu dùng cùng resolver. Không hardcode API origin mới hoặc đổi URL nguồn.
- **File mới** `Fe/src/services/legacyProductImageHolds.js`: giữ chặn đúng **19 file stock cũ** từ hồ sơ handoff, với hai alias API/Storage cho mỗi file. Các file này cũng mang prefix `illustration-`, nhưng không thuộc bộ 167 ảnh đã được PO xác nhận; đổi default không được vô tình mở thêm chúng. Không đọc manifest audit lúc runtime và không đổi bytes ảnh.
- Giữ nguyên hai nguồn HOLD ID 172 và bộ lọc Unsplash. URL thiếu/không an toàn vẫn placeholder; component đang có fallback khi tải lỗi. Ảnh upload mới không bị chặn theo product ID hoặc toàn bộ thư mục. Nhãn **“Ảnh minh họa”** vẫn hiển thị, không gọi đây là ảnh thật.
- `Fe/tests/product-images.test.mjs`, `Fe/tests/api-config.test.mjs`: cập nhật kỳ vọng default mới; kiểm tra tắt rõ ràng, HOLD stock/ID 172, URL theo backend, ảnh mới và luồng Admin multipart hiện có.

Không sửa component giao diện, source backend, hợp đồng API, phân quyền, schema, seeder hoặc dependency/lockfile. Giữ nguyên các thay đổi có sẵn của working tree. Không sửa `.env` PO; lần chạy sau website mặc định cũng nhận chính sách mới từ source.

## Website ↔ MySQL ↔ Laravel Storage

[NAVICAT_COMPARISON.md](NAVICAT_COMPARISON.md) chứa đủ **5 sản phẩm đại diện** ID **1, 3, 4, 7, 8**, thuộc áo nam, quần nam, váy/đầm, giày và túi. Với mỗi sản phẩm có tên, URL `san_phams.hinh_anh`, ID/URL gallery tương ứng, file Storage tuyệt đối, API HTTP 200/checksum và URL website đã quan sát. Có PNG riêng từng sản phẩm.

Dữ liệu được đọc trực tiếp qua PDO trong transaction **READ ONLY** trên cùng MySQL; không mở/sửa UI Navicat. Đây là các trường Navicat sẽ đọc từ `doan_nhom_5`.

`check-http.mjs` xác minh **167/167 ảnh chính qua Laravel 8000**: API trả đúng URL MySQL, HTTP trả ảnh, SHA-256 bằng file vật lý. Kiểm tra gallery API cho 5 mẫu + ID 172, ảnh được phép hiển thị đều truy cập được. `comparison.json` giữ đối chiếu đủ 168 sản phẩm; `samples.json` giữ gallery từng mẫu mà không lưu danh tính người đánh giá hoặc dữ liệu khách hàng.

Browser sau sửa: **167 ID ảnh minh họa riêng biệt**, toàn bộ **173 phần tử ảnh đã quan sát tải xong**. 170 phần tử là nguồn minh họa vì có thẻ lặp giữa khu ưu đãi và catalog; 3 phần tử placeholder gồm hai thẻ ID 172 và ảnh cũ trong giỏ hàng có sẵn. Không thay dữ liệu giỏ của PO để làm số đếm đẹp. Xem `browser-home-after.json` và `final-qa.json`.

Đã mở chi tiết cả 5 sản phẩm và xác nhận ảnh chính/thumbnail tải được, có nhãn minh họa; `browser-samples.json` lưu URL và trạng thái ảnh thực tế. ID 172 không được gán ảnh khác. Các liên kết biến thể hiện tại giữ nguyên; 9 liên kết nguồn HOLD của ID 172 tiếp tục fallback, không tự đổi ảnh màu.

## Kiểm thử và giới hạn

| Hạng mục | Kết quả |
|---|---|
| Website PO 5173 | **PASS**: 167 ảnh minh họa theo ID, ID 172 placeholder |
| API/Storage chính 8000 | **PASS**: 167 HTTP ảnh đúng file/hash; API main đúng MySQL |
| MySQL/Navicat đối chiếu 5 mẫu | **PASS**: ID 1, 3, 4, 7, 8; gallery và file tương ứng giữ nguyên |
| CORS/cache | **PASS** origin PO; module Vite mới nhận default; không có config cache, không cần xóa cache |
| FE tests | **78/78**, không skip; gồm ảnh, upload multipart/sắp xếp Admin, auth/cart/checkout hiện có |
| BE tests | **90 passed, 2 skipped**, tổng 92, 1381 assertions; SQLite memory |
| FE build | **PASS**, Vite build đạt |
| Login/giỏ/checkout trên PO | Form Customer/Admin tải được; mở giỏ có sẵn; checkout guest yêu cầu đăng nhập; không gửi login/order trên database chính |
| Auth/Admin API guest | **PASS**: GET `/api/kiem-tra-dang-nhap` và `/api/admin/san-pham/get-data` trả 401 |
| Ảnh thiếu/placeholder | URL ảnh UUID không tồn tại trả 404; placeholder frontend tải 200; component fallback/reset được FE tests kiểm tra |
| Bảo toàn nguồn | **PASS** catalog/gallery/variant links, hash lịch sử đơn, 187 file và `.env` không đổi |

`http-result.json` ghi **177 request GET** thành công theo kỳ vọng, không gửi request ghi dữ liệu. Các bước phát sinh token, tạo đơn, thanh toán hoặc upload không thực hiện trên PO: chúng có thể ghi database chính. Hồi quy các luồng này dựa trên FE/BE tests cô lập; **không tuyên bố đã đặt đơn/đăng nhập/upload thật end-to-end trên PO**. Không thay phân quyền Customer/Seller/Admin; suite backend giữ kiểm tra boundary hiện có.

BE SQLite không chứng minh toàn bộ luồng nghiệp vụ MySQL; bằng chứng MySQL trực tiếp trong tác vụ này là đọc catalog/ảnh/API và kiểm tra bảo toàn. Không kiểm thử ngắt Internet thực tế; ảnh sản phẩm chính là file local và không cần tải bộ ảnh mới.

## Bàn giao

Mở/reload **`http://localhost:5173`** để xem ảnh minh họa. Dev server đã khởi động bằng cấu hình local hiện có, không đổi cấu hình triển khai. Không cần thao tác UPDATE/DELETE Navicat, import ảnh hoặc thay Storage để đạt mục tiêu này.

File công cụ/bằng chứng mới nằm trong `docs/po-image-restore-2026-11-21/`: `snapshot.php`, `probe.php`, `check-http.mjs`, `make-comparison.mjs`, báo cáo/bảng đối chiếu, JSON, log tests/build, module Vite trước/sau và 5 PNG sản phẩm. Source ứng dụng thay đổi đúng 4 file nêu trên (3 sửa + 1 mới); không sửa backend.

Lệnh đã chạy: Git branch/status; PDO snapshot read-only trước/sau; probe Laravel read-only; HTTP GET kiểm tra catalog/ảnh/guest guards; `node --test Fe/tests/*.test.mjs`; `php artisan test`; `npm run build`; `php -l` PHP tools, `node --check` JS mới và `git diff --check` các file sửa. Các kiểm tra cuối đạt; Git chỉ cảnh báo line-ending LF/CRLF, không có lỗi whitespace.

Nhánh `nhanh-cua-duong`. Không xóa ảnh/bản ghi, không sửa lịch sử đơn, không commit/push/deploy, không chỉnh Jira/Proposal, không tác động N.Cảnh Barber. **Không phát sinh yêu cầu sửa database/Storage chính cần PO duyệt.**
