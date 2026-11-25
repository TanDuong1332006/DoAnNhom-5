# SOFEP — sửa API base, CORS và hiển thị ảnh

Ngày 20/11/2026, Asia/Saigon. Nhánh `nhanh-cua-duong`. Đã kiểm tra branch/status trước khi sửa, giữ các thay đổi có sẵn. Không commit/push/deploy, sửa Jira/Proposal, truy cập N.Cảnh Barber hoặc thay database/storage chính. Không sửa `.env` hiện tại của PO, dependency/lockfile hoặc hợp đồng endpoint/payload/xác thực.

## Kết luận nghiệm thu

**PASS bản sửa trong UAT: frontend kết nối backend mới và xử lý placeholder đúng chính sách PO.** Không đánh đồng với việc đã có ảnh chụp đúng SKU hoặc đã đưa thay đổi lên GitHub. Source đang ở working tree; clone `HEAD` chưa tự có các thay đổi này.

| Hạng mục | Kết quả | Bằng chứng |
| --- | --- | --- |
| API base dùng chung | PASS | `VITE_API_URL`; 29 caller không còn URL API origin cứng; chỉ giữ một default `8000` |
| Mặc định PO 5173/8000 | PASS cấu hình/test | Default API và danh sách CORS cũ giữ nguyên; không chạy thao tác ghi trên môi trường PO |
| FE 5194 → BE 8042 | PASS | Browser tải danh mục và 168 sản phẩm, mở chi tiết, chọn màu/size, thêm giỏ và mở checkout yêu cầu đăng nhập; CSP UAT chặn port 8000 |
| CORS origin được phép/bị từ chối | PASS | UAT nhận 5194 và 5173; origin ngoài allowlist không được cấp quyền CORS |
| Ảnh raw trong ZIP qua Laravel | PASS | 167/167 HTTP 200 và SHA-256 đúng trong UAT harness |
| Gallery/ảnh biến thể/placeholder | PASS | API gallery + relation fixture 510 → ảnh 436; frontend không dùng ảnh AI chưa xác minh; ID 172 dùng placeholder |
| Admin/Customer/Seller | PASS các case đã chạy | Login Admin/Customer thành công; Admin đọc sản phẩm được; Customer/Seller bị từ chối Admin/upload; Seller cũ bị từ chối catalog/checkout |
| Giỏ/checkout API | PASS | Quote giỏ và một đơn COD tổng hợp 201 trong database UAT; không gọi thanh toán ngoài |
| Laravel Artisan thông thường | PASS | Thêm 7 kiểm tra: catalog, chi tiết, ảnh JPEG, placeholder, hai 404 và login Customer trên 8042, database/storage UAT riêng |
| FE tests | PASS | **76/76**, không skip |
| BE tests | PASS có 2 skip | **90 passed, 2 skipped**, tổng 92; SQLite `:memory:`, 1381 assertion |
| FE build | PASS | Vite build sau thay đổi cuối đạt; xem `build.txt` |
| Offline thực tế | CHƯA KIỂM THỬ | Browser API không có điều khiển network-offline; không ngắt mạng hệ điều hành |
| Máy vật lý khác/cài dependency mới | CHƯA KIỂM THỬ | Tái sử dụng dependency khớp lockfile, UAT cùng máy |

## Nguyên nhân và cách sửa

Code cũ có interceptor chuyển một số URL cũ sang env, nhưng caller vẫn giữ literal và chỉ một hostname được hỗ trợ; Axios instance riêng/ảnh không đi qua cùng interceptor. Login dùng trực tiếp `import.meta.env.VITE_API_URL` nên không có default thống nhất. Source sạch ở đợt trước chưa có bản chặn illustration hiện trong working tree; không thể coi clone GitHub đã giống bản đang sửa.

Thêm `Fe/src/services/apiConfig.js`: đọc/chuẩn hóa API base duy nhất, default `http://127.0.0.1:8000/api`; bỏ slash cuối; từ chối protocol không HTTP(S), credential, query hoặc fragment trong base. `apiUrl()` tạo URL endpoint giữ nguyên method/path/query của caller. Chuyển 29 caller Client/Admin/login/đăng ký/reset/guard/header/history/engagement sang helper. Axios mặc định và `axiosClient` riêng dùng cùng cấu hình; không đổi khóa token hoặc tách phiên theo vai trò. `resolveApiUrl` chỉ còn lớp tương thích cho URL tích hợp cũ, hỗ trợ cả hai hostname local. Không có caller Fetch API riêng trong source ứng dụng được rà; không can thiệp mã vendor trong assets.

