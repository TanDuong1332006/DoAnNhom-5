# SOFEP — kiểm định bộ bàn giao đồ án, 21/11/2026

## Kết luận nghiệm thu

**PASS bộ ZIP v2 cho cài đặt sạch và các luồng chính đã kiểm thử trên môi trường độc lập cùng máy. Có thể trình PO nghiệm thu ZIP v2.** Không coi đây là chứng nhận chạy trên máy vật lý thứ hai, mọi hệ điều hành hoặc khi ngắt Internet thực tế.

Gói đề nghị dùng: [SOFEP_SUBMISSION_v2.zip](../../outputs/sofep-submission-2026-11-21/SOFEP_SUBMISSION_v2.zip), **86.552.490 byte**, SHA-256:

`b42640750e5c0c7902a22ffa23858b34f98db88e5055e77f389740e325a2b16e`

[File checksum](../../outputs/sofep-submission-2026-11-21/SOFEP_SUBMISSION_v2.zip.sha256) · [Hướng dẫn cài đặt/nộp thầy](INSTALL.md) · [Kiểm kê working tree](WORKTREE_INVENTORY.md).

**Không dùng ZIP v1** `SOFEP_SUBMISSION.zip`: v1 được giữ để truy vết, từng có lỗi cấu hình CORS của installer làm một BE test thất bại. V2 đã sửa installer, đóng ZIP mới, giải nén mới, cài lại dependency/import lại và chạy lại kiểm thử. Không sửa source ứng dụng hoặc `.env` trên môi trường PO để làm kết quả đạt.

## Thành phần bàn giao

| Thành phần | Nội dung |
|---|---|
| `Fe/` | Source Vue/Vite hiện tại, assets/public, package.json + lockfile, 78 kiểm tra FE; giữ bản vá API/HOLD và giao diện đã bỏ nhãn |
| `themmoik27be/` | Source Laravel hiện tại, migrations/factories, public/resources, composer.json + lockfile, PHPUnit và các test cần thiết |
| `demo/catalog.json` | Chỉ 7 bảng catalog: danh mục, thương hiệu, màu, size, sản phẩm, gallery, biến thể |
| `demo/media/` | 167 JPEG local, khớp SHA-256 bộ ảnh đã kiểm thử; không có 20 file HOLD hoặc ảnh ngoài bộ |
| `demo/manifest.json` | Mapping ảnh–sản phẩm, checksum, provenance và thông tin nguồn nội bộ |
| `demo/product-placeholder.svg` | Placeholder cho ID 172 và fallback |
| `handoff/setup.php` | Installer guard môi trường mới, tạo MySQL riêng, migration/import, copy ảnh, tạo `.env`/APP_KEY và hai tài khoản demo ngẫu nhiên tại máy người cài |
| `handoff/INSTALL.md`, `README.md` | Cài PHP/Composer/Node/MySQL, dependency, import, chạy Laravel/Vite, kiểm thử và đối chiếu Navicat |
| `SOURCE_MANIFEST.json` | Checksum source, commit/branch gốc để truy vết, ghi rõ snapshot working tree và 3 file đã ẩn thông tin trong bản đóng gói |

Có **999 file source/test**, **1.174 file trong gói** sau khi tính catalog, media và hướng dẫn. Không kèm `.git`, `.env*`, vendor, node_modules, dist, log, SQL dump chính, token/session runtime, ảnh upload ngoài bộ demo, Proposal hoặc các báo cáo chứa đường dẫn riêng của PO.

### Working tree và phần không nộp

Nhánh `nhanh-cua-duong`; kiểm kê **157 mục trạng thái Git**, 92 mục thuộc source/test được giữ và 65 mục ngoài allowlist. Các mục thư mục untracked có thể đại diện nhiều file. Xem từng mục trong `WORKTREE_INVENTORY.md`/`worktree-inventory.json`.

