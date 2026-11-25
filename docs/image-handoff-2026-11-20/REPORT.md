# SOFEP — kiểm định gói bàn giao ảnh demo

Ngày 20/11/2026, Asia/Saigon. Nhánh `nhanh-cua-duong`. Tiếp tục từ báo cáo `docs/image-clone-2026-11-20/REPORT.md`. Chỉ tạo công cụ/báo cáo trong thư mục này và runtime UAT riêng; không sửa source ứng dụng, gói ZIP gốc, database/storage chính, Jira/Proposal, N.Cảnh Barber; không commit/push/deploy.

## Kết luận

**FAIL cho quy trình cài đặt mới toàn ứng dụng; CHƯA sẵn sàng bàn giao như bộ “clone → cấu hình → thấy ảnh trên frontend”.** Bộ ZIP hiện **PASS cho import catalog/ảnh và kiểm tra qua Laravel/thư viện demo độc lập**. Không đánh đồng hai kết quả.

| Hạng mục | Kết quả | Bằng chứng/giới hạn |
| --- | --- | --- |
| Giải nén đúng ZIP, đủ file, checksum | PASS | 175 file đúng allowlist, 167 ảnh khớp SHA-256 |
| Import vào database/storage mới từ source sạch | PASS | 24 migration, database và storage riêng; không có bản ghi khách/đơn/tài khoản/token |
| URL catalog/import không gắn backend cũ | PASS | URL ảnh tương đối; toàn bộ verifier chạy tại port **8042**, không phải 8000/8038 |
| Laravel HTTP ảnh chính/gallery | PASS | 167/167 ảnh đúng JPEG/hash; 167/167 cặp main/gallery đúng API |
| Ảnh biến thể | PASS giới hạn fixture | Fixture synthetic ID 510 → ảnh 436 được xác minh; không phải khôi phục ảnh thật cho 9 liên kết gốc ID 172 |
| Placeholder/ảnh thiếu | PASS | ID 172 tải placeholder; hai ảnh thiếu/tên sai trả 404 đúng |
| Frontend dùng API origin mới | **FAIL** | `VITE_API_URL` đã nạp nhưng home/search/detail vẫn gọi port 8000; browser báo không tải được sản phẩm |
| CORS cho origin frontend UAT 5192 | **FAIL cấu hình UAT này** | Không có `Access-Control-Allow-Origin`; source mặc định cho 5173, không tự đổi CORS chính |
| Chính sách ảnh working tree và source sạch thống nhất | **FAIL** | Bản chặn illustration hiện chưa nằm trong `HEAD`; clone commit đang test không có bản vá đó |
| Offline thực tế | **CHƯA KIỂM THỬ** | Browser API không có điều khiển network-offline; không tắt Internet hệ điều hành |
| Không có dữ liệu nhạy cảm/ngoài phạm vi trong ZIP | PASS trong phạm vi kiểm tra | Allowlist file/cột catalog, kiểm tra dữ liệu và metadata ảnh; không phải chứng nhận quyền thương mại đúng SKU |
| Cài Composer/npm từ đầu trên máy vật lý khác | **CHƯA KIỂM THỬ** | Dùng lại dependency có sẵn khớp lockfile trên source sạch cùng máy |

## 167 ảnh, placeholder và ID 172 là các số khác nhau