`backendAssetUrl()` đưa đường dẫn upload tương đối và URL upload nội bộ cũ port 8000 qua origin backend mới, giữ path/query/hash. Không đổi URL website khác hoặc dữ liệu URL trong database/giỏ/snapshot. `productImageUrl()` dùng cùng base; ảnh main/gallery/ảnh theo màu dùng chung bộ lọc. Ảnh giỏ và ảnh dòng đơn Admin dùng `ProductImage`, cũng như các component sản phẩm cũ; giữ kích thước thumbnail khi dùng width/height. Thumbnail/ảnh liên quan lỗi dùng fallback; lỗi tải ảnh trong zoom đóng zoom và đưa trang chi tiết về trạng thái placeholder. Không sửa snapshot đơn.

`themmoik27be/config/cors.php` đọc `CORS_ALLOWED_ORIGINS`, phân tách comma, trim/deduplicate. Origin phải là HTTP(S) cụ thể, không path/query/fragment/credential/wildcard. Giữ default `localhost:5173`, `127.0.0.1:5173`, `localhost:3000`; không thêm wildcard origin. UAT chỉ khai báo thêm `127.0.0.1:5194` trong môi trường tiến trình riêng. Headers/methods/xác thực không đổi.

## Vì sao vẫn có 168 placeholder

| Nhóm catalog demo | Số sản phẩm | Quyết định |
| --- | ---: | --- |
| Main là ảnh `illustration-UUID`, provenance AI | 167 | Chưa chứng minh đúng SKU: placeholder, giữ nhãn chờ PO và URL gốc |
| ID 172 | 1 | Main trong demo đã là placeholder; không có ảnh gallery được phép phân phối |
| Có ảnh đúng SKU/quyền dùng được chứng minh trong dữ liệu hiện tại | 0 | Không tự phê duyệt hoặc gán lại ảnh |
| Main lỗi kết nối trong 167 file đã thử HTTP | 0 | File vẫn tải 200/hash đúng; placeholder không phải do mất kết nối |

Nguyên nhân 167 ảnh AI là **bộ lọc theo nguồn minh họa và tình trạng chưa xác minh SKU**, không phải 167 HTTP lỗi. Quyền dùng để demo file không chứng minh đúng sản phẩm thực tế. 19 ảnh cũ có hồ sơ Pexels vẫn HOLD như đợt audit trước; 87 Unsplash chưa xác minh SKU tiếp tục bị lọc. Không tự đưa nhóm HOLD vào gói.

Hai nguồn cũ cụ thể của ID 172 đã được audit giữ lại chờ PO: URL `thoitrangbigsize.vn/.../den-3.jpg` và upload `dd7b32f3-7ee8-4f6e-b7f0-e256ee801dc9.jpg`. Thêm danh sách HOLD theo **URL/path cụ thể**, không chặn cả product ID. Vì vậy hai nguồn này cũng trả placeholder khi xuất hiện từ dữ liệu chính/giỏ cũ; ảnh upload mới đúng SKU của cùng ID 172 không bị chặn theo ID. Không xóa hoặc thay URL gốc. Đợt trước resolver cho URL ngoài của ID 172 đi qua dù chưa đủ bằng chứng; nay thống nhất theo trạng thái HOLD đã có bằng chứng trong báo cáo.

Test có nhánh ảnh không bị HOLD trả URL backend mới bình thường, ảnh ngoài được giữ nguyên, URL không an toàn bị chặn, lỗi ảnh dùng placeholder và thay src thử lại. Điều đó là bằng chứng kỹ thuật cho logic hiển thị, không phải phê duyệt bất kỳ ảnh business nào. Không đổi tên file illustration để vượt bộ lọc.

## UAT, dữ liệu và hai chế độ server

Dùng lại database ZIP **`sofep_image_restore_20261120_7dab8307016c`**, media đã khôi phục từ ZIP được kiểm định trước. Snapshot backend mới nằm trong TEMP, chỉ copy app/bootstrap/config/routes/migration/dependency cần thiết; không lấy `.env`, storage chính, khách hàng hoặc đơn thật. Chạy thêm hai migration hiện có của working tree vào **UAT riêng** để schema đồng bộ với backend đang kiểm tra (26 migration); không tạo/sửa migration source. Tạo 3 tài khoản tổng hợp Admin/Customer/Seller cũ, credential/token chỉ trong file private TEMP, không vào source/báo cáo/gói.

Đầu tiên dùng Laravel kernel/harness của ZIP tại 8042 với runtime storage riêng; `uat-results.json` ghi **191 kiểm tra HTTP PASS**, gồm đủ 167 ảnh/hash, CORS, role, quote và một đơn COD fixture. Nguồn ảnh/main vẫn giữ nguyên trong UAT; chỉ dữ liệu đơn/kho UAT thay đổi do phép thử COD.

Frontend chạy code mới tại **5194**, env API **8042**. CSP chỉ ở cấu hình Vite UAT cho phép API/ảnh nội bộ 8042, chặn port 8000 và URL ngoài. Browser đã quan sát danh mục, “Xem tất cả (168)”, chi tiết sản phẩm ID 1, chọn Đen/M, thêm giỏ có ảnh placeholder và checkout yêu cầu đăng nhập. Đây là bằng chứng frontend không cần backend PO 8000 cho luồng được thử. Không coi CSP là kiểm thử offline thực tế; hero/logo/CDN bên ngoài có thể bị chặn trong UAT và không thuộc ảnh sản phẩm đã đóng gói.