- Giữ source đang hoạt động, kể cả file chưa tracked: cấu hình API, các service/controller/middleware/feature engagement, migrations, component và tests liên quan. Không lấy riêng HEAD vì HEAD chưa chứa hết các bản vá.
- Giữ test tự động Feature/Unit và `IsolatedApiTestCase`; loại script launcher/browser UAT cũ trong `tests/Support` không cần để chạy suite.
- Không nộp `.vscode`, cache, local env, node_modules/vendor hiện có, screenshots/log/JSON kiểm thử cũ, dữ liệu audit, Proposal hoặc output trung gian. Không xóa các file đó trên máy PO.
- Seeder cũ không được đóng vì chứa tài khoản mẫu/mật khẩu cố định và dữ liệu liên hệ không cần cho bộ này. Dùng installer + catalog allowlist; không chạy `db:seed` hoặc `composer setup`.

Ba file chỉ được thay nội dung **trong bản đóng gói**: `Fe/src/layout/wrapper/ClientLayout.vue`, `Fe/src/layout/ClientLayout.vue`, `Fe/src/components/Client/CheckoutModal.vue`. Thông tin liên hệ và số tài khoản mặc định được thay bằng địa chỉ `.example.test`, dữ liệu liên hệ/thanh toán demo. Source PO giữ nguyên; không đổi endpoint, xác thực hoặc nghiệp vụ. Checksum trước/sau nằm trong `SOURCE_MANIFEST.json`.

## Cài đặt sạch đã thực hiện

1. Tạo ZIP từ source hiện tại theo allowlist, sau đó **giải nén chính ZIP v2** vào thư mục TEMP mới; không dùng worktree hay Storage chính để phục vụ UAT.
2. Chạy `composer install --prefer-dist --no-interaction --no-scripts`: cài mới **107 package** vào vendor của bản giải nén; không copy/junction vendor PO. `composer check-platform-reqs` đạt.
3. Chạy `npm ci --ignore-scripts --no-audit --fund=false`: cài mới **60 package** vào node_modules của bản giải nén, không copy/junction node_modules PO. Lockfile giữ nguyên.
4. Chạy đúng `handoff/setup.php 8045 5198` từ bản giải nén. Installer không đọc `.env` PO, từ chối overwrite môi trường có sẵn và tự tạo database `sofep_demo_20261121_1862a96c23ee`. Launcher kiểm thử bên ngoài ZIP chỉ truyền credential MySQL local trong bộ nhớ; thành viên sẽ dùng credential MySQL của máy mình.
5. **26 migration** đạt; import 8 danh mục, 6 thương hiệu, 7 màu, 6 size, **168 sản phẩm, 167 gallery, 501 biến thể**. 501 gồm 500 catalog + một fixture tổng hợp ID 510 → ảnh 436 của sản phẩm 1. Giữ placeholder ID 172, không tự nối 9 ảnh biến thể nguồn HOLD của ID 172.
6. Sau setup: khách demo 1, Admin demo 1 được tạo ngẫu nhiên tại máy cài; **0 đơn, 0 dòng đơn, 0 token**, không copy tài khoản/khách/đơn thật. Mật khẩu chỉ nằm trong file private của bản cài mới, không nằm trong ZIP hoặc báo cáo.
7. Chạy **Laravel chuẩn bằng `php artisan serve` tại 8045**, không harness giả API; Vite tại **5198**. Probe xác nhận MySQL đúng database mới và public Storage bên trong bản giải nén. Không symlink ảnh sang thư mục PO.

Môi trường đã dùng: PHP **8.3.24**, Composer **2.10.3**, Node **24.21.0**, npm **11.19.0**, MySQL **8.4.3**, Windows. Prerequisite PHP/Node/MySQL có sẵn trên máy; dependency ứng dụng được cài mới từ lockfile. Không cài lại hệ điều hành/toolchain hoặc thử máy vật lý khác.

## PASS/FAIL

