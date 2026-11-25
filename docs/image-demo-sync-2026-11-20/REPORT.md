# Đồng bộ ảnh minh họa và demo SOFEP — 20/11/2026

## Kết luận và phạm vi

Áp dụng quyết định mới của PO: **168 sản phẩm là catalog giả lập**, không yêu cầu ảnh thật đúng SKU hoặc 8 đợt phê duyệt ảnh thật. Các tài liệu duyệt ảnh thật trước đó giữ nguyên làm lịch sử, không còn là điều kiện mở ảnh minh họa demo.

Bản vá frontend chỉ bật ảnh minh họa khi `VITE_DEMO_ILLUSTRATIONS=true`. UAT dùng FE **5195**, BE **8043**, MySQL **`sofep_image_restore_20261120_6675ee602cd4`**, Storage riêng trong TEMP. Mặc định môi trường PO không bật cờ này vẫn giữ hành vi hiện tại; không sửa `.env`, database hoặc Storage chính.

**167 ảnh chính minh họa trong bộ demo tải được; 1 placeholder là ID 172.** Đây là ảnh minh họa theo catalog giả lập, không phải chứng nhận ảnh thật/SKU/màu. ID 172 chưa có ảnh thay thế trong ZIP và hai nguồn cũ chưa rõ quyền phân phối, nên tiếp tục HOLD; không tự nối 9 biến thể gốc của nó vào ảnh khác.

## Nguyên nhân và bản sửa

`Fe/src/services/productImages.js` trước đó loại mọi filename `illustration-<UUID>.*`; do đó 167 file còn tồn tại vẫn bị resolver đổi thành placeholder, cộng ID 172 thành 168 placeholder. Bản sửa thêm cờ demo, cho phép main/gallery/ảnh biến thể qua cùng resolver khi bật. Vẫn chặn URL không an toàn, Unsplash cũ và hai nguồn HOLD ID 172. Thiếu/lỗi tải vẫn dùng placeholder.

`Fe/src/components/shared/ProductImage.vue` hiện ghi **“Ảnh minh họa”**, chỉ hiện nhãn khi resolver thực sự trả ảnh và chưa lỗi. Không đổi tên file hoặc URL để qua bộ lọc, không khẳng định ảnh thật. Component chi tiết vốn đã có nhãn minh họa riêng, giữ nguyên.

## Đối chiếu dữ liệu

[COMPARISON.md](COMPARISON.md) có đủ 168 sản phẩm: ID/tên, đường dẫn MySQL = API, trạng thái hiển thị, HTTP và checksum. [comparison.json](comparison.json) thêm URL website resolve theo backend mới, đường dẫn file vật lý, ID gallery và CORS. Các URL MySQL tương đối là dữ liệu Navicat cần thấy trong **database UAT**, không phải thay đổi trên `doan_nhom_5`.

Ví dụ đã quan sát trong browser:

| ID | Danh mục | Kết quả website |
|---|---|---|
| 1 | Áo Nam | Ảnh chính + thumbnail gallery tải qua 8043, nhãn minh họa; chọn màu Tiêu chuẩn của fixture vẫn tải đúng |
| 7 | Giày & Sneaker | Ảnh chính + thumbnail tải qua 8043 |
| 8 | Túi Xách & Ví | Ảnh chính + thumbnail tải qua 8043; đã reload lại trên bản sao FE TEMP độc lập |
| 172 | Áo Nam | Thẻ trang chủ dùng placeholder và tải được |

Ảnh bằng chứng: `browser-shirt.png`, `browser-shoes.png`, `browser-bag.png`, `browser-independent-fe.png`. Browser chỉ lấy mẫu; kiểm tra HTTP/API chạy toàn catalog, không lấy mẫu để suy ra tất cả đã được kiểm tra trực quan.

## Ảnh cũ, trùng và đề xuất dọn

Catalog chính được đối chiếu read-only: **168 sản phẩm, 514 gallery, 500 biến thể**. 275 URL duy nhất gồm 167 AI, 19 Pexels theo hồ sơ lịch sử, 87 Unsplash, một website ngoài và một upload chưa rõ nguồn. Các ảnh stock cũ không tự được đưa lại vào gallery demo chỉ vì PO cho phép minh họa; bộ local 167 ảnh đã có là nguồn demo độc lập.

- Trong cùng sản phẩm, 514 dòng gallery có 355 cặp sản phẩm–URL, tương đương **159 dòng trùng thêm theo URL**. Đây là trùng bản ghi URL, không mặc nhiên là trùng nội dung byte giữa những file khác tên.
- Bộ demo giữ 167 gallery minh họa. **347 dòng nguồn không nằm trong demo**: đây là khác biệt giữa catalog nguồn và catalog demo mới, không phải đã DELETE khỏi nguồn.
- [CLEANUP_CANDIDATES.md](CLEANUP_CANDIDATES.md) liệt kê cụ thể **298 dòng** có thể đề xuất bỏ khỏi catalog demo: không là URL ảnh chính, không có FK ảnh biến thể và không thuộc URL snapshot đơn theo dữ liệu đã đọc.
- **49 dòng còn lại trong nhóm 347 có tham chiếu cần bảo toàn**. Xem [gallery-audit.json](gallery-audit.json): từng ID, URL, tham chiếu main, biến thể, đơn, ID trùng và có/không trong demo. Không đưa chúng vào danh sách xóa thật.
- Không hiển thị không có nghĩa không còn tham chiếu: các dòng gallery vẫn là tham chiếu của database chính. **0 file ảnh mồ côi** trong phạm vi `storage/app/public/products`; không đề xuất xóa file nào. `storage-audit.json` xác nhận **187/187 file giữ nguyên checksum**, không thiếu đường dẫn local được tham chiếu.