- **167 file trong ZIP**: mỗi file là ảnh AI minh họa cho một trong 167 sản phẩm, dùng để kiểm thử truyền/khôi phục file. Chưa được xác nhận là ảnh thật hoặc đúng SKU.
- **168 sản phẩm trong catalog demo**: 167 sản phẩm có URL chính tương đối trỏ tới ảnh AI; ID **172 “Áo polo”** có `/product-placeholder.svg` và không có gallery phân phối.
- **Working tree hiện tại + catalog demo**: bộ xử lý URL trả placeholder cho **168/168 ảnh chính**: 167 ảnh AI bị chặn theo chính sách, cộng ID 172 đã dùng placeholder. Đây là số tính từ resolver và dữ liệu, không phải khẳng định đã thấy 168 thẻ trong browser: frontend test còn lỗi tải catalog.
- **Working tree + snapshot catalog chính đã audit**: **167/168** ảnh chính được resolver thay placeholder. ID 172 trong snapshot chính vẫn là URL website ngoài, không bị bộ lọc illustration chặn; HTTP lỗi có thể dẫn tới fallback thêm. Không thay dữ liệu chính để làm số lượng giống demo.
- **Source sạch `HEAD` đang test + catalog demo**: resolver cũ chấp nhận ảnh illustration, chỉ **1/168** URL chính là placeholder của ID 172. Bản vá chặn illustration vẫn là thay đổi working tree, không có trong commit được archive. Cần thống nhất source/bản vá bàn giao trước khi nghiệm thu frontend; không được dùng sự khác nhau này để tuyên bố 167 ảnh đã được duyệt đúng SKU.

Bằng chứng số lượng: `package-audit.json`, tính từ hai phiên bản `productImages.js` và catalog tương ứng. `HEAD` là `ece155286b4dbbd59903c1484d71fe26bc7616d5`; không đổi nhánh hoặc tạo commit để che chênh lệch.

## UAT mới bắt đầu từ ZIP thật

Đã kiểm tra ZIP SHA-256 `972561984c18aed8a24928ab9fad6364def20198b457d845033ab38559b6a7b4` với file `.sha256`, giải nén mới vào thư mục TEMP khác. Dùng chính `import.php`, `boot.php`, `server.php`, `verify.mjs` trong bản giải nén này; không dùng thư mục media nguồn làm storage phục vụ ảnh.

Source sạch là archive commit trên được giữ từ audit trước. Code application không bị overlay bằng working tree. Dependency vendor có sẵn khớp `composer.lock`; frontend cũng dùng dependency có sẵn theo lockfile. Không cài/nâng dependency, không sửa lockfile. Chưa clone remote GitHub/cài dependency mới trên máy vật lý thứ hai.

Database mới **`sofep_image_restore_20261120_7dab8307016c`**, storage mới **`<runtime>/storage/app/public`**, port backend **8042**. Đường dẫn runtime cụ thể tại `uat.json`, nguồn ZIP/source tại `inputs.json`. Backend chỉ cấu hình connection `demo`, kiểm tra `SELECT DATABASE()` đúng prefix/database UAT trước phục vụ; cache/session/mail/log/storage cũng cô lập. Dùng chung dịch vụ MySQL trên máy nhưng database riêng, không copy database thật. Launcher local chỉ đọc credential kết nối local trong bộ nhớ để khởi động; credential không lưu vào báo cáo/ZIP và không cần lấy từ máy PO khi thành viên tự chạy.

Import thật có 8 danh mục, 6 thương hiệu, 7 màu, 6 size, 168 sản phẩm, 167 gallery, 501 biến thể; 24 migration. 501 gồm 500 biến thể catalog và một fixture tổng hợp ID 510, không màu/size, liên kết ảnh ID 436 của sản phẩm 1. Các bảng khách hàng, đơn, dòng đơn, users và token đều **0 bản ghi**. Xem `import-result.json`.

Verifier nguyên bản trong ZIP hoàn thành **`passed: true`**, 341 kiểm tra GET và POST guest upload riêng: ảnh chính/gallery/ảnh biến thể fixture/placeholder đều đúng; khách upload bị 401. Xem `laravel-http.json`. JSON/catalog ảnh không chứa origin 8000; mọi URL trong lượt này resolve theo backend 8042. Không đổi URL database chính để đạt kết quả.

## Lỗi frontend và cách thử an toàn

Đã chạy Vite từ `Fe/` source sạch tại 5192, đặt `VITE_API_URL=http://127.0.0.1:8042/api`. Cấu hình CSP **chỉ ở server frontend UAT** cho phép kết nối backend 8042, chặn port 8000 và nguồn bên ngoài để không gọi nhầm môi trường chính.

