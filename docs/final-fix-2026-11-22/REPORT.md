# SOFEP — Final Fix & Submission Readiness

Ngày 22/11/2026. **Source ứng viên RC1 đủ điều kiện trình PO nghiệm thu kỹ thuật trong phạm vi đã kiểm thử: lỗi High đã sửa, quản lý đánh giá cũ đã gỡ, góp ý/phản hồi/thông báo vẫn hoạt động. Bộ nộp giảng viên CHƯA đầy đủ vì còn thiếu báo cáo cuối kỳ 5 chương.** Không tự viết báo cáo thay thế, không coi file đánh dấu thiếu là báo cáo cuối kỳ.

Đã thực hiện theo phê duyệt trong yêu cầu mới của PO/PM. Giữ giao diện hiện tại, không tích hợp Figma hoặc mở rộng nghiệp vụ. Không commit/push/deploy, không chỉnh Jira/Proposal, không tác động N.Cảnh Barber.

## 1. Nguyên nhân High và bản sửa

Trước sửa, `DanhMucController::destroy()` validate ID rồi gọi delete trực tiếp. FK `san_phams.id_danh_muc` cascade xuống sản phẩm, tiếp tục xuống biến thể/gallery; FK dòng đơn SET NULL. Đường này không đi qua `SanPhamController` đang chặn xóa sản phẩm có lịch sử, nên có thể mất liên kết và không hoàn tồn được khi hủy đơn.

Sau sửa:

- Validate ID integer/tồn tại; chỉ route Admin đã xác thực được gọi.
- Transaction khóa hàng danh mục trước, dùng locking read kiểm tra **mọi sản phẩm**, kể cả tạm ẩn. Còn sản phẩm: trả **409 Conflict**, `status: 0`, “Danh mục còn sản phẩm. Hãy tạm ẩn thay vì xóa.”
- Danh mục rỗng được xóa theo quy tắc cũ, HTTP 200. Không thêm cascade, không đổi schema/migration hoặc tự di chuyển/xóa sản phẩm. Parent lock phối hợp FK để xử lý thêm sản phẩm đồng thời; transaction có retry deadlock giới hạn.
- FE giữ bố cục/modal, sửa cảnh báo không còn nói sẽ xóa toàn bộ sản phẩm, hiển thị message backend khi 409; lỗi mạng vẫn có thông báo dự phòng. Không log lỗi nghiệp vụ dự kiến thành console.error.

**Bằng chứng MySQL UAT thật:** `category-results.json` **20/20 đạt**. Danh mục có sản phẩm/lịch sử trả 409; hash toàn bộ hàng của 7 bảng catalog/lịch sử/review trước/sau các thao tác bị từ chối giữ nguyên (`uat-rejection-preservation.json`). Guest401, Customer403, ID sai422. Tạo một danh mục thử rỗng, thử insert sản phẩm trên kết nối MySQL thứ hai trong transaction: request xóa chờ **1.469 ms**, sau commit trả409, sản phẩm giữ nguyên. Chỉ sản phẩm/danh mục fixture UAT không có lịch sử được dọn sau test; catalog về đúng 168 sản phẩm/8 danh mục. Không xóa file ảnh vật lý.

Browser xác nhận Admin bấm xóa danh mục có 19 sản phẩm nhận đúng thông báo và 8 danh mục vẫn còn: `browser-category409.png`.

## 2. Danh sách source đã sửa/tạo/xóa

Đối chiếu với snapshot nguồn v2, không nhầm các thay đổi cũ trong Git HEAD là bản sửa của lượt này. `changed-files.json` có đúng **10 đường dẫn**:

