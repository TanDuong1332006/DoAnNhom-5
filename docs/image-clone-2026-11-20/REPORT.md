# SOFEP — khôi phục bộ ảnh demo khi clone source

Ngày 20/11/2026, Asia/Saigon. Tiếp tục audit tại `docs/image-audit-2026-11-20/REPORT.md`. Nhánh `nhanh-cua-duong`, không chuyển nhánh, commit/push/deploy, sửa Jira/Proposal hoặc truy cập N.Cảnh Barber. Mọi file mới thuộc `docs/image-clone-2026-11-20/`; không sửa source ứng dụng trong đợt này.

## Nguyên nhân 169 timeout

Đã xác nhận toàn bộ 169 URL từng timeout có origin `http://127.0.0.1:8000`, thuộc route Laravel `/api/product-images/...`; không thuộc Unsplash hay website bên ngoài. Port 8000 có listener PHP 8.3.24 built-in server `php -S`, một worker trên Windows, dùng router `vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php`. Không đổi hoặc dừng server này.

Audit trước dùng concurrency 8, timeout 8.000 ms. Đo lại cùng 8 URL từng lỗi, vẫn concurrency 8: hạn 8 giây gây 3 timeout/5 HTTP 200; hạn 20 giây cho 8/8 HTTP 200. Kiểm tra tuần tự với `Connection: close`, hạn 20 giây cho **169/169 URL từng timeout trả HTTP 200**, khoảng 851–2142 ms/ảnh, trung vị 1285 ms. `/up` trả 200 và route không tồn tại trả 404 đúng khi kiểm tra tuần tự. Lần probe hạn 4 giây có kết nối TCP thành công nhưng `/up` và route không tồn tại chưa trả HTTP kịp.

Kết luận được chứng minh: server phát triển một worker có thời gian phục vụ khoảng 1–2 giây/yêu cầu; hàng đợi của 8 request song song có thể vượt hạn 8 giây. Đã tái hiện timeout trên file hợp lệ và lấy lại toàn bộ URL từng lỗi bằng cách kiểm tra tuần tự. Đây là timeout của môi trường phục vụ/cách đo, không phải 169 file mất hoặc URL 404. Chưa profiling để quy toàn bộ thời gian xử lý cho PHP, disk I/O, antivirus hay từng middleware; không tự thay cấu hình môi trường để che vấn đề.

Bằng chứng: `timeout-diagnostics.json`, `timeout-diagnostics-all.json`, `concurrency-comparison.json`. Các JSON ghi từng URL, mã HTTP, số byte, thời gian và checksum khi có. Không tự tăng timeout frontend hoặc sửa URL trong database thật. Quy trình kiểm kê mới dùng tuần tự và hạn 20 giây, ghi riêng timeout/404/checksum mismatch; không tăng tải vào development server như một benchmark production.

## File vật lý và bộ chuyển máy

| Hạng mục | Số lượng |
| --- | ---: |
| URL local upload và file vật lý tìm thấy | 187/187 |
| File nguồn thiếu | 0 |
| Ảnh kèm gói, checksum khớp manifest nguồn AI | 167 |
| File local giữ ở nguồn, chưa đưa vào gói | 20 |
| Trong 20 file: ảnh minh họa cũ ngoài manifest AI | 19 |
| Trong 20 file: ảnh upload sản phẩm 172 | 1 |
| Unsplash không đóng gói | 87 URL |
| Website ngoài khác không đóng gói | 1 URL |

167 ảnh được đối chiếu cả SHA-256 file storage và SHA-256 trong `docs/product-content/full-catalog/manifest.json`, bao gồm bản ghi published. Chỉ chuyển tài nguyên có provenance ghi tạo riêng bằng image_gen cho demo. Điều này cho phép kiểm thử file demo, KHÔNG xác nhận đây là ảnh thật hoặc đúng mẫu SKU. Không dùng cờ `approved` cũ để bỏ qua chính sách placeholder production.

Gói `sofep-image-demo.zip`, khoảng 27,16 MB (27.155.244 byte), gồm `media/products/{id}/illustration-UUID.jpg`, manifest SHA-256, placeholder, catalog allowlist, công cụ import, server UAT, verifier và README. `sofep-image-demo.zip.sha256` là checksum archive; `archive-verification.json` ghi đã giải nén sang thư mục TEMP khác và kiểm tra lại đủ 167 ảnh, không mismatch. Checksum ZIP có thể thay đổi khi đóng lại archive; dùng file `.sha256` đi kèm làm giá trị chuẩn.