Browser tích hợp mở được app nhưng sau đó hiển thị **“Không thể tải sản phẩm. Vui lòng thử lại.”**. Mã module Vite đã transform xác nhận biến môi trường 8042 tồn tại trong service ảnh, trong khi `TrangChu.vue`, `TimKiem.vue`, `ChiTietSanPham.vue` vẫn chứa URL API 8000. Trong working tree cũng có bằng chứng tại các lời gọi API ở các file này. CSP chủ động làm lộ phụ thuộc sai origin; đây không phải 167 file ảnh bị thiếu. Không dùng backend chính ở 8000 để làm frontend trông như đạt.

GET catalog Laravel tại 8042 vẫn 200. Gửi Origin frontend 5192 không có header CORS cho origin đó; mặc định source cho phép 5173. Việc chọn port frontend khác đòi hỏi cấu hình CORS UAT tương ứng. Đây là vấn đề riêng, không phủ nhận bằng chứng URL API ghi cứng. Xem `frontend-check.json` và `frontend-vite.mjs`. Chưa sửa source API base hoặc CORS chính trong task kiểm định này.

## 20 file giữ lại và ảnh còn thiếu

Đã đối chiếu từng file trong danh sách giữ lại, không chỉ đếm. **19 file** có bản ghi nguồn lịch sử **Pexels License** trong `sources.json`, nối qua `published.json`; SHA-256 file upload khớp SHA-256 nguồn. Như vậy không nên gọi chúng là “không có thông tin nguồn”. Tuy nhiên chưa xác minh lại đầy đủ điều kiện phân phối, việc tuân thủ giấy phép và ánh xạ đúng SKU với PO; vẫn **HOLD**, không tự đưa vào ZIP.

**Một file của ID 172** (`products/172/dd7b32f3-7ee8-4f6e-b7f0-e256ee801dc9.jpg`) chưa nối được với bản ghi nguồn/quyền dùng; vẫn HOLD. URL ngoài `https://thoitrangbigsize.vn/wp-content/uploads/2025/03/den-3.jpg` không được tải xuống/đưa vào gói. ID 172 thiếu ảnh chính/gallery được phép phân phối và 9 liên kết ảnh biến thể tương ứng. Trong demo, 9 liên kết được để null; database chính giữ nguyên.

Không có file nào trong **167 file đã đóng gói** bị thiếu sau import. Phần chưa được phân phối là **20 file local**, **87 URL Unsplash** và **1 URL ngoài khác**; không phải 108 lỗi khôi phục. Chưa có ảnh chụp đúng SKU được PO phê duyệt để thay chính sách placeholder cho 167 sản phẩm. Danh sách từng file, product ID, checksum, URL nguồn, giấy phép đã ghi nhận và quyết định HOLD tại `20-files-review.json`; không thay đổi ZIP.

## Kiểm tra dữ liệu và quyền riêng tư của ZIP

175 file gồm 167 ảnh và đúng 8 file gốc: README, boot/import/server/verify, manifest, catalog và placeholder. Không có file lạ/thiếu, `.env`, SQL dump, log, session, credential file, khách hàng, đơn hàng hay token. Catalog chỉ có bảy bảng/cột allowlist cần cho ảnh; không có password/email/customer/order/auth columns, URL máy PO hoặc đường dẫn tuyệt đối trong catalog/manifest. Script có tên biến môi trường PASSWORD nhưng không chứa mật khẩu thực tế. Không coi từ khóa cấu hình là credential bị lộ.

Đã đọc metadata của 167 JPEG trong bản ZIP giải nén: chỉ APP0/JFIF và APP1/EXIF; EXIF có đúng ba tag byte/số hằng đã đối chiếu, không có tag bổ sung cần duyệt, không thấy metadata GPS/tác giả/owner/comment/XMP/IPTC theo kiểm tra segment và parser. Không chỉnh sửa bytes ảnh. Xem `metadata.json`. Ảnh có provenance AI được dùng cho demo transport, chưa phải chứng nhận quyền dùng thương mại/đúng SKU. Không phát hành gói qua dịch vụ bên ngoài trong task này.