**23 URL lịch sử đơn với 41 tham chiếu giữ nguyên**, có danh sách tại [order-preservation.json](order-preservation.json). Không sao chép đơn thật vào demo, không cập nhật ảnh snapshot đơn. Mọi phương án xóa bản ghi/file thật phải có PO/PM duyệt riêng; nhiệm vụ này không chạy DELETE hoặc xóa file.

## Kết quả kiểm thử

| Hạng mục | Kết quả và giới hạn |
|---|---|
| Import ZIP vào MySQL/Storage mới | PASS: 24 migration; 168 sản phẩm, 167 gallery, 501 biến thể gồm 1 fixture synthetic ID 510 |
| Dữ liệu nhạy cảm trong UAT mới | PASS: khách hàng, đơn, dòng đơn, users và token đều 0 |
| Ảnh main/gallery qua Laravel thật | PASS: URL MySQL/API khớp, file/HTTP SHA-256 khớp; xem `http-results.json` và `comparison.json` |
| Ảnh biến thể | PASS fixture ID 510 → ảnh 436 của sản phẩm 1; không giả định đã khôi phục 9 liên kết gốc ID 172 |
| Placeholder/404 | PASS: ID 172 và ảnh thiếu; URL giả trả 404, test component xác nhận fallback/reset khi đổi source |
| FE → BE khác port | PASS browser trên 5195 → 8043, CORS allowlist UAT một origin; CSP chặn port 8000 và nguồn ngoài |
| FE tests | PASS 77/77, `node --test Fe/tests/*.test.mjs` |
| FE build | PASS, `npm run build` trong Fe; build mặc định không tự bật cờ demo |
| BE tests | 90 passed, 2 skipped, 1381 assertions; SQLite memory, không thay thế bằng chứng MySQL UAT |
| PHP tools | `php -l` đạt cho read-uat/router/serve; không sửa PHP ứng dụng |
| Bảo toàn nguồn | Read-only verify catalog/tham chiếu đơn không đổi; 187 checksum Storage không đổi; ZIP nguyên checksum |
| Offline thực tế | CHƯA TEST: không ngắt mạng hệ điều hành. CSP chỉ chứng minh tài nguyên ảnh demo hoạt động khi chặn nguồn ngoài trong browser |
| Clone/cài dependency mới trên máy khác | CHƯA TEST: backend dùng archive HEAD sạch, dependency có sẵn; FE là bản sao working tree đã vá, dùng junction dependency |

Backend UAT boot **Laravel thật qua HTTP kernel** từ source archive sạch, guard MySQL và Storage riêng bằng harness trong ZIP; không phải mock API. Không gọi đây là chạy `artisan serve` chuẩn của lượt này. Frontend đã được copy vào TEMP không có `.env` PO; không dùng Storage/database chính. Kiểm thử vai trò không thay đổi bởi bản vá hiển thị; BE suite giữ các kiểm tra boundary Admin/Customer/Seller. Không tạo tài khoản hoặc thao tác upload/checkout trong UAT ảnh mới này.

Ảnh sản phẩm demo hiện không phụ thuộc Internet. 87 URL Unsplash, 19 file Pexels và nguồn ID 172 vẫn giữ ở nguồn, không phân phối thêm trong ZIP. Tài nguyên ngoài khác của giao diện không nằm trong chứng nhận offline toàn ứng dụng.

## Bàn giao và file thay đổi

[MEMBER_GUIDE.md](MEMBER_GUIDE.md) hướng dẫn clone → cấu hình MySQL riêng → import ZIP → bật cờ demo/API base → chạy và kiểm tra. ZIP gốc giữ SHA-256 `972561984c18aed8a24928ab9fad6364def20198b457d845033ab38559b6a7b4`; không thêm 20 file HOLD.

**Đạt khôi phục ảnh minh họa trong UAT; chưa xác nhận “clone GitHub HEAD là chạy đủ bản vá”.** Các bản vá API/config và bản vá nhiệm vụ này còn trong working tree, chưa commit/push theo giới hạn PO. Thành viên phải nhận phiên bản source có các bản vá đã nghiệm thu trước; chưa tuyên bố đã test trên máy khác hoặc offline thực tế.

Source sửa đúng 3 file: `Fe/src/services/productImages.js` (cờ demo), `Fe/src/components/shared/ProductImage.vue` (nhãn), `Fe/tests/product-images.test.mjs` (test chế độ bật/tắt và HOLD). Giữ nguyên thay đổi có sẵn trong repository. Không sửa source backend/database/schema chính.

File mới nằm riêng trong `docs/image-demo-sync-2026-11-20/`: báo cáo, hướng dẫn, bảng đối chiếu/dọn, launcher/config UAT, công cụ đọc/verify và JSON/log/ảnh bằng chứng. `uat.json`/`inputs.json` ghi đường TEMP của lượt thử; không phải gói cấu hình portable hoặc chứa mật khẩu. Không commit/push/deploy, không chỉnh Jira/Proposal, không tác động N.Cảnh Barber.
