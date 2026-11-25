# SOFEP — Final Audit trước nghiệm thu PO

Ngày 21/11/2026. **Chưa đủ điều kiện nghiệm thu toàn bộ checklist không kèm điều kiện.** Bộ ảnh và ZIP v2 đạt các kiểm tra bàn giao đã thực hiện, nhưng còn **1 High, 1 Medium, 1 Low** cần PO xử lý/chốt phạm vi; một số mục vẫn NOT TESTED. Không tự sửa source ứng dụng, nghiệp vụ hoặc bản ZIP.

Chi tiết nguyên nhân, vị trí code, tác động và cách kiểm thử: [FINDINGS.md](FINDINGS.md).

## Môi trường và tính độc lập

- Nhánh `nhanh-cua-duong`; working tree đã có nhiều thay đổi từ các task trước. Không chuyển nhánh/hoàn tác/commit/push/deploy.
- UAT đang chạy từ bản giải nén **SOFEP_SUBMISSION_v2.zip**, FE `http://127.0.0.1:5198` → Laravel thật `http://127.0.0.1:8045`; database `sofep_demo_20261121_1862a96c23ee`, Storage nằm trong bản giải nén TEMP riêng. Đường dẫn tại `uat-location.json`. Không dùng API 8000 cho kiểm thử.
- PHP 8.3.24, Node 24.21.0, MySQL 8.4.3 trên Windows. Backend tests SQLite memory; HTTP và browser dùng MySQL UAT thật. Đã đọc cấu hình PHPUnit và guard `IsolatedApiTestCase` trước khi chạy.
- Dùng lại môi trường cài sạch v2 từ task bàn giao. **Không tuyên bố lượt này clone GitHub/cài mới toàn bộ từ đầu lần nữa.** Bằng chứng cài dependency/import mới ở [báo cáo bàn giao](../submission-handoff-2026-11-21/REPORT.md); lượt audit này đọc trực tiếp archive, so 1.174 file với bản giải nén, chạy lại tests/build và smoke test.
- Chỉ phát sinh tài khoản/voucher/đơn tổng hợp qua API UAT để thử; không gửi email/ngân hàng thật. Không thay database/storage chính hoặc lịch sử đơn chính.

## Kết quả định lượng

| Hạng mục | Kết quả |
|---|---|
| FE `node --test tests/*.test.mjs` | **PASS 78/78**, 0 skip — `fe-tests.txt` |
| BE `php artisan test` | **PASS 90, SKIP 2**, 1.381 assertions — `be-tests.txt` |
| FE `npm run build` | **PASS**, `build.txt` |
| HTTP/API/ảnh vòng chính | **382/382 kiểm tra đạt**, `http-results.json`; gồm status, checksum và trạng thái nghiệp vụ, không phải 382 luồng UI |
| HTTP kịch bản bổ sung | **14/14 đạt** sau sửa runner đúng tên field voucher, `scenarios.json` |
| Ảnh minh họa | **167 sản phẩm / 167 file** HTTP 200 + checksum đúng; browser trang chủ có 167 ID ảnh riêng biệt |
| Placeholder ID 172 | SVG local tải đúng; có lỗi caption/zoom mức Low F03 |
| Trang chủ browser | 181 thẻ ảnh tải được, gồm 172 local và 9 Unsplash; không dùng số DOM làm số sản phẩm |
| Console browser đã duyệt | 0 error/warn được ghi nhận, `browser-console.json` |
| FK MySQL UAT | 20 quan hệ, 0 orphan; 0 liên kết ảnh biến thể chéo sản phẩm; 0 dòng gallery trùng product+URL |
| Main PO | Catalog/lịch sử đơn/env giữ hash, 187 file ảnh giữ hash; 23 URL/41 tham chiếu lịch sử giữ nguyên — `preservation.json` |
| Source PO | 999 file source khớp hash nguồn khi đóng v2 — `source-preservation.json` |

Hai BE skip là export fixture browser tùy chọn khi không có `SOFEP_UAT_DIR` trong `BrowserUatPreparationTest` và `CheckoutIntegrityTest`. Không báo 92 test đều đã chạy. FE suite gồm cả kiểm component/service và assertion source; không tương đương 78 luồng E2E browser.

