# Gỡ nhãn ảnh khỏi giao diện — 21/11/2026

**PASS trên localhost:5173**: 167 ID sản phẩm vẫn tải ảnh, ID 172 vẫn placeholder; không còn nhãn/badge “Ảnh minh họa”. FE tests **78/78**, Vite build đạt.

## File sửa

| File | Thay đổi |
|---|---|
| `Fe/src/components/shared/ProductImage.vue` | Gỡ badge, CSS và import/method chỉ dùng để hiện nhãn. Giữ nguyên img/src, kích thước, fallback và reset khi đổi source. Áp dụng cho Customer, Admin, danh sách, Quick View, giỏ và các chỗ dùng component. |
| `Fe/src/components/Client/ChiTietSanPham.vue` | Gỡ badge và thông báo về ảnh, gỡ nhãn trong phóng to; caption dùng “ảnh sản phẩm”. Bỏ computed/import chỉ phục vụ nhãn. Thông báo khi không có ảnh phù hợp đổi thành câu trung tính, giữ nguyên điều kiện lọc. |
| `Fe/src/components/Admin/SanPham/ProductImages.vue` | Đổi chữ của ô phân loại nguồn thành “Lưu phân loại nguồn AI/stock (nội bộ)”; giữ checkbox, state và payload `image_kind=illustration` để không mất provenance khi upload. |
| `Fe/tests/product-images.test.mjs` | Cập nhật kỳ vọng nhãn đã mất; kiểm tra upload vẫn gửi phân loại nguồn nội bộ. Các kiểm tra fallback/HOLD/default ảnh giữ nguyên. |

Không sửa service ảnh, cấu hình API, danh sách HOLD, URL, dữ liệu ảnh hoặc backend. SHA-256 các service được bảo vệ, manifest AI, hồ sơ nguồn 20 file và ZIP demo giữ nguyên (`protected-files-before.json`, `verification.json`). Không xóa provenance/checksum hoặc thông tin kiểm kê nội bộ.

## Kiểm tra trực tiếp

- Trang chủ: **167 ID ảnh riêng biệt**, toàn bộ **173 phần tử ảnh quan sát được đã tải**; có ảnh lặp ở khu ưu đãi và ảnh giỏ có sẵn. ID 172 xuất hiện ở hai thẻ và đều dùng placeholder. Không nhãn trong text hoặc badge DOM (`browser-home.json`).
- Danh sách `/tim-kiem`: 169 phần tử ảnh gồm ảnh giỏ; không nhãn. Tìm kiếm thực tế `?q=Áo`: 68 phần tử ảnh tải được, không nhãn (`browser-list.json`, `browser-search.json`).
- Chi tiết sản phẩm 1: ảnh chính/thumbnail tải được, không badge hoặc thông báo cũ; mở phóng to thấy ảnh tải đúng và không nhãn (`browser-detail.json`, `browser-zoom.json`, `detail-no-label.png`).
- Nút xem nhanh trên trang chủ hiện điều hướng đến trang chi tiết; đã bấm và xác nhận không nhãn. Template Quick View cũ cũng dùng `ProductImage`, không còn logic nhãn riêng. Không thay cách điều hướng.
- Mở giỏ hàng có sẵn: không còn nhãn; không thêm/xóa món hoặc sửa localStorage của PO.
- Admin: đã rà source toàn bộ `Fe/src/components`/`Fe/src/pages`, build và test upload multipart/phân loại nguồn. Không đăng nhập hoặc upload trên database chính; không tuyên bố đã kiểm thử toàn trang Admin với phiên xác thực thật.

Tìm toàn bộ source giao diện không còn chuỗi “Ảnh minh họa”, class badge hoặc computed nhãn. Hàm phân loại ảnh trong service vẫn giữ nguyên để phục vụ bộ lọc, không bị xóa theo nhãn.

## Kiểm thử và bảo toàn

- `node --test Fe/tests/*.test.mjs`: **78 passed, 0 failed/skip**.
- `npm run build` trong `Fe/`: **PASS**.
- `git diff --check` bốn file: không lỗi whitespace; chỉ có cảnh báo line-ending ở working tree.
- PDO read-only verify: catalog, lịch sử đơn đầy đủ, 23 URL/41 tham chiếu ảnh đơn, **187 file Storage** và hai `.env` không đổi. Bằng chứng tại `preservation.json`.
- Không chạy lại BE tests vì không sửa backend; không gửi thao tác ghi database/storage chính.

Giữ nhánh `nhanh-cua-duong` và các thay đổi có sẵn. Không commit/push/deploy, không chỉnh Jira/Proposal, không tác động N.Cảnh Barber. Báo cáo, log, JSON và ảnh chụp mới nằm trong `docs/remove-image-labels-2026-11-21/`.