Sau đó kiểm tra thêm `php artisan serve --host=127.0.0.1 --port=8042 --no-reload` trên snapshot UAT. Phát hiện cấu hình storage runtime của harness không tự chuyển thành cấu hình Artisan chuẩn; đã kiểm chứng quy trình **copy media từ gói vào `storage/app/public` của clone UAT riêng**, giữ nguyên file nguồn. `normal-server-isolation.json` xác nhận đúng database UAT và storage trong clone TEMP, khác storage chính; `normal-server-results.json` có **7 kiểm tra PASS**. File `.env` thử nghiệm chỉ được tạo trong snapshot TEMP của tác vụ, không sửa `.env` PO. Hướng dẫn thành viên dùng `.env` clone riêng và storage chuẩn, tránh phụ thuộc cơ chế env/process của server khác nhau.

Gói ZIP gốc không thay đổi, không thêm 20 ảnh HOLD, account hoặc đơn UAT vào ZIP. Source sửa chưa commit/push; nhóm cần chuyển đúng bản đã duyệt tới thành viên trước khi gọi đó là bản clone đã có fix. Không xác nhận đã thử trên máy vật lý khác hoặc cài dependency mới.

## Kiểm thử và bảo toàn

- `node --test Fe/tests/*.test.mjs`: 76 pass, 0 fail/skip. Các harness VM được cập nhật để inject helper API thật; giữ assert nghiệp vụ/URL cấu hình. Thêm test base/URL cũ/URL ngoài/HOLD ID 172/default/scan caller/kích thước ảnh và fallback.
- `php artisan test`: 90 pass, 2 skip, 1381 assertion. Đã ép DB SQLite `:memory:`, mail array, cache/session array và log null trong tiến trình kiểm thử. Hai skip là xuất fixture browser tùy chọn ở `BrowserUatPreparationTest` và `CheckoutIntegrityTest` do không đặt `SOFEP_UAT_DIR`; không coi chúng là pass. SQLite không thay thế mọi khác biệt MySQL; HTTP UAT MySQL bổ sung các luồng được ghi nhận ở trên.
- `npm run build`: đạt sau bản sửa cuối. `php -l` config CORS/test mới, `node --check` công cụ và `git diff --check` đạt. Chỉ có cảnh báo line-ending của Git ở các file đang làm việc.
- Fingerprint read-only catalog và URL snapshot ảnh chính vẫn bằng baseline; 187 file storage chính không đổi hash/không mất file; **23 URL/41 tham chiếu lịch sử đơn giữ nguyên**. `source-unchanged.json`, `preservation.json` ghi bằng chứng. Không có file hết tham chiếu trong phạm vi kiểm kê được đề xuất xóa.

## File đã sửa/tạo và bàn giao

Danh sách đầy đủ **51 đường dẫn** source/test/guide liên quan nằm trong **`files-changed.json`**; phân biệt với các thay đổi khác vốn có trong working tree. Các nhóm chính:

- Thêm `Fe/src/services/apiConfig.js`; sửa `main.js`, `resolveApiUrl.js`, `axiosClient.js`, `productImages.js` và 29 caller được liệt kê trong `component-files.json`.
- Sửa component ảnh dùng chung, giỏ/layout, đơn Admin, thumbnail/ảnh liên quan/zoom chi tiết và các component/page sản phẩm cũ để dùng chính sách ảnh thống nhất.
- Thêm `Fe/tests/api-config.test.mjs`; cập nhật các test VM của login/catalog/checkout/header/product images/reset/dashboard để chạy cùng helper cấu hình; thêm `themmoik27be/tests/Feature/CorsConfigurationTest.php`; sửa config CORS.
- Cập nhật **`docs/image-handoff-2026-11-20/MEMBER_GUIDE.md`**: clone/nhận đúng bản sửa, cấu hình `Fe/.env.local`/Laravel `.env` trên clone, import trước `.env` theo giới hạn ZIP, copy ảnh đúng storage khi dùng Artisan, chạy backend/frontend port mới và kiểm tra ảnh/quyền.
- Thêm thư mục báo cáo này: công cụ snapshot/launch/probe/check chỉ cho UAT, cấu hình Vite UAT, kết quả tests/build/HTTP, danh sách file, bảo toàn và báo cáo. Không xóa file source nào.

**Có thể chốt phần sửa cấu hình API và chính sách ảnh trên UAT đã kiểm tra.** Vẫn còn công việc nội dung: PO cung cấp/duyệt ảnh đúng SKU để giảm 168 placeholder, và nghiệm thu máy khác/offline thực tế nếu đó là điều kiện phát hành. Không phát hành hoặc đẩy thay đổi trong tác vụ này.