Runner kịch bản bổ sung lần đầu dùng nhầm `ma_giam_gia` thay vì hợp đồng `ma_giam_gia_su_dung`: hai assertion voucher sai kỳ vọng. Giữ bằng chứng `scenarios-harness-v1.json`; sửa **runner audit**, không sửa app/test sản phẩm hoặc dữ liệu để che lỗi, chạy lại đạt giảm 10.000 và mã sai bị 422. Hai voucher/tài khoản/đơn tổng hợp từ các lượt runner được giữ trong UAT.

## Ma trận chức năng

PASS chỉ áp dụng phần được mô tả; cột browser tách riêng. “NOT TESTED” có thể đi cùng PASS API/automated của cùng nhóm, không được hiểu là toàn nhóm đã nghiệm thu đầy đủ.

| Nhóm | Tự động/API thực tế | Browser thực tế | Kết luận/phạm vi |
|---|---|---|---|
| Customer đăng ký | PASS API 201, tài khoản chưa kích hoạt, login 403; validation 422 | NOT TESTED form đăng ký tới cuối | PASS phần API; E2E còn thiếu |
| OTP | PASS tests mã đúng/sai/hết hạn và throttle; HTTP sai định dạng 422 | NOT TESTED email → nhập mã đúng | NOT TESTED luồng thực tế; mailer array |
| Customer/Admin đăng nhập | PASS HTTP đúng/sai, session, role; tests khóa tài khoản/throttle/logout | PASS hai vai trò, đăng xuất và đăng nhập lại Admin | PASS lượt smoke; không dùng credential PO |
| Tìm kiếm/lọc | PASS tests catalog/query | PASS tìm “Áo” 67 kết quả, lọc ≤300k còn 16 | PASS trường hợp đã chạy |
| Chi tiết/biến thể | PASS API gallery 436, fixture 510; tests giá/stock/variant | PASS sản phẩm 1 chọn Đen/M, gallery chuyển ảnh | PASS mẫu; không test mọi tổ hợp 501 dòng |
| Giỏ hàng | PASS tests giới hạn lượng, lựa chọn, phiên | PASS thêm Đen/M, đúng 249.000, mở drawer/checkout | PASS lượt smoke |
| Voucher | PASS tạo voucher tổng hợp, quote giảm 10.000, mã sai 422; tests ngày/quota/audience | PASS mở ví trạng thái rỗng; NOT TESTED chọn/apply mã trong checkout browser | PASS API, UI áp mã chưa test |
| COD | PASS đặt đơn thật UAT và đọc detail; tests tồn/voucher/idempotency | NOT TESTED lại COD trong lượt này; có bằng chứng task bàn giao trước | PASS API, không gộp bằng chứng cũ thành browser mới |
| Thanh toán mô phỏng | PASS failed/success/persisted; Admin không checkout được | PASS tạo đơn, thất bại có thông báo, thành công hiển thị “Đã thanh toán mô phỏng” | PASS; không có tiền thật |
| Checkout validation | PASS thiếu payload 422, vượt tồn 422 | PASS thiếu SĐT có thông báo, điền lại đặt thành công | PASS mẫu |
| Hết hàng | PASS HTTP active variant tồn 0 bị 422; tests stock/hidden/wrong variant | NOT TESTED thêm biến thể hết hàng qua browser | PASS API; UI trường hợp này chưa test |
| Lịch sử đơn | PASS HTTP list/detail own order; tests ownership/pagination | PASS danh sách có đơn vừa tạo/trạng thái đã thanh toán | PASS list; chưa mở mọi detail browser |
| Đánh giá sản phẩm | FAIL phạm vi: không có Customer create route | FAIL không có form chấm sao/gửi; chỉ góp ý riêng | **F02 Medium** |
| Thông báo/ví | PASS HTTP list/count/preferences/wallet; tests ownership/campaign/grant/retry | PASS empty state; NOT TESTED nhận/đọc thông báo mới tới cuối | Chưa nghiệm thu trọn vòng gửi → nhận |
| Admin dashboard | PASS HTTP thống kê; tests doanh thu chỉ đơn giao/đã trả | PASS trang thống kê tải được | PASS smoke, chưa đối soát mọi chart |
| Admin sản phẩm/ảnh | PASS list, create rỗng 422, upload rỗng 422; tests CRUD/ảnh | PASS 168 dòng, tìm SKU còn 1, mở gallery 2 ảnh | CRUD thành công/upload mới trong browser NOT TESTED lượt này |
| Admin biến thể/tồn | PASS list/invalid create; tests giá/stock/combo/ảnh | PASS danh sách 501 dòng và form tồn kho | Lưu thay đổi tồn bằng browser NOT TESTED |
| Admin danh mục | PASS list/invalid create | PASS 8 danh mục/form; không bấm xác nhận xóa | **FAIL an toàn xóa F01 High** từ code+schema |
| Admin đơn hàng | PASS list, chặn nhảy trạng thái 409, hủy COD tổng hợp và hủy lặp 200 | PASS 7 đơn tải được; cập nhật qua browser NOT TESTED | PASS API vòng đã chạy |
| Admin khách hàng | PASS list; tests lọc Customer/chặn Seller và trạng thái | PASS 3 khách tổng hợp | Khóa/mở khóa bằng browser NOT TESTED |
| Admin voucher | PASS tạo fixture 201, invalid 422; tests sửa/quota/history | PASS form/list 2 mã tổng hợp | CRUD đủ qua browser NOT TESTED |
| Phân quyền | PASS Guest Admin401, Customer Admin403, Admin checkout403; tests route inventory/Seller/ownership | Hai phiên Customer/Admin cùng tồn tại theo thiết kế; không dùng điều đó làm test Customer vượt quyền | PASS backend các ca đã chạy; Seller browser NOT TESTED |