## Offline và bảo toàn

**Chưa test offline thực tế**: API browser hiện có không cung cấp điều khiển network-offline; không tự tắt mạng máy đang dùng. CSP và URL relative chứng minh không cần tải nguồn ngoài cho thư viện demo nhưng **không thay thế** thử nghiệm khi ngắt Internet. Full frontend còn có tài nguyên/luồng khác, chưa nghiệm thu offline.

Đối chiếu read-only catalog/snapshot ảnh nguồn sau kiểm thử vẫn cùng fingerprint. 187 file storage chính giữ hash, không mất file; 23 URL/41 tham chiếu lịch sử đơn nguyên vẹn. Trong phạm vi kiểm kê hiện có **0 file hết tham chiếu**, không đề xuất xóa. Xem `main-source-unchanged.json`, `preservation.json`. Không sửa snapshot hoặc sao chép dữ liệu đơn sang UAT.

## Điều kiện để bàn giao toàn quy trình

1. Chuẩn hóa API base frontend theo backend được cấu hình và CORS theo origin frontend UAT; kiểm thử lại home/search/detail, ảnh main/gallery/variant và 404/placeholder. Chưa triển khai sửa trong task audit này.
2. Thống nhất bản source/bản vá placeholder được bàn giao: working tree và `HEAD` đang khác chính sách, không thể giả định clone có bản vá chưa commit. Giữ nguyên giới hạn không commit/push.
3. PO duyệt ảnh/giấy phép/SKU cần hiển thị thật; ID 172 và các ảnh HOLD phải có quyết định rõ. Không đổi tên `illustration-` hoặc gỡ placeholder để che thiếu ảnh đúng SKU.
4. Chạy lại từ dependency cài mới trên máy thành viên; test offline thực tế trong môi trường riêng nếu cần tiêu chí offline. Không đánh dấu các tiêu chí chưa chạy là PASS.

Hướng dẫn ngắn đã chuẩn bị tại **`MEMBER_GUIDE.md`**, ghi rõ phần backend đang dùng được và bước frontend chưa đạt. Có thể bàn giao ZIP dưới nhãn **“bộ kiểm thử backend/ảnh AI minh họa — chưa nghiệm thu frontend”**; **chưa sẵn sàng bàn giao như demo ứng dụng hoàn chỉnh**.

## File mới, lệnh kiểm tra và hạn chế

File mới trong thư mục này: `launch.php`, `frontend-vite.mjs`, `audit-package.mjs`, `frontend-check.mjs`, `check-metadata.py`, `MEMBER_GUIDE.md`, `REPORT.md`; các JSON gồm inputs/uat/import-result/laravel-http/package-audit/20-files-review/frontend-check/metadata/main-source-unchanged/preservation. Source runtime/dependency chỉ trong clone/TEMP; không thay source ứng dụng chính.

Đã chạy: Git branch/status; checksum ZIP và Expand-Archive mới; importer từ ZIP; verifier từ ZIP tại 8042 (PASS); Vite source sạch tại 5192 và browser (FAIL nối catalog); kiểm tra module transform/CSP/CORS; audit allowlist/checksum/dữ liệu; đối chiếu nguồn 20 file; đọc metadata JPEG; read-only kiểm tra bảo toàn nguồn. Cài dependency từ đầu, máy vật lý khác, offline thực tế, upload với tài khoản Admin/Customer/Seller chưa chạy. Không báo các phần đó thành công.

Các file công cụ mới: `launch.php`, `frontend-vite.mjs`, `frontend-check.mjs`, `audit-package.mjs`, `review-exclusions.mjs`, `check-metadata.py`. `php -l launch.php`, `node --check` cho ba script kiểm tra và `git diff --check` đều đạt; Git chỉ cảnh báo chuyển đổi line ending ở các thay đổi có sẵn. Không chạy build/test frontend lại vì không sửa source ứng dụng. ZIP gốc được giữ nguyên checksum.