Không đưa vendor, `.env`, thông tin kết nối thực tế, log, lịch sử đơn, khách hàng, tài khoản hoặc token vào gói. `package-safety.json` xác nhận không có bảng nhạy cảm, origin localhost hoặc đường dẫn máy PO trong catalog/manifest. JPEG có 3 tag metadata dạng byte/số hằng giống nhau; đã đọc cả 167 file, không có GPS/tên tác giả/owner/serial/tag khác trong EXIF được parser trả về; không chỉnh bytes để giữ checksum nguồn. Xem `metadata-check.json`.

20 file loại trừ và từng URL ngoài nằm trong `excluded-images.json`; đây là loại trừ khỏi gói phân phối, KHÔNG phải danh sách đề nghị xóa. Với 19 file cũ có thể còn tài liệu stock trong `docs/product-content/sources.json`, cần đối chiếu quyền dùng và bản file cụ thể trước khi bổ sung. Ảnh upload sản phẩm 172 chưa có bằng chứng đủ để xác nhận quyền sử dụng/đúng SKU.

## Quy trình import và cô lập

Hướng dẫn chính xác nằm tại `portable-demo/README.md` và trong ZIP. Công cụ chỉ nhận đường dẫn Laravel sạch và thư mục runtime mới; không nhận tên database tùy ý. Nó kiểm tra checksum trước CREATE DATABASE, chỉ cho MySQL loopback, tự sinh tên `sofep_image_restore_YYYYMMDD_<random>`, và giữ đúng connection đó qua các migration/import/API.

Runtime override trong bộ nhớ: chỉ một connection database UAT; storage nằm trong thư mục UAT riêng; cache/session array, mail array, queue sync, logging null. Không đọc `.env` ứng dụng sạch, không tạo hoặc sửa `.env`, không dùng storage nguồn. Launcher local đọc credential hiện có để truyền qua environment tiến trình, không lưu credential vào file hoặc bộ gói; `local-run.php` không có trong ZIP. Trên máy thành viên, cung cấp credential local của thành viên bằng biến tiến trình theo README.

Import tạo schema bằng migration của source sạch, không sao chép toàn bộ database. Catalog chỉ gồm bảy bảng: danh mục, thương hiệu, màu, size, sản phẩm, gallery, biến thể. Toàn bộ đường dẫn ảnh demo là tương đối `/api/product-images/...` hoặc `/product-placeholder.svg`, không gắn host/port máy PO.

## Môi trường sạch và bằng chứng khôi phục

Source dùng `git archive HEAD` của commit `ece155286b4dbbd59903c1484d71fe26bc7616d5` đã được tạo trong audit trước, giữ nguyên code source sạch. Để chạy, copy dependency vendor hiện có với `composer.lock` khớp source sạch; không cài/cập nhật dependency. Không copy `.env` hoặc storage nguồn vào clone. Chỉ công cụ import mới đưa 167 ảnh trong gói vào storage runtime riêng.

Database UAT: `sofep_image_restore_20261120_fda6e65a359c`. Kết quả import thật: 24 migration, 8 danh mục, 6 thương hiệu, 7 màu, 6 size, 168 sản phẩm, 167 gallery và 501 biến thể. Không có bản ghi trong `khach_hangs`, `don_hangs`, `chi_tiet_don_hangs`, `users`, `personal_access_tokens`. Runtime và PID/port được ghi trong `uat-location.json`; kết quả import được giữ trong runtime.

501 biến thể gồm 500 biến thể catalog nguồn và **1 fixture mới ID 510** của sản phẩm 1, liên kết ảnh ID 436; fixture không màu/size, đánh dấu synthetic trong manifest để kiểm tra quan hệ Eloquent mà không giả nhận màu hàng thật. 9 liên kết ảnh gốc của sản phẩm 172 được để null CHỈ trong demo vì ảnh tham chiếu đều bị loại khỏi gói; các ID và lý do giữ trong `excluded-images.json`. Không sửa 9 liên kết này trong database chính.

Server loopback port 8038 boot Laravel thật từ source sạch. Verifier đã hoàn tất, `passed: true` trong `restore-http-results.json`: **167/167 ảnh HTTP 200, đúng JPEG và SHA-256; 167/167 cặp ảnh chính/gallery khớp API chi tiết; fixture ảnh biến thể được xác minh; sản phẩm 172 dùng placeholder**. Hai yêu cầu ảnh thiếu/tên file không hợp lệ trả 404 đúng; khách không đăng nhập upload trả 401. Có 341 kiểm tra GET được lưu, cùng POST guest upload riêng. Không dùng server file tĩnh để thay cho route Laravel trong lần test này.

