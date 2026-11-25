# Các phương án cần PO/PM duyệt trước bản chính thức

Không áp dụng thay đổi source trong lượt này. Không tích hợp Figma hoặc đổi bố cục SOFEP. Đây là tài liệu kỹ thuật trong báo cáo, không sửa tài liệu Proposal/Jira.

## A. Bắt buộc trước nộp: chặn xóa danh mục còn sản phẩm

Nguyên nhân và bằng chứng High vẫn như F01 trong `../final-audit-2026-11-21/FINDINGS.md`: xóa danh mục cascade đi vòng qua kiểm tra lịch sử của controller sản phẩm. Không thực thi xóa trên PO/UAT để làm mất catalog.

**Bản vá tối thiểu đề nghị:** giữ endpoint/method/request hiện có; danh mục rỗng vẫn xóa như cũ, danh mục có ít nhất một sản phẩm bị từ chối HTTP 409 với `status: 0`, message “Danh mục còn sản phẩm. Hãy tạm ẩn thay vì xóa.” Kiểm tra trong transaction, khóa hàng danh mục trước khi kiểm sản phẩm. FK hiện có buộc insert sản phẩm đồng thời chờ khóa parent, giúp tránh race; cần test MySQL riêng sau khi duyệt. Không đổi migration/schema/dữ liệu, không tự tạm ẩn hàng loạt.

Các file dự kiến:

| File | Thay đổi |
|---|---|
| `themmoik27be/app/Http/Controllers/Admin/DanhMucController.php` | Transaction + khóa parent + kiểm sản phẩm; trả 409 khi bị chặn |
| `Fe/src/components/Admin/DanhMuc/index.vue` | Hiển thị message backend khi bị chặn; sửa cảnh báo xóa để không hướng dẫn cascade; giữ bố cục |
| `themmoik27be/tests/Feature/CategoryDeletionSafetyTest.php` (mới) | Regression SQLite memory có schema FK thật: rỗng xóa được; có sản phẩm/historical order giữ nguyên; Guest/Customer/Seller bị từ chối |

Sau vá: php-lint, FE tests/build, BE suite, MySQL UAT riêng kiểm danh mục rỗng/có sản phẩm, tranh chấp tạo sản phẩm và xóa danh mục; hủy đơn tổng hợp vẫn hoàn tồn. Không chạy xóa danh mục có dữ liệu trên main.

**Cần duyệt riêng phản hồi mới 409:** AGENTS.md không cho tự đổi hợp đồng API. Skill `sofep-bug-fixer` mục 6 quy định: “Xin phép và chờ người dùng đồng ý với bản vá cùng danh sách file trước khi chỉnh sửa.” Việc ưu tiên sửa lỗi chưa thay thế phê duyệt phản hồi API cụ thể này.

## B. Đề nghị bỏ màn hình quản lý đánh giá cũ khỏi bản nộp

Customer hiện không gửi được đánh giá sản phẩm; main có 0 dòng, demo cũng 0. Không cần mở rộng sang xây chức năng chấm sao mới để nộp đồ án. Đề nghị PO xác nhận đây là chức năng cũ ngoài phạm vi, thay vì giữ trang “Đánh giá & Phản hồi” trống dễ gây nhầm với góp ý nền tảng.

**Sau khi được duyệt mới thực hiện:**

| File | Thay đổi dự kiến |
|---|---|
| `Fe/src/layout/components/MenuRocker.vue` | Bỏ mục “Đánh giá lịch sử” |
| `Fe/src/router/index.js` | Bỏ route `/admin/danh-gia` |
| `Fe/src/components/Admin/DanhGia/index.vue` | Bỏ component sau khi đã gỡ caller |
| `themmoik27be/routes/api.php` | Bỏ import controller và 3 route Admin get-data/change-status/destroy đánh giá |
| `themmoik27be/app/Http/Controllers/Admin/DanhGiaController.php` | Bỏ controller Admin không còn caller |
| `Fe/tests/legacy-review-retirement.test.mjs` (mới) | Chặn menu/router cũ xuất hiện lại, giữ link góp ý |
| `themmoik27be/tests/Feature/LegacyReviewRetirementTest.php` (mới) | Route cũ không hoạt động; feedback/reply/inbox và chi tiết sản phẩm vẫn đúng |

**Giữ nguyên** bảng/migration `danh_gia_san_phams`, `DanhGiaSanPham`, quan hệ `SanPham::danhGias`, phần đọc review/rating của `ProductDetailController`, và các key `reviews/rating` trong response. Chúng còn phụ thuộc API/test; xóa hết sẽ mở rộng phạm vi và có thể đổi hợp đồng chi tiết. Không sửa seeder/database để tạo dữ liệu lấp trang trống.

**Bảo toàn:** route `/gop-y`, `/admin/gop-y`, `/thong-bao`, `/vi-uu-dai`; các component Engagement; `FeedbackController`, `CustomerInboxController`, campaign/voucher services; bảng `private_feedback`, `feedback_attachments`, `customer_notifications` và cơ chế phân quyền. Hàm FE tên cũ `dieuHuongDanhGia()` đang đưa tới `/gop-y`, cần giữ nguyên hành vi, chưa refactor tên trong bản ổn định.

Đây là việc gỡ route/API/code, cần PO/PM duyệt phạm vi B trước. Không có thao tác nào ở trên đã thực hiện trong lượt audit này.

## C. Low: placeholder ID 172

Giữ placeholder hiện tại; lỗi caption “1 ảnh sản phẩm”/zoom là Low. Đề nghị hoãn nếu thời gian nộp hạn chế; sửa tách trạng thái hiển thị sau khi luồng High đã ổn định. Không đổi nguồn ảnh, HOLD, checksum hay URL.

## D. Đóng gói chính thức

Chưa tạo ZIP v3/final. Sau A và quyết định B: kiểm lại source diff, chạy suite/browser/UAT mới, cập nhật manifest checksum, kiểm dữ liệu riêng tư, rồi trình PO/PM duyệt đóng gói. Không commit/push nếu chưa được yêu cầu riêng.