| Loại | File | Nội dung |
|---|---|---|
| Sửa | `themmoik27be/app/Http/Controllers/Admin/DanhMucController.php` | Transaction/parent lock/kiểm phụ thuộc/409 |
| Sửa | `Fe/src/components/Admin/DanhMuc/index.vue` | Cảnh báo và lỗi backend dễ hiểu |
| Sửa | `Fe/src/layout/components/MenuRocker.vue` | Bỏ mục “Đánh giá lịch sử” |
| Sửa | `Fe/src/router/index.js` | Bỏ page/component cũ, giữ alias `/admin/danh-gia` chỉ redirect `/admin`; guard Admin vẫn áp dụng |
| Sửa | `themmoik27be/routes/api.php` | Bỏ import/controller registration và 3 API quản lý đánh giá |
| Xóa code | `Fe/src/components/Admin/DanhGia/index.vue` | Component chỉ phục vụ chức năng cũ |
| Xóa code | `themmoik27be/app/Http/Controllers/Admin/DanhGiaController.php` | Controller chỉ phục vụ quản lý cũ |
| Tạo | `Fe/tests/submission-safety.test.mjs` | Thông báo 409/network fallback, alias an toàn/menu cũ biến mất, route góp ý/thông báo giữ nguyên |
| Tạo | `themmoik27be/tests/Feature/CategoryDeletionSafetyTest.php` | Schema FK thật SQLite memory: empty/hidden product/history preservation/validation/authorization |
| Tạo | `themmoik27be/tests/Feature/LegacyReviewRetirementTest.php` | API cũ404 với Guest/Admin/Customer; bản ghi đánh giá lịch sử không đổi |

Các tài liệu/runner/log/checksum/screenshot mới nằm trong `docs/final-fix-2026-11-22/`. ZIP ứng viên và SHA nằm trong `outputs/sofep-final-candidate-2026-11-22/`. Những file này là bằng chứng nội bộ, không copy toàn bộ vào source nộp. Các thư mục TEMP phục vụ staging/cài sạch được ghi trong package-location/uat-location. Không xóa hoặc hoàn tác các thay đổi ngoài phạm vi có sẵn.

## 3. Đánh giá cũ đã loại bỏ và phần bảo toàn

Ba endpoint không còn đăng ký, gọi trực tiếp đều **404**:

- `GET /api/admin/danh-gia/get-data`
- `POST /api/admin/danh-gia/change-status`
- `POST /api/admin/danh-gia/destroy`

Không còn menu hoặc component quản lý. URL FE cũ chuyển `/admin`, không tải trang rỗng/lỗi. Alias redirect được giữ có chủ đích để bookmark cũ an toàn, không phải giữ trang đánh giá. Browser thấy old menu link count0 và URL sau điều hướng là `/admin`.

**Giữ nguyên** bảng/dữ liệu/migration `danh_gia_san_phams`, model `DanhGiaSanPham`, quan hệ `SanPham::danhGias`, phần đọc `ProductDetailController`, key `reviews/rating` và test chi tiết còn dùng chúng. Không drop/seed/xóa dữ liệu để lấp trang trống. Không xóa hàm FE tên cũ `dieuHuongDanhGia()` vì hành vi hiện tại là đưa tới `/gop-y`.

**Góp ý vẫn hoạt động:** HTTP Customer gửi góp ý website tổng hợp201 → Admin đọc200/phản hồi in_app200 → Customer thấy notification và đánh dấu đọc200. Customer gọi API đọc góp ý Admin bị403. **15/15 kiểm tra đạt**, `feedback-results.json`; browser thấy “SOFEP phản hồi góp ý #1”, `browser-feedback-inbox.png`. Các bảng private_feedback/feedback_attachments/customer_notifications, controller Feedback/Inbox và routes `/gop-y`, `/admin/gop-y`, `/thong-bao`, ví/campaign giữ nguyên. Email thật không được cấu hình hoặc gửi ra ngoài.

## 4. Kiểm thử từ chính ZIP RC1

Đóng ZIP mới theo allowlist source hiện tại, giải nén **chính archive** vào TEMP mới. Không copy/junction vendor/node_modules/storage từ PO hoặc v2. Composer107 package và npm60 package được cài mới từ lockfile; không upgrade dependency/lockfile. Installer trong ZIP tạo môi trường/MySQL demo mới:

- FE **http://127.0.0.1:5200** → Laravel thật **http://127.0.0.1:8047**.
- DB **sofep_demo_20261122_9ada385739a5**, Storage trong bản giải nén `Nhom5_CMU-IS432CIS_SOFEP` riêng, không dùng upload PO.
- 26 migration; ban đầu 168 sản phẩm,167 gallery,501 biến thể,167 ảnh,1 Admin/1 Customer ngẫu nhiên; **0 đơn,0 token**.
- Wrapper cục bộ ngoài archive chỉ truyền credential MySQL local trong bộ nhớ cho installer; không ghi credential vào báo cáo/gói. Env/key chỉ sinh trong thư mục cài mới theo quy trình đã được duyệt.
- Sau UAT: 168 sản phẩm,167 gallery,501 biến thể;2 Customer (có một đăng ký tổng hợp chưa OTP),1 Admin,4 đơn/4 dòng tổng hợp,1 voucher,1 góp ý/phản hồi/thông báo và token runtime. Có 1 đơn fixture voucher đã hủy. **Không đóng dữ liệu runtime phát sinh này vào ZIP.**

| Kiểm tra/lệnh | PASS | SKIP | FAIL | Bằng chứng/phạm vi |
|---|---:|---:|---:|---|
| `node --test tests/*.test.mjs` | **80** | 0 | 0 | fe-tests.txt; tăng 2 regression FE |
| `php artisan test` | **95** | **2** | 0 | be-tests.txt;97 tổng,1.393 assertions; tăng5 regression BE |
| `npm run build` | PASS | — | 0 | build.txt, chạy sau setup API port8047 |
| PHP lint controller/routes | PASS | — | 0 | Không lỗi cú pháp; PHPUnit cũng load source mới |
| HTTP/API/ảnh vòng chính | **381/381** | 0 | 0 | http-results.json; bớt1 API Admin review đã gỡ so vòng cũ382 |
| MySQL danh mục/legacy/race | **20/20** | 0 | 0 | category-results.json, không mất catalog/lịch sử |
| Góp ý/phản hồi/inbox | **15/15** | 0 | 0 | feedback-results.json, cộng browser xác nhận inbox |
| Voucher/đăng ký/hết hàng/hủy đơn | **14/14** | 0 | 0 | scenarios.json; quote giảm10.000, mã sai422, jump status409, cancel lặp an toàn, zero stock422 |
| MySQL FK | **20 quan hệ**,0 orphan | — | 0 | db-results.json |

Hai BE skip: export fixture browser tùy chọn trong BrowserUatPreparationTest và CheckoutIntegrityTest khi thiếu SOFEP_UAT_DIR. Không báo97/97 đã chạy. PHPUnit dùng SQLite memory + mail array; đã xác nhận guard trước chạy, không dùng MySQL chính. MySQL thật được chứng minh bằng installer/API, snapshot và bài thử tranh chấp riêng. FE suite có kiểm service/source/component, không đồng nghĩa80 E2E browser.

### Browser UAT thực tế

`browser-results.json` ghi12 quan sát:

- Admin login/dashboard; modal/cảnh báo xóa, thông báo từ chối dễ hiểu; danh sách vẫn8; URL đánh giá cũ redirect và không còn menu.
- Các màn hình sản phẩm168 dòng, biến thể501, đơn3 và khách2 đã tải tại thời điểm duyệt; sau đó browser tạo thêm đơn thứ4. Các API Admin khác/validation được kiểm HTTP; không tuyên bố đã thao tác mọi CRUD qua UI.
- Customer login, home đủ ảnh, tìm “Áo”67 kết quả, Đen/M, giỏ đúng lựa chọn, COD thành công, lịch sử đơn và thông báo góp ý.
- ID172 SVG placeholder local tải đúng. Không thay URL, HOLD hoặc ảnh nguồn.
- Console lượt smoke: **0 error/warning**, browser-console.json. Các lỗi công cụ reconnect tab xảy ra trước lượt UI cuối không được coi là lỗi website; đã mở tab mới và chạy lại lượt cần chứng minh.

