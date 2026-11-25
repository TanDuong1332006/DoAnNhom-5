# Kiểm tra an toàn trước migration SOFEP

**Ngày 07/11/2026 — Chưa chạy migration lên `doan_nhom_5`.** Phạm vi là database local MySQL 8.4.3 tại loopback 3306; không kết nối production hay truy vấn/thay đổi database Barber.

## Hai migration, đúng thứ tự

| Migration | Tác động của `up()` |
|---|---|
| `2026_11_03_000001_add_voucher_eligibility_and_order_reservations.php` | Thêm `doi_tuong` (mặc định `all`), `gioi_han_moi_khach`, `ids_khach_hang` vào `ma_giam_gias`; thêm `ton_kho_da_giu`, `voucher_da_giu` (mặc định false), `checkout_fingerprint` vào `don_hangs`; thêm index `orders_voucher_customer_status`. |
| `2026_11_06_000001_create_customer_engagement_tables.php` | Tạo 8 bảng `private_feedback`, `feedback_attachments`, `customer_preferences`, `customer_campaigns`, `campaign_recipients`, `customer_notifications`, `voucher_grants`, `saved_vouchers`; thêm `campaign_restricted` (false) và `mo_ta` (nullable) vào `ma_giam_gias`. Phải chạy sau migration thứ nhất. |

Cả hai `up()` **không xóa, đổi tên bảng/cột hoặc cập nhật/ghi đè giá trị các trường nghiệp vụ cũ**. Hàng cũ chỉ nhận giá trị mặc định/NULL cho cột mới; không tự gán quyền riêng cho voucher cũ, không suy đoán đơn cũ đã giữ kho. Các bảng mới ban đầu rỗng. Foreign key mới chỉ nối các bảng engagement mới.

Hai migration chưa có bản ghi trong bảng `migrations` chính; các cột/bảng/index dự kiến thêm chưa tồn tại, không thấy xung đột tên. Đã lưu SHA-256 hai file trong báo cáo JSON để đối chiếu lại trước triển khai. **Không dùng `down()` để rollback mù**: các hàm đó có bỏ bảng/cột, có thể làm mất dữ liệu mới.

## Sao lưu và kiểm chứng phục hồi

- SQL đầy đủ, ngoài Git, giới hạn ACL cho tài khoản Windows hiện tại và SYSTEM:
  `C:/Users/dn133/AppData/Local/SOFEP/backups/preflight_20261107_c1d5a8edf0/doan_nhom_5.full.sql`
- Dung lượng **292.774 byte**, SHA-256:
  `d376a21601f783ec01af291ae4e74115103967f2d790d584831f763c1ec84a43`.
- Dùng mysqldump 8.4.3, `--single-transaction --quick`, bao gồm schema/data, routines/events/triggers và binary/JSON. Cả 28 bảng đều InnoDB; số view/trigger/routine/event bằng 0. Không dùng all-databases, không xuất lệnh đổi sang database chính hoặc DROP TABLE/DATABASE, không khóa ghi toàn server.
- Đã khôi phục thực tế vào database mới, độc lập: **`sofep_restorecheck_20261107_ad344fb88db3`**. Database này không nối với ứng dụng, scheduler hoặc worker. Không import đè lên database chính/UAT; giữ lại bản kiểm chứng, không xóa database nào.
- **28/28 bảng, 1.419/1.419 dòng**, SHA-256 nội dung từng bảng khớp snapshot; metadata đầy đủ của cột, schema logic, index/FK và bộ đếm AUTO_INCREMENT đều khớp. FK của bản phục hồi chỉ tham chiếu trong database phục hồi.
- MySQL hiển thị thêm chữ `CHARACTER SET utf8mb4` ở một số DDL phục hồi. Đã kiểm chứng charset/collation/type/default thực tế giống nhau; chỉ chuẩn hóa cách biểu diễn dư thừa này khi so sánh DDL, không sửa schema hoặc dữ liệu.
- File cấu hình xác thực MySQL tạm đã được xóa; không lưu credentials hoặc dữ liệu khách hàng vào Git/báo cáo.

Snapshot ổn định trong lúc lấy backup. Sau đó có hoạt động phiên đang chạy làm thay đổi `last_used_at`/`updated_at` của **1 token**, số dòng không đổi; các bảng nghiệp vụ vẫn khớp snapshot. Vì vậy backup là bản chụp tại thời điểm đã ghi, không đại diện mọi hoạt động xảy ra sau đó. Cần lấy backup mới ngay trước cửa sổ triển khai có kiểm soát ghi.

## Đối chiếu schema chính với UAT

