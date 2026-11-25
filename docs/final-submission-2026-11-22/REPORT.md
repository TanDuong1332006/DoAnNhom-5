# SOFEP — tình trạng chốt source nộp đồ án, 22/11/2026

**CHƯA SẴN SÀNG đóng bản nộp chính thức.** Cài sạch ZIP v2 và các luồng chính đã kiểm thử lại đạt; còn một lỗi High bắt buộc xử lý trước nộp và quyết định PO/PM về việc gỡ trang đánh giá cũ. Không tích hợp Figma, không thay thiết kế hay mở rộng nghiệp vụ.

Final Audit trước đó đã hoàn thành: [REPORT 21/11](../final-audit-2026-11-21/REPORT.md), [chi tiết lỗi](../final-audit-2026-11-21/FINDINGS.md). Báo cáo này bổ sung cài sạch mới và quyết định phạm vi nộp, không xóa/ghi đè bằng chứng cũ.

## Lỗi và quyết định trước nộp

| Mức | Tình trạng | Xử lý đề nghị |
|---|---|---|
| Critical | Chưa phát hiện lỗi đủ bằng chứng trong phạm vi audit | Không coi đây là chứng nhận toàn hệ thống an toàn |
| High F01 | Còn tồn tại: xóa danh mục cascade sản phẩm, đi vòng qua bảo vệ sản phẩm có lịch sử đơn, mất liên kết và có thể làm hủy/hoàn tồn thất bại | **Bắt buộc sửa trước nộp**: chặn danh mục còn sản phẩm, không đổi schema/main. Phạm vi file và phản hồi 409 cần duyệt ở DECISIONS A |
| Medium F02 | Customer không có luồng tạo đánh giá; main/demo có 0 dòng; `/admin/danh-gia` trống đúng dữ liệu | Theo quyết định ổn định/không mở rộng: đề nghị gỡ trang/menu/API quản lý cũ sau PO duyệt, không xây chấm sao mới |
| Low F03 | ID 172 dùng placeholder đúng, nhưng chi tiết đếm là “1 ảnh sản phẩm” và cho zoom | Có thể hoãn để tránh rủi ro trước nộp; không đổi URL/nguồn ảnh |

**Chưa sửa file ứng dụng hoặc gỡ code đánh giá.** [DECISIONS.md](DECISIONS.md) là phương án cụ thể A/B để PO/PM duyệt. A thay phản hồi API khi bị chặn, cần duyệt riêng theo AGENTS.md; skill `sofep-bug-fixer` mục 6 yêu cầu “Xin phép và chờ người dùng đồng ý với bản vá cùng danh sách file trước khi chỉnh sửa.” B còn chịu yêu cầu trực tiếp của PO chưa xóa code/database trước duyệt. Không tự đóng ZIP final để vượt qua bước này.

## Đánh giá sản phẩm và góp ý nền tảng

Xem [REVIEW_RETIREMENT.md](REVIEW_RETIREMENT.md). Main chỉ đọc COUNT: **0 đánh giá, 0 được duyệt**. Demo mới cũng 0; API Admin 200 trả mảng rỗng, browser hiển thị “Chưa có đánh giá nào.” Không có Customer create route/form. Không tạo dữ liệu giả để che màn hình trống.

Phần đánh giá chưa hoàn toàn không còn tham chiếu: ProductDetailController trả `reviews/rating`, model/relationship/test vẫn dùng bảng. Đề nghị chỉ gỡ bề mặt quản lý cũ và 3 API Admin sau duyệt, giữ bảng/model/contract chi tiết. Không drop table/migration hoặc xóa toàn bộ code có chữ review.

Góp ý nền tảng độc lập: `FeedbackController` → `private_feedback`/`feedback_attachments`; phản hồi Admin → `customer_notifications`; CustomerInbox bảo vệ theo chủ tài khoản. **15/15 kiểm tra HTTP góp ý/phản hồi/inbox đạt**, browser xác nhận hiển thị phản hồi. Danh sách đánh giá vẫn rỗng sau vòng này. Phải giữ `/gop-y`, `/admin/gop-y`, `/thong-bao`, ví/campaign, phân quyền và ảnh góp ý riêng.

## Cài sạch lại từ chính ZIP v2

Lượt mới, không dùng lại database/storage UAT 5198/8045:

- Giải nén archive v2 vào TEMP mới `sofep-final-install-b297de044aec4ae98af6019a5278e819` (đường dẫn đầy đủ trong `uat-location.json`). Không symlink/copy vendor/node_modules/Storage PO.
- Cài mới dependency từ lockfile: Composer 107 package, npm 60 package; không update/đổi lockfile. Log `composer-install.txt`, `npm-ci.txt`, exit 0.
- Chạy installer nguyên trong ZIP với port **BE 8046, FE 5199**. Wrapper bên ngoài archive chỉ truyền credential MySQL local trong bộ nhớ, không in/đóng credential vào báo cáo; installer không đọc env PO. Env/key chỉ tạo trong bản giải nén mới theo quy trình đã được yêu cầu kiểm thử.
- MySQL mới **sofep_demo_20261122_a1fec473a820**, 26 migration; import 8 danh mục, 6 brand, 7 màu, 6 size, 168 sản phẩm, 167 gallery, 501 biến thể. 501 = 500 catalog + fixture ảnh biến thể 510 → gallery 436.
- Import **167 file ảnh** checksum đúng; ID 172 `/product-placeholder.svg`. Hai account demo tạo ngẫu nhiên tại máy cài. Ban đầu **0 đơn, 0 token** — `install-result.json`.
- Chạy `php artisan serve --host=127.0.0.1 --port=8046 --no-reload`, Vite `--port 5199 --strictPort`; backend thật, MySQL thật và public Storage của bản giải nén mới. Không dùng port 8000/storage của PO.

Đây là **cài sạch từ ZIP mới trên cùng máy vật lý**, không phải clone GitHub HEAD hoặc chứng minh máy/OS khác. Toolchain PHP/Node/MySQL có sẵn; dependency ứng dụng được cài lại. Không thay môi trường chính.

## PASS / FAIL / NOT TESTED

| Kiểm tra lượt 22/11 | Kết quả và bằng chứng |
|---|---|
| Giải nén/cài Composer/npm/import MySQL/Storage mới | **PASS**, các log cài đặt và install-result |
| FE tests | **PASS 78/78**, `node --test tests/*.test.mjs`, fe-tests.txt |
| BE tests | **90 PASS, 2 SKIP**, 1.381 assertions, `php artisan test`, be-tests.txt |
| Build sau setup port mới | **PASS**, `npm run build`, build.txt |
| HTTP/API/ảnh | **382/382 kiểm tra PASS**, http-results.json; số kiểm gồm status/hash/trạng thái, không phải 382 luồng UI |
| Góp ý → Admin phản hồi → thông báo | **15/15 PASS HTTP**, feedback-results.json; browser thấy phản hồi #1 |
| 167 ảnh | **PASS** HTTP 200, SHA-256 khớp file vừa import; trang chủ browser 181/181 thẻ ảnh tải được, 167 ID ảnh catalog riêng biệt (có ảnh lặp và 9 Unsplash trang trí) |
| ID 172 | **PASS placeholder tải qua HTTP/browser**, Low caption/zoom chưa sửa |
| Gallery/ảnh biến thể | **PASS API** gallery 436/fixture 510; không suy ra mọi màu có ảnh riêng đúng SKU |
| CORS allow/deny, Guest/Customer/Admin | **PASS HTTP**, origin 5199 được phép, origin không khai báo không được phép; Customer Admin403, Guest401, Admin checkout403 |
| Customer login/tìm kiếm/Đen-M/giỏ | **PASS browser**, tài khoản demo mới, tìm Áo 67 kết quả, mua đúng biến thể |
| COD | **PASS HTTP và browser**, browser tạo thêm đơn tổng hợp thành công, trạng thái Chưa thanh toán |
| Thanh toán mô phỏng | **PASS HTTP** failed/success/persisted, không ngân hàng thật; browser mô phỏng không chạy lại ở lượt này |
| Admin login/dashboard/đánh giá | **PASS browser** tải dashboard và danh sách đánh giá rỗng đúng dữ liệu |
| Các danh sách Admin khác/validation | **PASS HTTP** sản phẩm/biến thể/danh mục/khách/voucher/đơn; create rỗng 422, upload rỗng 422; chưa lặp toàn bộ CRUD qua browser |
| Browser console | 0 error/warn ở lượt đã duyệt, browser-console.json |
| FK MySQL sau tests | **PASS**, 20 quan hệ, 0 orphan; 167 ảnh giữ hash, db-results.json |
| Xóa danh mục an toàn | **FAIL F01** từ code+schema audit; không chạy thao tác xóa làm mất catalog |
| Full OTP qua email thật | **NOT TESTED**, demo dùng mailer array và tài khoản sinh sẵn; suite có kiểm OTP nhưng không thay thế email/browser E2E |
| Offline ngắt Internet, máy vật lý/OS khác | **NOT TESTED**; còn CDN Bootstrap/font/Unsplash/logo ngoài, không kết luận offline đạt |

