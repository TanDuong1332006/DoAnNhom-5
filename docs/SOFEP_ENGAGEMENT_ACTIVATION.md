# Kích hoạt Customer Engagement trên MySQL local

Ngày 07/11/2026, nhánh `nhanh-cua-duong`. Đã áp dụng đúng hai migration được PM duyệt vào **MySQL 8.4.3 local `doan_nhom_5`**, loopback cổng 3306. Không sửa source, `.env`, secrets hoặc kết nối ứng dụng; không commit/push/deploy. Các thay đổi source có sẵn được giữ nguyên.

## Backup mới và cửa sổ ngăn ghi

Đã bật Laravel maintenance riêng SOFEP trước backup và migration; không thấy worker/scheduler SOFEP đang chạy hoặc kết nối khác tới database chính ở bước pre-flight. Không dừng MySQL chung hay ứng dụng khác. Snapshot dữ liệu ổn định trong suốt backup/kiểm chứng.

- Thư mục: `C:/Users/dn133/AppData/Local/SOFEP/backups/preflight_activation_20261107_163842/`
- File: `doan_nhom_5.full.sql`, **292.774 byte**; snapshot 16:38:54 ngày 07/11/2026 giờ Việt Nam.
- SHA-256: `b278931430b0930598c1f02a36f3b80f9de02724a6b9865c10580058d3b0ced5`.
- Đã phục hồi thực tế sang database mới `sofep_restorecheck_20261107_1a17843c4933`, giữ lại để kiểm chứng, không nối ứng dụng.
- Khớp 28 bảng, 1.419 dòng, hash nội dung từng bảng, toàn bộ metadata cột, DDL logic, AUTO_INCREMENT, index/FK. MySQL biểu diễn dư `CHARACTER SET utf8mb4` trong DDL phục hồi; chỉ chuẩn hóa phần chữ dư này sau khi kiểm tra metadata thực tế giống nhau.
- Backup ngoài Git, ACL chỉ tài khoản Windows hiện tại và SYSTEM; file xác thực MySQL tạm đã xóa. Không import đè database chính. SQL không bao gồm file ảnh ngoài DB; migration không thay file ảnh.

## Hai migration đã thực hiện

Gọi Laravel migrator **từng đường dẫn cụ thể**, `--force --no-interaction`, không gọi migrate mặc định và không chạy migration khác. Đã kiểm tra hash file khớp báo cáo PM, lịch sử migration chưa có hai file và schema trước triển khai khớp bản đã review.

| Migration | Kết quả | Giờ Việt Nam | Batch |
|---|---|---|---|
| `2026_11_03_000001_add_voucher_eligibility_and_order_reservations.php` | Thành công; xác minh xong mới chạy bước 2 | 16:39:50 | 5 |
| `2026_11_06_000001_create_customer_engagement_tables.php` | Thành công | 16:39:58 | 6 |

Migration thứ nhất thêm 3 cột voucher (`doi_tuong`, `gioi_han_moi_khach`, `ids_khach_hang`), 3 cột đơn (`ton_kho_da_giu`, `voucher_da_giu`, `checkout_fingerprint`) và index `orders_voucher_customer_status`.

Migration thứ hai tạo 8 bảng: `private_feedback`, `feedback_attachments`, `customer_preferences`, `customer_campaigns`, `campaign_recipients`, `customer_notifications`, `voucher_grants`, `saved_vouchers`; thêm `campaign_restricted`, `mo_ta` vào `ma_giam_gias`. Các bảng mới đều rỗng sau kích hoạt. Voucher/đơn lịch sử nhận đúng mặc định/NULL cho cột mới.

| Baseline | Trước | Sau migration |
|---|---:|---:|
| Bảng | 28 | 36 |
| Dòng trong `migrations` | 23 | 25 |
| Sản phẩm / đang bán | 168 / 168 | 168 / 168 |
| Khách hàng | 3 | 3 |
| Đơn hàng | 36 | 36 |
| Biến thể | 500 | 500 |
| Ảnh gallery trong DB | 514 | 514 |
| Voucher | 5 | 5 |
| Mục cột trong index (`STATISTICS`) | 71 | 101 |
| Cột foreign key | 19 | 21 |

Đã so sánh hash toàn bộ dữ liệu chiếu trên các cột cũ sau từng migration; không thay giá trị cũ. Hậu kiểm tiếp tục khớp 24 bảng nghiệp vụ. Các bảng runtime như token/cache/session được tách khỏi hậu kiểm sau khi mở website; truy cập phiên có thể cập nhật token và cache.

**Chi tiết index:** InnoDB tự bỏ index hỗ trợ FK đơn `don_hangs_id_khach_hang_foreign` khi index ghép mới có cùng tiền tố `id_khach_hang` xuất hiện. Foreign key tương ứng vẫn nguyên vẹn, index ghép có đúng thứ tự 3 cột; các index cũ khác không đổi. Đã tạm bật lại bảo trì để điều tra chênh lệch này, không chạy DDL/rollback bổ sung, chỉ mở lại sau khi xác minh. Hai FK mới chỉ nối các bảng engagement mới. Không có mất dữ liệu hoặc FK.

## Kiểm thử và vận hành