Đã mở `http://127.0.0.1:8038/__demo/images` bằng trình duyệt tích hợp và quan sát ảnh đã tải, cùng nhãn “Ảnh AI minh họa UAT”. Đây là trang kiểm tra riêng của harness, không phải thay đổi giao diện bán hàng. Chưa xác nhận mọi ảnh xuất hiện trên mọi viewport chỉ bằng screenshot; HTTP verifier kiểm tra từng ảnh riêng.

## Offline và phạm vi chưa đạt

Trang preview dùng CSP `default-src 'none'; img-src 'self'`, CSS nội bộ, không JS/CDN/webfont bên ngoài; catalog/manifest chỉ có URL tương đối. Tất cả ảnh trong gói có thể được phục vụ qua loopback và MySQL local. Chưa ngắt Internet hệ điều hành hoặc dùng browser network-offline vì công cụ browser hiện có không cung cấp điều khiển mạng; không tuyên bố đã nghiệm thu browser offline thực tế. Không dùng thao tác tắt mạng máy người dùng trong lúc các tác vụ đang chạy.

Đây là khôi phục API ảnh trên source sạch **cùng máy**, không phải clone GitHub và cài mới mọi dependency trên máy vật lý thứ hai. Chưa kiểm tra cài Composer/npm từ đầu hoặc full frontend/checkout. Giao diện bán hàng trong working tree vẫn chặn ảnh minh họa; chưa hoàn tất mục tiêu ảnh đúng sản phẩm thực tế. Thành viên nhận source còn cần nhận ZIP và các công cụ chưa commit bằng kênh được PO cho phép; không có file nào được push trong tác vụ này.

## Bảo toàn và ảnh chờ PO

23 URL liên quan 41 tham chiếu snapshot dòng đơn được bảo toàn. `preservation.json` liệt kê từng URL, số tham chiếu, tình trạng file local và có/không trong gói. Không sửa snapshot; gói không chứa dữ liệu đơn. Đã đối chiếu lại SHA-256 cả 187 file với audit trước: không thay đổi hoặc thiếu file nào.

`unreferenced-files.csv` hiện chỉ có header: **0 file không còn tham chiếu** trong phạm vi catalog/gallery/biến thể/snapshot ảnh đơn. Không có ứng viên xóa được đề xuất. Kết quả không loại trừ tham chiếu từ ứng dụng/bản backup khác. Việc xóa thật vẫn cần PO/PM phê duyệt riêng.

Để thay placeholder, PO cần cung cấp ảnh chụp đúng SKU/màu/biến thể và quyền sử dụng, duyệt manifest nguồn/hash/ánh xạ. Thử upload và mapping trên UAT trước; sau đó mới xin phê duyệt riêng cập nhật catalog thật. Giữ URL/file đang được snapshot đơn tham chiếu và các ảnh minh họa cũ cho tới khi có phương án bảo tồn/xóa được duyệt. Không tự đổi tên `illustration-` thành ảnh thật để vượt bộ lọc.

## File và kiểm tra

Chỉ tạo thư mục báo cáo này. Các script nguồn: `export.php`, `diagnose.mjs`, `compare-load.mjs`, `build-package.mjs`, `boot.php`, `import.php`, `server.php`, `verify.mjs`, `local-run.php`, `inspect-uat.php`, `preserve.mjs`, `metadata-check.py`, `check-package.mjs`. Các JSON/CSV là bằng chứng catalog đọc có chọn lọc, file vật lý, provenance, loại trừ, timeout, bảo toàn, vị trí UAT và kiểm tra gói. `portable-demo/` chứa bản đóng gói kèm ảnh; ZIP/sha256 và README phục vụ chuyển máy.

Các lệnh đã chạy: kiểm tra nhánh/status; `php export.php`, `node diagnose.mjs --all`, `node compare-load.mjs`, `node build-package.mjs`, launcher import UAT, `node portable-demo/verify.mjs`, `node preserve.mjs`, `php export.php --verify`, `python metadata-check.py`, `node check-package.mjs`; PowerShell Compress/Expand-Archive và hash; `php -l` cho toàn bộ PHP mới, `node --check verify.mjs`, `git diff --check`. Chi tiết HTTP cuối nằm trong `restore-http-results.json`. Kiểm tra checksum catalog giả bị sửa xác nhận importer dừng trước tạo runtime/database. Lỗi helper khai báo hàm hai lần trong lần chạy đầu đã sửa trước khi import thành công; không thao tác database nguồn để xử lý lỗi đó.

Không chạy build/test frontend lại vì không sửa source frontend; không chạy toàn bộ PHPUnit, test Admin/Customer/Seller upload có xác thực hoặc full checkout. Test guest upload 401 nằm trong verifier, không tạo upload thật. Không coi các phần chưa chạy là đạt. Sau khi kết thúc, database/runtime UAT được giữ để PO kiểm tra; không tự xóa database hoặc file.
