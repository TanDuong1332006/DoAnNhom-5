# Lỗi và khoảng trống nghiệm thu — 21/11/2026

Phạm vi: snapshot working tree hiện tại và bản giải nén ZIP v2, Laravel/MySQL UAT 8045, frontend 5198. Chỉ audit; chưa áp dụng bản vá. Không có phát hiện Critical đủ bằng chứng trong phạm vi đã kiểm tra. Điều này không chứng nhận toàn hệ thống không có lỗi bảo mật.

## F01 — High — Xóa danh mục đi vòng qua bảo vệ sản phẩm có lịch sử đơn

**Bằng chứng xác nhận từ code và schema MySQL thực tế; chưa chạy thao tác xóa.**

- `themmoik27be/app/Http/Controllers/Admin/DanhMucController.php:79`, đặc biệt dòng 83: chỉ validate ID danh mục rồi `DanhMuc::where(...)->delete()`, không kiểm sản phẩm/lịch sử.
- `themmoik27be/database/migrations/2026_09_23_000005_create_san_phams_table.php:13`: FK sản phẩm → danh mục có `ON DELETE CASCADE`.
- `schema-proof.json`: MySQL UAT thực sự có CASCADE danh mục → sản phẩm → biến thể/gallery; FK dòng đơn → sản phẩm/biến thể là SET NULL.
- `themmoik27be/app/Http/Controllers/Admin/SanPhamController.php:114`: xóa trực tiếp sản phẩm có lịch sử bị chặn 409. Cascade từ danh mục không gọi controller này.
- `Fe/src/components/Admin/DanhMuc/index.vue:158` có cảnh báo xóa toàn bộ sản phẩm, nhưng không có bảo vệ lịch sử ở backend. Route chỉ dành Admin; đây là lỗi bảo toàn dữ liệu khi Admin thao tác hợp lệ, không phải Customer vượt quyền.

**Kịch bản:** danh mục có sản phẩm đang được một đơn chưa hoàn thành tham chiếu. Admin xác nhận xóa danh mục. MySQL xóa sản phẩm, biến thể, gallery và đặt liên kết dòng đơn về NULL; các cột snapshot đơn không bị xóa theo, nhưng mất liên kết catalog. Khi hủy đơn giữ kho, `OrderLifecycle.php` tìm sản phẩm/biến thể bằng `firstOrFail()` nên không thể hoàn tồn cho tài nguyên đã bị cascade xóa. File ảnh không được controller này xóa, có thể còn file không còn catalog tham chiếu.

**Đề xuất ưu tiên:** chặn xóa danh mục đang có sản phẩm, trả 409 và hướng dẫn tạm ẩn; dùng transaction/khóa phù hợp để tránh tạo sản phẩm đồng thời. Có thể sửa tối thiểu controller và thông báo FE, không cần đổi schema ở bước đầu. Nếu muốn đổi FK thì phải trình PO duyệt database riêng.

**Kiểm thử sau khi được duyệt:** fixture danh mục rỗng được xóa; danh mục có sản phẩm chưa có đơn và đã có đơn đều được giữ; đơn chưa thanh toán vẫn hủy/hoàn tồn được; Customer/Guest không gọi được; thử hai request tạo sản phẩm/xóa danh mục đồng thời. Chạy trên UAT mới, không xóa danh mục trên PO. Audit này chỉ đọc schema, chưa thực thi kịch bản phá hủy.

## F02 — Medium — Checklist Customer đánh giá sản phẩm chưa có luồng thực hiện

**Xác nhận bằng code và browser.** Chi tiết sản phẩm 1 và 172 chỉ có liên kết “Góp ý riêng cho SOFEP”; không có form chấm sao/gửi đánh giá và tab đánh giá. `Fe/src/components/Client/ChiTietSanPham.vue:25`, computed `tabs()` dòng 85 chỉ có mô tả/thông số/size. Dữ liệu `reviews/rating` còn được đọc ở dòng 108 nhưng không tạo chức năng gửi đánh giá.