| Hạng mục | Kết quả/bằng chứng |
|---|---|
| ZIP v2 giải nén khớp nguồn staged | **PASS**, 1.174 file khớp checksum, `package-audit.json` |
| Composer/npm từ bản giải nén mới | **PASS**, `composer-install-v2.txt`, `npm-ci-v2.txt`; không copy dependency PO |
| Platform PHP | **PASS**, `platform-reqs-v2.txt` |
| MySQL/import/Storage mới | **PASS**, `install-result.json`, `probe-result.json` |
| FE → BE khác port | **PASS**, browser 5198 dùng API/ảnh 8045; không cần 8000 của PO |
| CORS allow/deny | **PASS**, chỉ origin local khai báo rõ; giữ 5173 và thêm 5198, không wildcard |
| 167 ảnh main | **PASS**, HTTP response/hash khớp 167 file mới import; `http-results.json` |
| Gallery/ảnh biến thể | **PASS**, fixture 510 → 436 và gallery trả đúng; không tuyên bố phục hồi nguồn HOLD ID 172 |
| Placeholder/ảnh thiếu | **PASS**, ID 172 dùng placeholder; URL file thiếu trả 404; FE fallback/reset tests đạt |
| Login Customer/Admin | **PASS API và browser**, dùng credential ngẫu nhiên chỉ sinh tại máy cài |
| Sản phẩm/tìm kiếm/chọn màu-size/giỏ | **PASS browser**, truy cập chi tiết, tìm `Áo`, chọn Đen/M, thêm giỏ |
| Checkout | **PASS**, quote giỏ và đơn COD tổng hợp qua API; browser đặt thêm một đơn COD thành công. Không gọi thanh toán thật |
| Admin | **PASS**, API đọc sản phẩm, kiểm từ chối Guest/Customer, upload ảnh mới và sắp xếp gallery; browser đăng nhập/dashboard/danh sách 168 sản phẩm |
| FE tests | **PASS 78/78**, không skip; `fe-tests-v2.txt` |
| BE tests | **90 passed, 2 skipped**, tổng 92; 1.381 assertions; `be-tests-v2.txt` |
| Build | **PASS**, `build-v2.txt`, chạy ngay trong bản giải nén v2 |
| Console | **PASS trong lượt browser đã chạy**, 0 error/warning được ghi nhận; `browser-console.json` |
| FK sau kiểm thử | **PASS**, 20 quan hệ, 0 orphan; `db-results.json` |
| Main PO không đổi | **PASS**, hash catalog/lịch sử đơn, 187 ảnh và `.env` giữ nguyên; `preservation.json` |
| Máy vật lý khác/OS khác | **CHƯA KIỂM THỬ** |
| Offline/ngắt Internet thực tế | **CHƯA KIỂM THỬ**, không suy ra từ ảnh local |
| SMTP/OAuth/ngân hàng thật | **KHÔNG CẤU HÌNH/NGOÀI PHẠM VI**; demo không gửi email/chuyển tiền ra ngoài |

V1 từng **FAIL 1 BE test CORS** do installer chỉ lưu origin port mới. Đã sửa **installer đóng gói**, giữ origin local mặc định cộng port được chọn, tạo v2 và lặp lại cài/import/tests. Không sửa test để bỏ assertion hoặc mở wildcard. V1 không phải bản đề nghị bàn giao.

Hai BE skip là các bước export fixture browser tùy chọn khi thiếu `SOFEP_UAT_DIR`; không phải 92/92 đã chạy. Browser UAT của gói v2 được kiểm thử riêng bằng credential installer. SQLite memory của PHPUnit không được coi là bằng chứng thay thế MySQL; bằng chứng MySQL là installer, API/CRUD demo và kiểm FK thực tế.

HTTP verifier có **188 kiểm tra** theo kỳ vọng. Browser ghi nhận **167 ID ảnh minh họa riêng biệt**, ảnh tải đúng và ID 172 placeholder; không còn nhãn ảnh. Không có lỗi console trong các luồng đã chạy. Không dùng con số thẻ ảnh DOM có lặp làm số sản phẩm.

### Trạng thái UAT sau kiểm thử khác catalog trong ZIP