**Quick View:** đã bấm nút “Xem chi tiết” trên home: hành vi hiện tại chuyển sang `/san-pham/ao-polo`, không mở modal Quick View. `TrangChu.vue::xemChiTiet()` đã hoạt động theo cách này từ trước, không thay thiết kế/luồng trong bản sửa. Modal cũ không có luồng mở từ nút đã test; **không kết luận modal Quick View đã PASS** hoặc tự bật lại chức năng ngoài phạm vi. Lựa chọn biến thể/giỏ được kiểm ở trang chi tiết thật.

OTP đúng/sai/hết hạn/throttle/session được kiểm tự động; đăng ký HTTP tạo tài khoản inactive và login403. **Chưa kiểm email thật → nhập OTP bằng browser.** Thanh toán mô phỏng failed/success/persisted đạt HTTP, COD đạt cả HTTP/browser; không chuyển tiền thật. Không thử mọi tổ hợp variant/mọi thao tác Admin/mọi độ phân giải.

## 5. Ảnh, database và Storage chính

167 file trong ZIP/import khớp manifest SHA-256 và HTTP200 trên8047. Home browser **181/181 thẻ ảnh tải**, gồm ảnh trùng/catalog + 9 Unsplash trang trí; **167 ID ảnh catalog riêng biệt**. Không dùng181 làm số sản phẩm. ID172 placeholder tải từ5200; gallery436/fixture biến thể510 được kiểm API.

`preservation.json` kiểm read-only MySQL chính: catalog, hash đầy đủ hàng lịch sử đơn,23 URL/41 tham chiếu ảnh lịch sử và `.env` giữ nguyên; **187 file ảnh gốc giữ hash**. Không có thao tác ghi database/storage chính; regression ghi/xóa chỉ trong SQLite memory/MySQL demo prefix đã guard. Không đổi migration/schema hoặc xóa file ảnh vật lý. Bản ghi review test được giữ nguyên trong regression; model/bảng nguồn không bị xóa.

## 6. Working tree và 97 file

Nhánh giữ `nhanh-cua-duong`, không commit/push/reset/chuyển nhánh. `git-before.txt`, `git-after.txt` lưu trạng thái chi tiết. `WORKTREE_INVENTORY.md` và worktree-inventory.json phân loại source/test cần giữ, hướng dẫn, tài liệu riêng và runtime/fixture không nộp.

[PREVIOUS_97.md](PREVIOUS_97.md) đối chiếu đủ **97 file source/test được nêu trước**: **96 giữ trong RC1**, **1 component FE DanhGia được gỡ theo PO duyệt**. Backend DanhGiaController cũng gỡ nhưng không thuộc97 file thay đổi trước đó. Ba file regression mới được đưa vào RC1. Các thay đổi chưa commit vẫn chưa có đảm bảo trên GitHub HEAD; phải dùng đúng ZIP ứng viên/checksum hoặc chờ quy trình Git được duyệt riêng.

Manifest mới gồm **1.000 file source/test**, so v2 là 5 sửa +3 thêm −2 xóa; còn lại khớp nguồn. Kiểm lại hash source working tree với source_sha256 của candidate: không phát hiện thay đổi ngoài manifest. Ba file phân phối ẩn liên hệ/thanh toán (2 ClientLayout/CheckoutModal) tiếp tục giữ quy tắc redaction cũ; không sửa dữ liệu liên hệ PO trong source gốc.

## 7. ZIP ứng viên, cấu trúc và bảo mật

Gói: [Nhom5_CMU-IS432CIS_SOFEP_candidate_rc1.zip](../../outputs/sofep-final-candidate-2026-11-22/Nhom5_CMU-IS432CIS_SOFEP_candidate_rc1.zip), **86.615.322 byte**.

SHA-256: **44380aa0dc4a742e85223d380a8478c1e52802baf9c9213a87e55be894365d36**. [File SHA](../../outputs/sofep-final-candidate-2026-11-22/Nhom5_CMU-IS432CIS_SOFEP_candidate_rc1.zip.sha256).

Root trong archive: `Nhom5_CMU-IS432CIS_SOFEP/`:

| Thành phần | Nội dung |
|---|---|
| Fe/ | Vue/Vite, assets/public,80 tests,package.json/lockfile, `.env.example` an toàn |
| themmoik27be/ | Laravel app/routes/config, migrations/factories,97 tests và support cần thiết, Composer/lockfile, `.env.example` an toàn |
| demo/ | Catalog allowlist7 bảng,168 sản phẩm/167 gallery/501 biến thể,167 media,placeholder,provenance/checksum |
| handoff/ | setup.php và INSTALL.md: prerequisite, dependency, import, port/CORS, Laravel/Vite, account, Navicat/Storage |
| SOURCE_MANIFEST.json | Hash nguồn/snapshot working tree, redaction và nhánh/HEAD để truy vết |
| BaoCao/README_CHO_BO_SUNG.md | **Chỉ đánh dấu thiếu**, không phải báo cáo5 chương |
| SUBMISSION_STATUS.md | Ghi rõ ứng viên, còn thiếu báo cáo, chưa được nộp như bộ hoàn chỉnh |

**1.179 file archive khớp bản giải nén**, zip-direct.json. Package-audit.json kiểm allowlist, checksum media, marker riêng PO và mẫu secret mạnh: không thấy vi phạm. Chỉ cho phép2 `.env.example` với APP_KEY/DB_PASSWORD trống; không có `.env` thật, private access file, token/session runtime, SQL dump chính, khách/đơn/users thực, vendor/node_modules/dist/log/cache. Không copy20 ảnh ngoài bộ/HOLD. Các fixture test dùng chuỗi giả; credits/license công khai assets được giữ. Pattern scan không phải bảo đảm tuyệt đối về mọi dạng secret, nhưng dữ liệu demo cũng được kiểm allowlist không có trường PII/credential.

Database demo dùng migrations + catalog.json + installer, không dùng SQL dump database chính. Seeder cũ chứa credential/dữ liệu không cần thiết bị loại khỏi archive; quy trình import thay seeder cũ, không chạy db:seed/composer setup. Giữ migrations và factories cần thiết. Hai account Admin/Customer sinh ngẫu nhiên **sau cài** vào `storage/app/private/demo-access.json`; mật khẩu không nằm trong ZIP, không đổi tài khoản PO. Hướng dẫn đã nêu rõ không copy template thành.env trước khi chạy installer mới.

ZIP v2 cũ không bị ghi đè; hash còn **b42640750e5c0c7902a22ffa23858b34f98db88e5055e77f389740e325a2b16e**. RC1 là file ứng viên riêng, không tự phát hành/đẩy GitHub.

## 8. Vấn đề còn lại và kết luận

| Mức/trạng thái | Còn lại |
|---|---|
| High F01 | **Đã xử lý**, regression SQLite/MySQL/race/browser đạt trong phạm vi nêu trên |
| Medium F02 | **Đã xử lý theo quyết định loại bỏ chức năng cũ**, không thêm nghiệp vụ đánh giá mới |
| Low | Caption/zoom của placeholder ID172 vẫn coi là1 ảnh sản phẩm; ảnh placeholder đúng. Giữ nguyên để không mở rộng phạm vi |
| Điều kiện hồ sơ bắt buộc | **Báo cáo cuối kỳ5 chương còn thiếu**. Nhóm phải bổ sung bản được duyệt; Codex không tạo báo cáo giả |
| NOT TESTED | Máy vật lý/OS khác, offline ngắt Internet thực tế, email OTP thực, SMTP/OAuth/ngân hàng thật; toàn website vẫn có CDN/font/Unsplash/logo ngoài |

**Có thể trình PO nghiệm thu bản source kỹ thuật RC1 và các bản sửa được duyệt. Chưa thể xác nhận bộ nộp giảng viên hoàn chỉnh.** Sau khi nhóm bổ sung báo cáo cuối kỳ, kiểm lại toàn bộ thành phần, bảo mật/checksum và duyệt phiên bản cuối trước nộp. Không commit/push/deploy khi chưa có yêu cầu riêng.