`themmoik27be/routes/api.php:147` chỉ khai báo Admin đọc/đổi trạng thái/xóa đánh giá. Rà routes không có endpoint Customer tạo đánh giá sản phẩm. Góp ý riêng `/gop-y` là một chức năng khác, không thay thế đánh giá theo sản phẩm.

**Tác động:** không thể nghiệm thu mục Customer đánh giá theo checklist hiện tại. Nếu PO đã chủ ý bỏ đánh giá công khai thì cần chốt lại phạm vi nghiệm thu; không tự phục hồi tính năng cũ.

**Đề xuất:** PO chọn xác nhận ngoài phạm vi hoặc duyệt luồng đánh giá theo đơn đã mua, chống trùng, validation, kiểm quyền sở hữu và moderation. Nếu triển khai mới sẽ cần duyệt hợp đồng API và có thể database riêng. Sau đó test Customer đúng đơn, Customer khác, Guest, Admin, đánh giá trùng và nội dung không hợp lệ. Audit chưa sửa chức năng.

## F03 — Low — ID 172 đếm placeholder là ảnh sản phẩm

**Tái hiện browser:** `/san-pham/172` hiển thị đúng SVG placeholder local, nhưng caption “Áo polo 1 ảnh sản phẩm” và nút “Phóng to ảnh sản phẩm” vẫn xuất hiện. Xem `placeholder-172.png` và `placeholder-172.json`; ảnh chính và thumbnail đều có URL frontend `/product-placeholder.svg`, tải thành công.

`Fe/src/components/Client/ChiTietSanPham.vue:13` bật zoom khi có `activeImage`; dòng 19 dùng `images.length` làm số ảnh. Bộ gallery chấp nhận đường dẫn placeholder nên bị đếm như ảnh thật trong gallery. Đây là lỗi mô tả trạng thái, không phải gán nhầm ảnh cho ID 172 và không làm mất fallback.

**Đề xuất:** phân biệt placeholder với ảnh catalog để không tính vào số ảnh/zoom, vẫn giữ SVG và cơ chế lỗi ảnh. Không đổi dữ liệu nguồn, HOLD hoặc provenance. Test ID 172, gallery thật 1/nhiều ảnh, fallback khi ảnh 404 và reset khi đổi sản phẩm.

## Giới hạn kiểm thử cần đóng trước nghiệm thu đầy đủ

- OTP: UAT dùng `MAIL_MAILER=array`, đăng ký API 201 tạo tài khoản chưa kích hoạt và đăng nhập bị từ chối 403; không có email thật để lấy OTP. Test tự động có kiểm OTP đúng/sai/hết hạn, nhưng browser chưa hoàn tất nhận email → nhập OTP → đăng nhập. Đây là giới hạn cấu hình demo đã nêu trong hướng dẫn, không suy diễn thành lỗi SMTP của môi trường chính.
- CRUD đầy đủ bằng browser, mọi vai trò/trạng thái và mọi tổ hợp biến thể chưa được chạy hết. Xem ma trận trong REPORT.md; không đổi “NOT TESTED” thành PASS từ kết quả build.
- Chưa ngắt Internet thực tế. `Fe/index.html:9` đến 21 còn tải Google Fonts và Bootstrap CSS/icons/JS từ CDN; trang chủ/menu có 9 ảnh Unsplash; checkout có logo từ Wikimedia. Local 167 ảnh không chứng minh toàn website offline.
- Chưa chạy trên máy vật lý/OS khác. Lượt cài sạch từ ZIP v2 ở task trước diễn ra trên thư mục/database/storage độc lập cùng máy; lượt này kiểm lại archive, checksum, runtime và tests, không cài lại toàn bộ toolchain.

**Thứ tự:** F01 → quyết định phạm vi/hoàn tất F02 và OTP → F03 → vòng UAT hồi quy, đóng ZIP mới nếu có bản vá được duyệt. Không tự sửa ZIP v2 để làm thay đổi kết quả audit.