ZIP vẫn có 167 gallery và không có đơn/tài khoản/token cài sẵn. Các tài khoản chỉ sinh lúc setup. Sau test trên database UAT có **2 đơn tổng hợp**, 2 dòng đơn và **168 gallery** vì đã thử upload thêm một bản sao JPEG vào sản phẩm 1 (ID ảnh thử 515). 167 ảnh ban đầu giữ hash, FK không lỗi. Dữ liệu phát sinh này chỉ ở UAT, không được đóng lại vào ZIP và không được sao chép từ nguồn chính.

## Kiểm tra an toàn gói

Allowlist catalog không có bảng khách/đơn/tài khoản/token và không có cột password/email/số điện thoại/địa chỉ khách. Không có URL localhost hoặc đường dẫn máy PO trong catalog; main/gallery dùng `/api/product-images/...`, ID 172 dùng `/product-placeholder.svg`.

Kiểm các đường file cấm, marker liên hệ/tài khoản/đường dẫn riêng PO và mẫu credential mạnh không thấy trong gói. `.env`, private access file và token runtime chỉ sinh **sau giải nén**, không có trong archive. Test tự động vẫn chứa chuỗi fixture tổng hợp như mật khẩu giả để kiểm xác thực; chúng không phải credential tài khoản cài sẵn hoặc bí mật thực. Thông tin tác giả/license công khai của thư viện assets được giữ.

Không tuyên bố chứng nhận pháp lý tuyệt đối về mọi nội dung; 167 ảnh giữ provenance/checksum đúng bộ đã audit, không tải bộ ảnh mới. 20 file HOLD và URL ảnh cũ ngoài bộ không được đưa thêm vào gói. Gói không phụ thuộc thư mục upload PO: media đã nằm trong ZIP, import sang Storage mới và tải qua HTTP mới thành công.

## Hướng dẫn nộp thầy và điểm PO cần chốt

[INSTALL.md](INSTALL.md) có các bước PHP/Composer/Node/MySQL → cài đúng lockfile → setup/import → chạy Laravel/Vite → login bằng tài khoản sinh mới → chứng minh Navicat. Trong Navicat, mở database đúng tên từ `install-result.json`, đối chiếu `san_phams.hinh_anh`, `anh_san_phams`, FK biến thể và file Storage/URL HTTP. `image-map.json` là mapping/checksum 167 ảnh dùng trong kiểm thử.

Đề nghị PO nghiệm thu **đúng ZIP v2 + checksum**, không lấy ZIP v1 hoặc clone HEAD đơn lẻ. Các bản vá working tree chưa commit/push; bộ ZIP chứa snapshot đã test, không thay Git history. Trước phát hành theo GitHub cần yêu cầu riêng từ PO, nhiệm vụ này không commit/push/deploy.

Các điểm còn lại: nghiệm thu trên máy vật lý thứ hai nếu đó là tiêu chí bắt buộc; kiểm offline thực tế; cung cấp SMTP/OAuth thật nếu muốn demo các luồng đó; xem và chấp nhận 3 redaction bản đóng gói, credential sinh mới và fixture biến thể. Internet vẫn cần khi cài dependency chưa có cache, một số tài nguyên/luồng ngoài ảnh sản phẩm có thể cần mạng. Không kết luận toàn ứng dụng offline.

## File/công cụ đã tạo và giới hạn

File mới trong `docs/submission-handoff-2026-11-21/`: builder/auditor, installer/guide mẫu, launcher/probe/verifier UAT, inventory, kết quả install/platform/tests/build/API/DB/browser, ảnh chụp và báo cáo. File bàn giao trong `outputs/sofep-submission-2026-11-21/`. Thư mục TEMP/source giải nén chứa runtime/credential không được giao. Gói v1 được giữ làm bằng chứng, không xóa.

Không sửa source ứng dụng hoặc môi trường PO; chỉ tạo bản sao đã sanitize trong TEMP và file tài liệu/output. Không sửa/xóa `doan_nhom_5`, không dọn 227 dòng nguồn, không xóa ảnh gốc, không thay lịch sử đơn, không tác động N.Cảnh Barber, không commit/push/deploy, không chỉnh Jira/Proposal.