Đối chiếu với `sofep_uat_20261103_0552eb8e0b6d`, cùng MySQL 8.4.3. Chênh lệch đúng nhóm tính năng mới: UAT có 8 bảng engagement, 5 cột voucher và 3 cột giữ đơn/index nêu trên.

Có ba khác biệt nền cần giữ nguyên, **không đồng bộ schema từ UAT đè lên chính**:

- Chính có thêm `cua_hangs` và `migrations`; UAT được chuẩn bị bằng script riêng nên không có hai bảng đó.
- Chính có `chi_tiet_san_phams.id_anh_san_pham`, UAT không có; hai migration không đụng bảng biến thể.
- `khach_hangs.role`: chính `varchar(20)`, UAT `varchar(255)`; null/default/collation giống nhau, các role đang dùng đều vừa 20 ký tự; không cần đổi cột này.

Không thấy xung đột kiểu dữ liệu ở các cột nền của hai bảng cần mở rộng. InnoDB page size 16 KB hỗ trợ độ dài index dự kiến; quyền SELECT/INSERT/CREATE/ALTER/INDEX cần thiết đã xác minh. **Kết luận: không thấy chặn tương thích schema cho hai migration**, dựa trên metadata chính và schema đã chạy ở UAT. Chưa chạy DDL/migration trên bản phục hồi hoặc database chính trong bước này; không khẳng định không có rủi ro metadata lock/disk/runtime.

## Vì sao 168 sản phẩm so với 167

Chính có đúng một sản phẩm không có trong UAT: **ID 172 — Áo polo**, SKU `SPYKRV3B`, đang bán, giá 350.000đ/khuyến mãi 300.000đ. `created_at` lưu trong DB là **2026-11-04 08:21:49 UTC** (15:21:49 giờ Việt Nam), sau bản sao catalog UAT ngày 03/11/2026.

Không có sản phẩm chỉ nằm ở UAT. ID/SKU/tên/slug/giá/trạng thái/danh mục/cửa hàng của **167 sản phẩm chung đều khớp**. Vì vậy đây là khác biệt thời điểm chụp catalog, không phải mất sản phẩm hay cần ép trạng thái để đủ số lượng. Không có audit log để kết luận ai tạo ID 172; không sửa/xóa sản phẩm hoặc sao chép dữ liệu UAT vào chính.

## Kế hoạch triển khai và khôi phục — chỉ sau phê duyệt

1. Chốt cửa sổ triển khai; tạm ngăn thao tác ghi riêng SOFEP, dừng worker SOFEP liên quan nếu cần. Không dừng MySQL chung hoặc tác động ứng dụng Barber.
2. Kiểm tra lại đúng host/database, hash hai migration, schema và lịch sử migration; lấy backup mới, kiểm tra tính toàn vẹn/khả năng phục hồi.
3. Chạy **từng migration theo đường dẫn cụ thể**, thứ tự 03/11 rồi 06/11; không chạy toàn bộ migration còn chờ. Dừng ngay nếu bước trước lỗi.
4. Đối chiếu đầy đủ schema/index/log sau từng bước; xác nhận cột cũ, số dòng, catalog, đơn và dữ liệu khách không bị thay đổi. Smoke test phân quyền và các chức năng mới trước khi mở lại ghi/worker.
5. Nếu lỗi: giữ chế độ ngăn ghi, lưu lỗi và kiểm tra `information_schema` cùng `migrations`. MySQL DDL tự commit từng lệnh, nên có thể để lại schema dở dang; **không retry mù, không rollback/reset/down mù, không import backup đè lên chính**. Chọn hoàn tất phần thiếu sau rà soát hoặc khôi phục sang database riêng và kiểm chứng trước; mọi cutover/khôi phục vào môi trường chính phải được phê duyệt riêng. Giữ nguyên database gốc và lịch sử phát sinh để tránh mất dữ liệu.

Chưa bật migration, bảo trì hoặc thay `.env`/kết nối ứng dụng trong bước kiểm tra này. Không chạy migrate:fresh/migrate:reset/db:wipe/seed/truncate, không xóa dữ liệu, không commit/push/deploy. SQL backup chỉ chứa database, không chứa file ảnh ngoài DB; hai migration không thay các file ảnh.

**Kết luận:** backup đã phục hồi được và kiểm chứng khớp; hai migration là thay đổi cộng thêm, không thấy xung đột schema. Báo cáo sẵn sàng để PM xem xét phê duyệt việc áp dụng — chưa triển khai lên `doan_nhom_5`.

Bằng chứng máy đọc được: `docs/sofep-pre-migration-safety.json`. Manifest và schema chi tiết, log kiểm chứng được giữ trong thư mục backup riêng; không đưa SQL chứa dữ liệu nhạy cảm vào repository.