API/bộ test không thay thế thao tác trực tiếp. Browser đã từng còn phiên Admin từ task trước; đã đăng xuất rồi đăng nhập lại để kiểm lại. Lượt điều hướng `/admin` khi vẫn có phiên Admin **không** được tính là bằng chứng Customer bypass hay bằng chứng kiểm từ chối. Bằng chứng từ chối vai trò ở HTTP và suite riêng.

## Giao diện desktop và ảnh

Quan sát tại viewport browser hiện có, chiều ngang 1.280 CSS px: trang chủ, tìm kiếm, chi tiết sản phẩm, giỏ, checkout, dashboard, sản phẩm/gallery, biến thể, danh mục, đơn, khách và voucher. Không thấy tràn ngang toàn trang ở các DOM snapshot đã lưu; bảng Admin dùng vùng cuộn ngang riêng. Không thấy chữ bị che/nút mua hoặc checkout bị vô hiệu không có lý do trong lượt smoke. Không coi kiểm DOM overflow là kiểm toàn bộ khả năng tiếp cận, mọi độ phân giải hoặc mọi nút.

Loading quan sát được ở login, chi tiết, checkout xác minh phiên, lịch sử đơn và ví; sau đó tải xong. Các snapshot Admin đầu tiên có 1 dòng empty/loading, đã kiểm lại bản `*-loaded` đủ 501/8/7/3 dòng; không dùng ảnh chụp trung gian để kết luận rỗng dữ liệu. Lỗi selector của công cụ khi click nút giá được khắc phục bằng AX click, không tính là lỗi website.

F03 là vấn đề giao diện cụ thể còn lại của placeholder; xem ảnh chụp `placeholder-172.png`. Không thiết kế lại, không xóa nhãn/ảnh/dữ liệu trong lượt audit.

[Bảng đối chiếu ảnh](IMAGE_CROSSCHECK.md) gồm 5 sản phẩm mẫu ID 1/22/43/85/127 và ID 172, có URL MySQL main/gallery, HTTP status/hash, file Storage và ảnh browser trang chủ. ID 1 có thêm gallery thử 515 từ task trước; HTTP/checksum của cả gallery đó cũng đạt. `image-crosscheck.json` có đường dẫn đầy đủ; `image-map.json` có 167 ảnh gốc. Không đưa ảnh ngoài bộ 167 vào ZIP.

UAT sau kiểm thử: 168 sản phẩm, 168 gallery (167 gốc + upload thử có sẵn 515), 501 biến thể, 3 khách tổng hợp, 1 Admin, 7 đơn/7 dòng đơn tổng hợp; 2 voucher tổng hợp. Có 2 đơn COD được hủy trong runner; file ảnh gốc giữ nguyên. ZIP vẫn chỉ có 167 gallery/167 file và **không chứa dữ liệu runtime này**. Không áp dụng dọn 227 dòng lên database chính.

## Bàn giao và dữ liệu riêng tư

**PASS các kiểm tra archive/phân phối:** [ZIP v2](../../outputs/sofep-submission-2026-11-21/SOFEP_SUBMISSION_v2.zip), 86.552.490 byte; SHA-256 `b42640750e5c0c7902a22ffa23858b34f98db88e5055e77f389740e325a2b16e`. `zip-direct.json` kiểm trực tiếp 1.174 entry/hash, không chỉ tin staging. `package-audit.json` kiểm nội dung allowlist và marker/credential pattern.