- `node --test tests/*.test.mjs` tại `Fe/`: **59/59 đạt**.
- `npm run build`: đạt, 168 module được biên dịch; không cài dependency.
- `php artisan test`: **72 bài, 70 đạt, 2 bỏ qua** do chức năng xuất file tùy chọn; 1.183 assertions. SQLite `:memory:` có guard từ chối database thật; mail array, session/cache cô lập. Không dùng kết quả SQLite để thay cho kiểm tra MySQL.
- HTTP MySQL UAT `sofep_uat_20261103_0552eb8e0b6d`: góp ý/ảnh riêng, Admin quản lý/phản hồi, preview và gửi thử 1 đối tượng fixture, Customer nhận thông báo, IDOR, Lưu mã duy nhất/không tiêu lượt, áp voucher theo giá server, tạo đơn idempotent, hủy đơn hoàn lượt một lần, lưu trạng thái đọc: đạt. Chỉ UAT tạo đơn/voucher/chiến dịch, không marketing tài khoản thật, không gửi email/SMS.
- Smoke kernel trên DB chính với PDO chỉ đọc: Admin danh sách góp ý/chiến dịch và preview đạt 200; Customer hộp thư/ví/tùy chọn đạt 200; Customer vào Admin và Admin vào ví Customer bị 403; Guest vào ví/tạo đơn bị 401. User model có sẵn được gắn guard trong bộ nhớ, không tạo tài khoản/token hoặc thay thông tin xác thực. Không tạo/gửi chiến dịch chính.
- HTTP thật gửi form góp ý rỗng trả **422**, không tạo góp ý. Một thử nghiệm validation qua PDO read-only trả 500 do middleware throttle cần ghi cache; đã tách kiểm tra này sang HTTP bình thường và xác minh 422. Đây là giới hạn harness read-only, không phải lỗi form đang chạy.
- Browser 5173: form góp ý hiển thị; phiên Customer hiện có tải hộp thư 0 chưa đọc và ví rỗng, không có lỗi chờ migration. Customer vào Admin bị chuyển đăng nhập đúng quyền. Không gửi góp ý thử vào DB chính.
- Catalog smoke sau kích hoạt: API trả 168 sản phẩm; Product Detail 2.1 của ID 172/84/1 đạt, có lần lượt 9/3/2 biến thể và 2/4/4 ảnh gallery; ba ảnh chính HTTP 200. Hash dữ liệu toàn bộ catalog/biến thể/ảnh trong DB giữ nguyên. Một lượt audit HTTP toàn ảnh đã được dừng để tránh kéo dài tải trên PHP dev server; không coi đó là kiểm tra toàn ảnh hoàn tất. Bằng chứng mới nằm ở `sofep-engagement-activation-catalog.json`.
- Đã mở lại SOFEP, maintenance tắt. Khởi động scheduler local bằng `php artisan schedule:work`, PID ban đầu 9784; lịch `sofep:deliver-campaigns --limit=100` mỗi phút chạy thành công. Log trong thư mục backup riêng. Không cấu hình tự khởi động Windows; sau khi dừng tiến trình/khởi động lại máy cần chạy lại scheduler hiện có. Tác vụ không gửi email/SMS.
- `git diff --check`: đạt; chỉ cảnh báo CRLF trên các thay đổi có sẵn.

## URL chính thức local

- Cửa hàng: <http://localhost:5173/>
- Đóng góp ý kiến: <http://localhost:5173/gop-y>
- Thông báo Customer: <http://localhost:5173/thong-bao>
- Ví ưu đãi: <http://localhost:5173/vi-uu-dai>
- Admin quản lý góp ý: <http://localhost:5173/admin/gop-y>
- Admin tạo/preview thông báo: <http://localhost:5173/admin/thong-bao>

## Giới hạn và khôi phục

Không phát hiện lỗi chặn kích hoạt. Chưa thao tác trình duyệt bằng phiên Admin thật trên 5173; đã kiểm tra endpoint chính ở chế độ chỉ đọc và toàn luồng Admin qua HTTP UAT. Không tạo thông báo/voucher/đơn thử trên DB chính để giữ sạch dữ liệu. Chưa kiểm chứng gửi chiến dịch hẹn giờ qua giao diện tài khoản thật; scheduler đã xác nhận chạy và logic hẹn giờ có regression/UAT.

Nếu phát sinh lỗi: tạm ngăn ghi SOFEP và dừng riêng scheduler SOFEP, giữ log/schema/migration history; không rollback mù, không import backup đè DB chính. Backup đã phục hồi độc lập; mọi phục hồi/cutover vào chính cần phê duyệt riêng. Không chạy fresh/refresh/reset/wipe/seeder, không xóa dữ liệu hoặc sửa sản phẩm/Seller lịch sử. Không kết nối hoặc thay đổi database Barber/production.

Bằng chứng: `sofep-engagement-activation.json`, `sofep-engagement-activation-smoke.json`, `sofep-engagement-activation-catalog.json` (mới), `sofep-customer-engagement-uat.json` (cập nhật kết quả UAT); manifest, baseline schema/index/FK và log migration ở thư mục backup riêng. Báo cáo này cũng là file mới. Source ứng dụng giữ nguyên.
