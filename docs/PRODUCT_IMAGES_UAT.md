# Quản lý ảnh SOFEP — bàn giao và UAT Windows

## Cơ chế
- `san_phams.hinh_anh`: URL ảnh chính. `anh_san_phams`: gallery thuộc sản phẩm qua `id_san_pham`, thứ tự `thu_tu`.
- Chưa có cột liên kết ảnh với biến thể. Ảnh đang thuộc cấp sản phẩm; đổi màu không giả lập ảnh riêng của màu.
- File upload nằm trong `themmoik27be/storage/app/public/products/{productId}/{uuid}.{ext}`.
- URL trả về: `{API origin}/api/product-images/{productId}/{filename}`. Route chỉ phục vụ JPG/PNG/WebP đã lưu, có `nosniff`; không cần symlink `public/storage` cho upload mới.
- Nếu dùng URL `/storage/...` truyền thống, cần symlink `public/storage` trỏ đúng `storage/app/public`. Tác vụ này không tự tạo link hoặc sửa cấu hình.
- Chỉ Admin được upload/chọn ảnh chính/sắp xếp/gỡ ảnh. Gỡ liên kết không xóa file, để giữ ảnh trong lịch sử đơn hàng. Chưa có công cụ dọn file không còn tham chiếu.

## Admin thao tác
1. Mở `/admin/san-pham`, đăng nhập tài khoản Admin hiện có.
2. Tạo sản phẩm, điền tên/SKU/danh mục/giá/mô tả ngắn/chi tiết; lưu ở trạng thái ẩn. Không bắt buộc nhập URL ảnh.
3. Ở dòng sản phẩm đã lưu, bấm **Ảnh**. Chọn ảnh đúng SKU từ máy tính: JPG, PNG, WebP; tối đa 2 MB/ảnh, 6000 × 6000 px, 3 ảnh/lần và 20 ảnh/gallery.
4. Kiểm tra preview rồi bấm **Tải ảnh đã chọn lên**. Ảnh đầu tiên trở thành ảnh chính nếu sản phẩm chưa có ảnh chính.
5. Chọn radio **Ảnh chính**, dùng mũi tên đổi thứ tự rồi bấm **Lưu thứ tự và ảnh chính**. Nếu sản phẩm có URL ảnh cũ, chọn ảnh upload mới làm ảnh chính để thay URL đó.
6. **Gỡ ảnh** xóa liên kết khỏi gallery ngay. Nếu gỡ ảnh chính, hệ thống dùng ảnh còn lại đầu tiên; nếu không còn ảnh thì dùng placeholder trên Client.
7. Màu/size/giá chênh lệch/tồn kho quản lý tại `/admin/chi-tiet-san-pham`. Gallery chưa gắn từng biến thể.
8. Sửa mô tả trong **Sửa**; nội dung được hiển thị dạng văn bản, không thực thi HTML.

## Điều kiện UAT
Chỉ tạo/sửa/gỡ dữ liệu sau khi xác nhận kết nối là database UAT dùng một lần hoặc bản sao được phép ghi. MySQL local hiện có không mặc nhiên là UAT. Không chạy migrate/import/reset để chuẩn bị nếu chưa được duyệt riêng.

## Checklist UAT Windows chưa thay thế bởi build
- Tạo sản phẩm UAT mặc định ẩn; xác nhận Client không truy cập được.
- Upload ít nhất hai ảnh đúng SKU; preview và URL ảnh tải được; chọn ảnh thứ hai làm ảnh chính, đổi thứ tự, mở lại modal kiểm tra đã lưu.
- Điền mô tả riêng và thêm hai biến thể màu/size/giá chênh lệch/tồn kho.
- Bật bán, bật nổi bật nếu muốn kiểm tra ở trang chủ. Mở tìm kiếm, Quick View và `/san-pham/{slug}`: ảnh chính/tên/mô tả/giá phải trùng Admin.
- Trang chi tiết: thumbnail, đổi ảnh, zoom và đóng zoom; giá/size/tồn kho vẫn đúng khi đổi biến thể.
- Gỡ ảnh chính rồi tải lại Client; gỡ hết ảnh thì hiện placeholder, không hiện ảnh SKU khác.
- Thử ảnh lỗi URL, file PHP/SVG/giả JPG, ảnh quá dung lượng/kích thước; phải từ chối hoặc hiện fallback rõ ràng.
- Customer/Seller/guest không upload, sắp xếp hoặc gỡ ảnh được.
- Không xác nhận đơn hàng hoặc dùng thanh toán thật trong UAT ảnh.

## Kiểm thử tự động
- Frontend: trong `Fe`, chạy `node --test tests/*.test.mjs`, `npm run build`.
- Backend: trong `themmoik27be`, chạy `php -d extension=pdo_sqlite vendor/bin/phpunit --no-progress` nếu extension chưa bật. PHPUnit dùng SQLite `:memory:` và test upload dùng `Storage::fake('public')`, không dùng MySQL/storage thật.
- Fixture ảnh tự động là PNG thử nghiệm, không phải ảnh kinh doanh và không được gán cho catalog thật.
- Kết quả SQLite không chứng minh tích hợp MySQL/Laragon hoặc thao tác browser upload đã đạt.

## Giới hạn
- PHP CLI local hiện `upload_max_filesize=2M`, `post_max_size=8M`; source giới hạn 2 MB/ảnh và 3 ảnh/lần để phù hợp. Cấu hình PHP của Apache/Nginx có thể khác; không tự sửa cấu hình thật.
- Ảnh stock từ Unsplash chưa xác minh SKU được thay bằng placeholder trên các thẻ sản phẩm và Quick View. Không sửa URL trong database.
- Hỗ trợ preview raster; không xử lý HEIC/SVG/GIF, crop, nén hoặc tự tạo ảnh theo màu.
- Chưa có metadata thông số kỹ thuật riêng ngoài các trường mô tả/biến thể hiện có; cần đề xuất schema riêng nếu muốn bổ sung.
## File thuộc lần triển khai này
- Backend: `app/Http/Controllers/Admin/ProductImageController.php` (mới), `routes/api.php` (route ảnh), `tests/Feature/ProductImagesTest.php` (mới), `tests/Feature/ApiAccessBoundaryTest.php` (kiểm tra route có tham số).
- Frontend: `src/components/Admin/SanPham/index.vue`, `src/components/Admin/SanPham/ProductImages.vue` (mới), `src/components/shared/ProductImage.vue` (mới), `src/services/productImages.js` (mới), `src/services/productDetail.js`, `src/components/Client/ChiTietSanPham.vue`, `src/components/Client/TrangChu.vue`, `src/components/Client/TimKiem.vue`, `public/product-placeholder.svg` (mới), `tests/product-images.test.mjs` (mới), `tests/catalog-session.test.mjs`.
- Tài liệu: `docs/PRODUCT_IMAGES_UAT.md`.
- Không sửa model/schema/migration, cấu hình môi trường, giá, tồn kho, variant ID, cartStore hoặc checkout trong lần triển khai này.
