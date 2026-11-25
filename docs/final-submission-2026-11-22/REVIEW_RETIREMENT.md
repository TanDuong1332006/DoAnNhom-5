# Rà chức năng đánh giá sản phẩm

Ngày 22/11/2026. **Không có luồng Customer tạo đánh giá sản phẩm trong source đang chạy. Trang Admin trống phù hợp với dữ liệu 0 dòng, không phải API tải danh sách bị lỗi.** Chưa xóa/sửa code, route, API hoặc database.

## Bằng chứng

| Tầng | Kết quả |
|---|---|
| Customer chi tiết | `Fe/src/components/Client/ChiTietSanPham.vue`: chỉ link “Góp ý riêng cho SOFEP”, không có form chấm sao/gửi đánh giá |
| Customer navigation | `ClientLayout.vue::dieuHuongDanhGia()` là tên hàm cũ, hành vi hiện tại đưa tới `/gop-y`; không được xóa nhầm |
| Vue Admin | `/admin/danh-gia` → `components/Admin/DanhGia/index.vue`; menu hiện ghi “Đánh giá lịch sử” |
| API Admin | `GET /api/admin/danh-gia/get-data`, `POST .../change-status`, `POST .../destroy`, middleware auth/admin |
| Controller | `Admin/DanhGiaController` chỉ đọc/đổi trạng thái/xóa `DanhGiaSanPham`; không có create |
| Database chính | Truy vấn `START TRANSACTION READ ONLY`, COUNT bảng `danh_gia_san_phams`: 0 dòng, 0 được duyệt; không xuất nội dung/khách hàng — `review-counts.json` |
| Demo mới | API get-data HTTP 200, `data: []`; browser “Chưa có đánh giá nào.” — `admin-reviews.png`, `feedback-results.json` |
| Phụ thuộc còn dùng | `ProductDetailController` còn đọc bảng, trả `reviews/rating`; `SanPham::danhGias`, model/migration và test `ProductDetailDemoTest` còn liên quan |
| Dữ liệu cũ | `DatabaseSeeder` cũ có tạo đánh giá giả lập; seeder này không được đóng trong v2 và không được chạy để lấp trang trống |

Kết luận “không có luồng tạo” dựa trên router/API/controller/component hiện tại; không khẳng định không bao giờ có dữ liệu nhập tay hoặc từ phiên bản cũ.

## Góp ý nền tảng là luồng độc lập cần bảo toàn

`/gop-y` → `FeedbackController::store` → `private_feedback`, ảnh riêng `feedback_attachments`. Admin `/admin/gop-y` đọc/cập nhật/phản hồi. Phản hồi `in_app` tạo `customer_notifications`; `/thong-bao` dùng `CustomerInboxController`, giới hạn theo Customer đã xác thực. Không lưu vào `danh_gia_san_phams` và không dùng `DanhGiaController`.

Đã kiểm thực tế trên MySQL UAT mới:

1. Customer gửi góp ý website tổng hợp: 201.
2. Admin đọc: 200; Customer gọi endpoint Admin: 403.
3. Admin phản hồi in-app: 200, không gửi mail thật.
4. Customer nhận đúng notification, đánh dấu đọc: 200.
5. Danh sách đánh giá vẫn rỗng; API chi tiết vẫn giữ key `reviews/rating`.
6. Browser Customer nhìn thấy “SOFEP phản hồi góp ý #1” — `browser-feedback-inbox.png`.

15/15 kiểm tra bổ sung đạt trong `feedback-results.json`; đây là API + một bước xác nhận hiển thị thông báo bằng browser, không phải toàn bộ vòng gửi/phản hồi được thao tác qua UI.

## Đề nghị PO/PM

Xác nhận đánh giá sản phẩm cũ ngoài phạm vi đồ án; gỡ menu/trang/3 API quản lý cũ sau duyệt. Giữ bảng/model/migration, quan hệ và key API chi tiết để bảo toàn dữ liệu và tương thích; chưa dọn mọi code đọc review. Không phát triển tính năng đánh giá mới. Danh sách file, phạm vi kiểm thử và điều kiện duyệt ở [DECISIONS.md](DECISIONS.md), phương án B.
