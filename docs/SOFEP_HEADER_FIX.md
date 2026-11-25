# SOFEP — Account menu, logout và Customer Search

Ngày kiểm tra: 03/11/2026. Nhánh: `nhanh-cua-duong`. Giữ nguyên thay đổi có sẵn; không commit/push/deploy.

## Nguyên nhân và bản sửa

- Dropdown dùng `text-truncate` cho tên và chiều rộng cố định: bỏ cắt tên/email trong phần thông tin, cho xuống dòng, giới hạn chiều rộng/chiều cao theo viewport. Tên trên nút Header vẫn gọn và có title/accessible name đầy đủ.
- Nút logout có đồng thời màu chữ đỏ và nền đỏ: dùng button thật, chữ trắng trên nền #9f1239, SVG rõ ràng, hover/focus và trạng thái đang xử lý.
- Đăng nhập trước đây sao chép cùng token vào cả khóa Customer và Admin; logout xóa toàn bộ khóa. Tách lưu phiên theo vai trò, chọn bearer theo API, giữ Authorization tường minh; logout/guard chỉ xóa phiên đúng vai trò. Backend logout hiện có đã thu hồi token được gửi nên không cần sửa backend/hợp đồng API.
- Search phụ thuộc icon font và màu theme: thay bằng SVG trắng trên nền tối, CSS chỉ trong Customer Header. Form submit thống nhất Enter và click icon; trim, chặn rỗng/gửi lặp, thông báo lỗi điều hướng. Trang kết quả giữ cơ chế loading, lỗi API/retry và empty state hiện có.
- Thêm Escape, mũi tên/Home/End, trả focus, đóng khi click ngoài hoặc focus ra ngoài menu. Customer Header hiện không có autocomplete API; không bổ sung tính năng mới. Admin autocomplete giữ nguyên.

## File trong bản sửa này

| File | Nội dung |
| --- | --- |
| `Fe/src/layout/wrapper/ClientLayout.vue` | Dropdown, logout Customer, kiểm tra phiên, search form và scoped CSS |
| `Fe/src/services/authSessions.js` (mới) | Lưu/đọc/xóa phiên riêng theo vai trò, tương thích khóa cũ |
| `Fe/src/components/DangNhap/index.vue` | Lưu phiên đúng vai trò |
| `Fe/src/main.js` | Chọn bearer theo API; giữ Authorization đã chỉ định |
| `Fe/src/router/checkLogin.js` | Admin guard không ghi đè/xóa Customer |
| `Fe/src/layout/components/HeaderRocker.vue` | Hiển thị và logout đúng phiên Admin |
| `Fe/tests/customer-header.test.mjs` (mới) | 6 regression tests cho phiên, logout, search, bàn phím và interceptor |
| `Fe/tests/admin-login.test.mjs`, `Fe/tests/catalog-session.test.mjs` | Cập nhật harness theo helper phiên |
| `docs/header-fix-build.log`, `docs/header-fix-screenshots/*` | Bằng chứng build và ảnh QA |

Các thay đổi khác đang có trong working tree thuộc công việc trước, không được hoàn tác.

## Bằng chứng kiểm thử

- `node --test tests/*.test.mjs` trong `Fe`: **38 pass, 0 fail**, gồm hồi quy Product Detail, cart, checkout/voucher, Admin login/search và lỗi/rỗng API catalog.
- `npm run build` trong `Fe`: **đạt**, 4.58s; log kèm theo.
- `git diff --check`: **đạt**; Git có thông báo quy đổi LF/CRLF ở các file có sẵn.
- Trình duyệt in-app hợp lệ, desktop **1440x1000**, mobile **390x844**.
- `http://localhost:5173/`: Enter tìm “Áo Thun” hiện sản phẩm; icon tìm chuỗi không tồn tại hiện “Không Tìm Thấy Tác Phẩm Phù Hợp”. Không sửa giỏ hàng 1 sản phẩm có sẵn.
- Đăng nhập trên **cùng source** qua `http://127.0.0.1:5175/`, API UAT `127.0.0.1:8012`, đã xác minh `/__uat/status` thuộc MySQL cô lập `sofep_uat_20261103_0552eb8e0b6d`.
- Admin đăng nhập → Customer đăng nhập → mở menu thấy tên “Khách demo customer”, email và “Đăng xuất” → logout thấy Header khách → truy cập `/admin` vẫn thấy Admin dashboard → đăng nhập lại Customer và mở menu thành công.
- Escape đóng menu, mũi tên mở và focus mục đầu, click ô search bên ngoài đóng menu. Unit test bổ sung Home/End và trả focus.
- Mobile: menu trái 29.9px, phải 369.9px trong viewport 390px; document scrollWidth 375px, không tràn ngang. Chữ logout thực tế `rgb(255,255,255)`, nền `rgb(159,18,57)`.
- Mobile Enter “Áo Thun” trả **12** sản phẩm; icon với chuỗi không tồn tại hiện empty state. Từ khóa khoảng trắng hiện “Vui lòng nhập từ khóa tìm kiếm.” và không điều hướng.
- Admin Search nhập “sản phẩm” mở gợi ý “Sản phẩm”; Enter kích hoạt lựa chọn. Component Admin Search không sửa.
- Console hai tab QA: không ghi nhận error/warn tại thời điểm kiểm tra.

## Giới hạn

- Không dùng tài khoản thật để kiểm thử đăng nhập trên cổng 5173; luồng xác thực được kiểm thử bằng tài khoản demo UAT cùng source. Không thay đổi schema/dữ liệu nghiệp vụ, secrets hay môi trường; chỉ tạo/thu hồi phiên đăng nhập QA.
- Lỗi API và logout mất mạng kiểm thử tự động, không chủ động ngắt server để tạo lỗi trên trình duyệt. Logout mất mạng xóa phiên Customer cục bộ và cảnh báo chưa xác nhận thu hồi server; token server có thể tồn tại đến khi hết hạn.
- Không thực hiện lại giao dịch đặt hàng trong đợt sửa Header. Regression tự động cho Product Detail/cart/checkout/voucher đã đạt; không tuyên bố UAT lại toàn bộ hệ thống A–H trong báo cáo này.

Ảnh: `account-desktop.jpg`, `account-mobile.jpg`, `search-desktop-localhost.jpg`, `search-mobile.jpg`, `search-no-results.jpg` trong `docs/header-fix-screenshots/`.

URL trải nghiệm: **http://localhost:5173/**. Bản demo UAT cùng source: **http://127.0.0.1:5175/**.
