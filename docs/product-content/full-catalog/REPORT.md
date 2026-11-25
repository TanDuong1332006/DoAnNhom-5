# SOFEP — ảnh toàn catalog local demo

Ngày thực hiện: 31/10/2026 (Asia/Saigon). Database đã xác minh: APP_ENV local, MySQL loopback, doan_nhom_5, không có DB_URL chuyển hướng. Không thay schema, cấu hình, tài khoản; không commit/push/deploy.

- Tổng sản phẩm kiểm tra: 169; đang bán: 167; 2 sản phẩm ẩn giữ nguyên.
- Trước: 16/167 ảnh chính HTTP 200, đúng định dạng và được frontend chấp nhận.
- Đã bổ sung/thay ảnh chính: 167 SKU.
- Sau: 167/167 (100.00%).
- SKU còn thiếu ảnh chính hợp lệ: 0.

## Nguồn và ánh xạ

Ảnh mới được tạo riêng bằng built-in image_gen, sau đó xuất JPEG chất lượng 88; không phải ảnh chụp mẫu thật. Mỗi SKU có prompt, product ID, nguồn tạo, SHA-256, dung lượng và URL đã xuất bản trong manifest.json. Không dùng giấy phép stock cho ảnh AI. Giao diện hiện có nhận filename illustration-UUID để hiển thị nhãn ảnh minh họa. Không xác nhận chất liệu, tính năng hoặc màu từng biến thể bằng ảnh. Không tạo thêm gallery giả góc chụp.

before.json và backup-ID.json giữ trạng thái ảnh trước thay đổi. Không xóa file hoặc bản ghi gallery cũ. Ảnh stock Unsplash cũ tiếp tục bị frontend chặn vì chưa xác minh với SKU; không nới bộ lọc để che thiếu dữ liệu.

## Xác minh

- audit.mjs: kiểm tra HTTP và magic bytes tất cả URL được liên kết; after-audit.json ghi chi tiết cả URL lịch sử lỗi/chưa xác minh.
- verify-api.mjs: 167 sản phẩm; 345 yêu cầu API đọc; 0 lỗi; kiểm tra catalog đầy đủ, tìm kiếm từng tên, danh mục, home-data và Product Detail 2.1.
- Tên/SKU/slug/giá/tồn kho/trạng thái/mô tả/biến thể giữ nguyên; liên kết gallery lịch sử giữ nguyên.
- Upload ẩn danh bị từ chối: HTTP 401. Không thay logic phân quyền Admin/Customer/Seller.
- node --test Fe/tests/product-images.test.mjs Fe/tests/product-detail-demo.test.mjs Fe/tests/catalog-session.test.mjs: 21/21 đạt.
- npm run build trong Fe: đạt; còn cảnh báo bundle lớn có sẵn.
- php -l publish.php: đạt. Backend test liên quan: 24 test bị bỏ qua do PHP thiếu PDO SQLite; không coi là đạt và không thay môi trường để chạy.

## Chưa kiểm chứng

Công cụ computer-use tự dừng vì không xác minh được URL Chrome trên Windows; trình duyệt tích hợp không điều hướng được localhost. Chưa có ảnh chụp browser, chưa xác minh trực quan desktop 1440px/mobile 390px hoặc thao tác Quick View trong browser. Không tuyên bố hoàn tất nghiệm thu toàn bộ màn hình.

## SKU còn thiếu

Không còn SKU đang bán thiếu ảnh chính hợp lệ theo kiểm tra dữ liệu và HTTP.

## Chạy lại

prepare-assets.ps1 chuẩn hóa file; chỉ đặt review=approved sau khi duyệt trực quan. publish.php mặc định dry-run; --apply chỉ chấp nhận MySQL local đã xác minh và ảnh được duyệt. Script dùng chính ProductImageController để upload/sắp xếp, nhận diện SHA-256 để không upload trùng. Chạy audit.mjs after và verify-api.mjs sau khi xuất bản.

## Kiểm tra bổ sung

- Quick View API: sản phẩm ID 1; ảnh chính/gallery khớp. Endpoint cũ có hành vi tăng 1 lượt xem; không đổi tên/giá/tồn kho/trạng thái. Chưa kiểm tra thao tác modal trong browser.
- Chạy lại --apply toàn lô: số bản ghi ảnh 512 → 512; không tạo trùng: true.
- Tất cả 167 file xuất bản có SHA-256 khớp nội dung HTTP; ảnh chính mới có SHA-256 riêng biệt theo SKU.

## Tệp được tạo trong công việc này

Chỉ bổ sung thư mục docs/product-content/full-catalog và file ảnh public qua controller có sẵn; không sửa source ứng dụng hoặc thay đổi các file đang có thay đổi của người dùng.

- audit.mjs, verify-api.mjs: kiểm kê URL và xác minh API/dữ liệu.
- publish.php: công cụ xuất bản local, dry-run/apply và nhận diện upload trùng.
- prepare-assets.ps1, contact-sheet.ps1: JPEG tối ưu và ảnh tổng hợp phục vụ duyệt.
- report.mjs, REPORT.md, manifest.json: tổng hợp kết quả và nguồn/prompt/ánh xạ.
- before.json, after.json, before-audit.json, after-audit.json, api-verification.json, idempotency.json: bằng chứng trước/sau.
- backup-{ID}.json, published-{ID}.json: khả năng khôi phục và nhật ký mỗi SKU.
- assets/{ID}.jpg, assets/{ID}.json, bản PNG nguồn; review-*.jpg: ảnh và bằng chứng duyệt theo ID. PNG bị loại được giữ riêng, không xuất bản.
- themmoik27be/storage/app/public/products/{ID}/illustration-{UUID}.jpg: 167 file mới được lưu bằng cơ chế chính thức.

Gallery cũ được bảo toàn; URL Unsplash lịch sử vẫn bị lọc. Ảnh không tự thay theo màu do schema hiện có chưa ánh xạ ảnh với biến thể.

Lệnh backend đã chạy trong themmoik27be/: `php artisan test --filter='ProductImagesTest|ProductDetailDemoTest|ProductCatalogPhaseTwoTest|ApiAccessBoundaryTest'`. Kết quả: 24 skipped, 0 assertions. Kiểm tra cú pháp: `php -l docs/product-content/full-catalog/publish.php` từ gốc repository.