BE skip là hai bước export fixture browser tùy chọn khi không đặt SOFEP_UAT_DIR; không phải mọi test đều đã chạy. PHPUnit dùng SQLite memory và mail array, đã đọc cấu hình trước chạy; bằng chứng MySQL là installer/API và FK thật riêng. FE tests có cả kiểm source/service, không được gọi là toàn bộ E2E.

Browser evidence: `admin-reviews.png`, `browser-cod-success.png`, `browser-feedback-inbox.png`, `browser-placeholder172.png`, `browser-results.json`, `home.json`. Vòng gửi góp ý/phản hồi thực hiện qua HTTP, browser chỉ xác nhận thông báo; phân biệt rõ với thao tác UI.

Sau smoke UAT mới: 168 sản phẩm, 167 gallery, 501 biến thể, 1 Customer, 1 Admin; **3 đơn/3 dòng tổng hợp**, 1 góp ý + phản hồi/thông báo tổng hợp; các token chỉ phát sinh ở runtime UAT. Không thêm ảnh upload mới. Dữ liệu phát sinh không nằm trong ZIP và không xuất từ main.

## So ZIP v2 với working tree và thành phần bản nộp

- Nhánh `nhanh-cua-duong`. Đối chiếu **999 file source** trong SOURCE_MANIFEST với bản nguồn working tree: **0 khác biệt** so snapshot v2. **97 file source/test trong manifest** đang có trạng thái modified/untracked, chưa commit/push. Không đồng nghĩa GitHub HEAD đã chứa các bản vá.
- `git status --porcelain -uall` ghi 4.584 đường dẫn tại lúc kiểm kê (bao gồm hàng nghìn artifact/docs/output đã có; khác số mục directory-grouped ở báo cáo trước). [WORKTREE.md](WORKTREE.md) và working-tree.json liệt kê từng file; không xóa các file đó. Source-comparison.json liệt kê 999 checksum.
- Ba file source trong ZIP đã được ẩn thông tin liên hệ/thanh toán khi đóng v2 được đánh dấu trong manifest: CheckoutModal.vue, hai ClientLayout.vue. Root PO vẫn khớp checksum nguồn; không tự đưa thông tin riêng trở lại bản phân phối.
- Kiểm trực tiếp ZIP: **1.174 entry** khớp checksum bản giải nén, **0 đường dẫn cấm** `.env*`/credential runtime/vendor/node_modules/dist/log/SQL/sqlite, `zip-direct.json`. SHA-256 vẫn `b42640750e5c0c7902a22ffa23858b34f98db88e5055e77f389740e325a2b16e`; không ghi đè/tạo ZIP final.
- Nội dung ZIP không đổi byte so audit v2 trước: giữ kết quả kiểm allowlist 7 bảng catalog, không chứa khách/đơn/token/tài khoản thật; không chứa private credentials tạo sau cài. Các chuỗi fixture test không phải credential tài khoản cài sẵn. Không coi pattern scan là bảo đảm pháp lý tuyệt đối mọi nội dung.

[HANDOFF_CONTENTS.md](HANDOFF_CONTENTS.md) chốt nhóm cần giữ/không nộp, hướng dẫn giảng viên, tài khoản demo và các bước cài. File demo-access.json chỉ sinh tại máy cài, không đưa vào Git/ZIP. Có hướng dẫn Navicat đối chiếu MySQL ↔ API ↔ Storage. Các file thử nghiệm/docs audit là bằng chứng nội bộ, không đưa nguyên vào source nộp.

## Giới hạn an toàn và việc còn lại

`preservation.json` xác nhận catalog/lịch sử đơn/env chính giữ hash; **187 ảnh** và **23 URL/41 tham chiếu ảnh lịch sử** không đổi. Chỉ SELECT số lượng đánh giá main; không chỉnh/xóa main, không chạy seeder main, không sửa lịch sử. Không tác động N.Cảnh Barber, Jira, Proposal; không commit/push/deploy. Chỉ tạo tài liệu/runner/bằng chứng trong `docs/final-submission-2026-11-22/` và môi trường UAT mới theo yêu cầu.

**Cần PO/PM duyệt tiếp:** A sửa High với danh sách file/response 409 cụ thể; B gỡ menu/route/API quản lý đánh giá cũ, giữ góp ý và dữ liệu. Low có thể hoãn. Sau khi sửa các phần được duyệt phải chạy hồi quy, đóng bộ mới và kiểm cài sạch theo phiên bản đó; **PASS v2 hiện tại không chứng minh bản vá tương lai đã đạt**. Chưa đóng gói bản chính thức và chưa đề nghị nộp khi F01 còn mở.