- 999 file source/test + installer/hướng dẫn/catalog/manifest/167 media/placeholder. Không có `.git`, `.env*`, vendor/node_modules/dist, log, SQL/sqlite dump, private account file hoặc token runtime trong ZIP.
- Catalog chỉ 7 bảng: danh mục/thương hiệu/màu/size/sản phẩm/gallery/biến thể; không có khách/đơn/users/token, không có cột email/password/địa chỉ/điện thoại khách. Không hardcode localhost/đường dẫn PO trong catalog.
- Installer tạo database prefix demo, env/key và tài khoản ngẫu nhiên tại máy người cài; hướng dẫn lấy credential ở private file tại máy đó, không đặt mật khẩu vào báo cáo. Source test có fixture tổng hợp và assets có thông tin tác giả/license công khai; kiểm pattern không phải chứng nhận pháp lý tuyệt đối.
- 3 file đã ẩn liên hệ/thanh toán riêng trong ZIP so với source PO được ghi rõ trong SOURCE_MANIFEST; audit xác nhận checksum bản gốc và bản phân phối, không tự sửa chúng.
- Hướng dẫn [INSTALL.md](../submission-handoff-2026-11-21/INSTALL.md) có PHP/Composer/Node/MySQL, cài lockfile, setup/import, chạy artisan/Vite, tài khoản demo, Navicat ↔ Storage, đổi port/restart Vite và lưu ý không chạy seed/composer setup.

**NOT TESTED:** máy vật lý/OS khác, clone GitHub mới trong lượt audit này, cài lại prerequisite, ngắt Internet thực tế, SMTP/OAuth/ngân hàng thật. Browser API hiện có không cung cấp thao tác offline có kiểm soát; không ngắt mạng toàn máy PO. Home có 9 ảnh Unsplash và source còn CDN Bootstrap/Google Fonts/Wikimedia: chưa chứng nhận toàn website offline dù 167 ảnh catalog local tải đúng.

## Điều kiện để đề nghị PO nghiệm thu

1. Duyệt và xử lý F01, test hồi quy bảo toàn catalog/lịch sử/hoàn tồn; đây là lỗi ưu tiên cao trước khi cho thao tác quản trị đầy đủ.
2. PO chốt đánh giá sản phẩm thuộc phạm vi hay chủ ý loại bỏ (F02). Chốt OTP dùng tài khoản demo sẵn hay cần E2E email thật; không coi suite OTP là gửi mail thực tế đã đạt.
3. Hoàn tất các ca browser còn thiếu mà PO yêu cầu nghiệm thu đầy đủ, ưu tiên voucher, CRUD Admin, nhận/đọc thông báo và trạng thái hết hàng. Xử lý F03 sau luồng chính.
4. Nếu source đổi sau audit, đóng phiên bản ZIP mới và lặp lại kiểm checksum/cài sạch/test tương ứng; giữ v2 làm bằng chứng, không ghi đè im lặng.

**Có thể trình diễn có giới hạn** các luồng đã PASS trong UAT cùng máy. **Chưa đề nghị PO ký nghiệm thu toàn bộ website** khi F01 chưa xử lý và các mục thiếu chưa được PO chốt. Không thao tác xóa thực tế để tái hiện F01 trong lượt này.

## File và bằng chứng tạo trong lượt audit

Chỉ thêm thư mục `docs/final-audit-2026-11-21/`: báo cáo này, FINDINGS, IMAGE_CROSSCHECK, runner HTTP/scenario/kiểm schema/ảnh, bản sao verifier read-only và location, JSON/log/screenshot. Không sửa file ứng dụng. Build/test sinh artifact riêng trong bản giải nén UAT; test API tạo dữ liệu tổng hợp UAT đã nêu. Script package kiểm lại ghi cùng nội dung file checksum cạnh ZIP; hash ZIP không đổi.

Các bằng chứng chính: `fe-tests.txt`, `be-tests.txt`, `build.txt`, `http-results.json`, `scenarios.json`, `browser-results.json`, `browser-console.json`, `home.json`, `schema-proof.json`, `db-results.json`, `image-crosscheck.json`, `zip-direct.json`, `package-audit.json`, `source-preservation.json`, `preservation.json`; screenshots home/cart/checkout-success/Admin/placeholder. Không ghi credential hoặc token vào các báo cáo này.

