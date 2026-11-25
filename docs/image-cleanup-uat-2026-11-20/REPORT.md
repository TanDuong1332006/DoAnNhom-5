# Kiểm thử dọn ảnh cũ trên database demo — 20/11/2026

## Kết luận trình PO

**Không duyệt phương án xóa cả 298 dòng theo danh sách cũ.** Rà lại URL theo nguồn ảnh phát hiện **71 dòng cần giữ bảo thủ** vì cùng ảnh Unsplash với snapshot đơn, chỉ khác query kích thước. Danh sách cũ so URL nguyên văn nên bỏ sót những tham chiếu này. Báo cáo này thay thế đề xuất “298 dòng không có tham chiếu” trong `image-demo-sync-2026-11-20`.

**PASS thử xóa 227 dòng đã xác minh trên UAT cô lập**, có backup và thử khôi phục thành công. 167 ảnh minh họa vẫn hoạt động, ID 172 vẫn dùng placeholder. **Chưa áp dụng vào database/storage chính, gói ZIP hoặc bản bàn giao. Chờ PO duyệt kết quả và phạm vi trước khi triển khai bản bàn giao.**

Một điểm cần phân biệt: **database demo được import từ ZIP vốn chỉ có 167 gallery; không chứa ID nào trong 298 dòng**. Vì vậy nó không cần xóa thêm. Lượt thử có chủ ý đưa lại 298 dòng catalog vào bản sao UAT để thực sự thử DELETE/khôi phục; không báo xóa 298 khi SQL chỉ tác động 0 dòng.

## Số lượng trước/sau

| Phạm vi | Gallery ban đầu | Thao tác | Gallery cuối |
|---|---:|---|---:|
| Database chính `doan_nhom_5` | 514 | Chỉ SELECT trong transaction READ ONLY | 514 |
| Demo gốc từ ZIP | 167 | Không đổi; 298 ID đều vắng mặt | 167 |
| Backup database demo mới | 167 | Bản sao giữ nguyên | 167 |
| Kịch bản UAT thử dọn | 465 = 167 + 298 fixture catalog | DELETE đúng 227 ID; giữ 71 ID bảo vệ | **238 = 167 + 71** |

168 sản phẩm và 501 biến thể của demo giữ nguyên; 501 gồm 500 biến thể catalog và một fixture synthetic ID 510 → ảnh 436 của sản phẩm 1. Không khẳng định đã khôi phục 9 liên kết ảnh gốc của ID 172; chúng vẫn null trong demo như trước.

Database backup: `sofep_image_restore_20261120_858331c19d01`.

Database thử: `sofep_image_restore_20261120_078039924064`.

FE **5196** → BE **8044**, MySQL và Storage TEMP riêng; không dùng các port chính để đạt kết quả. Source backend là archive sạch, Laravel HTTP kernel thật qua harness UAT; frontend dùng bản sao working tree đã vá của lượt trước và dependency có sẵn. Không sửa source ứng dụng hoặc `.env` PO trong nhiệm vụ này.

## Phân loại từng dòng

[CANDIDATE_REVIEW.md](CANDIDATE_REVIEW.md) liệt kê đủ **298 ID ảnh, ID sản phẩm, URL, nguồn, vai trò main/gallery/biến thể, tham chiếu và quyết định**. Bản máy đọc: [candidate-audit.json](candidate-audit.json).

| Quyết định | Số dòng | Nguồn | Lý do |
|---|---:|---|---|
| Thử loại khỏi demo | 227 | 19 Pexels theo hồ sơ lịch sử, 208 Unsplash | Không ảnh chính; không FK biến thể hoặc FK khác tới ảnh; không có URL ảnh liên quan ngoài gallery sau chuẩn hóa nguồn |
| Phải giữ trong kịch bản | 71 | Unsplash, 15 đường dẫn nguồn | Cùng nguồn ảnh với `chi_tiet_don_hangs.hinh_anh`, dù khác query kích thước |

Ví dụ: gallery ID **8**, sản phẩm **8**, URL `https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=500` có nguồn ảnh trùng snapshot đơn. Gallery ID **9 và 10** của sản phẩm 9 cũng cần giữ theo quy tắc này. Không coi query `w=500`/`w=700` là hai ảnh độc lập để cho phép xóa. Đây là quyết định bảo thủ về tham chiếu nguồn, không khẳng định bytes ảnh biến đổi kích thước phải giống nhau.

Tất cả 298 dòng **đang là gallery trong database chính**; chúng không phải “không được tham chiếu ở bất cứ đâu”. “Có thể loại” chỉ có nghĩa không cần cho phần ảnh đang dùng của bản demo trong kịch bản đã kiểm tra, sau khi loại trừ tham chiếu cần bảo toàn.

Đã kiểm tra FK từ schema MySQL, các cột ảnh URL của gallery, sản phẩm, danh mục, thương hiệu và dòng đơn; không sao chép khách hàng/đơn thật. `source-reference-baseline.json` chỉ lưu catalog, tổng tham chiếu URL ảnh đơn và metadata schema. Không chứng nhận mọi chuỗi nhúng trong log hoặc hệ thống ngoài database đã được tìm kiếm.

[MUST_KEEP.md](MUST_KEEP.md) liệt kê **238 dòng còn lại trong UAT** cùng lý do: 167 gallery minh họa đang dùng và 71 dòng cũ được bảo vệ. Tài liệu cũng liệt kê **49 dòng bảo vệ khác nằm ngoài tập 298**: không đưa vào kịch bản và không thao tác tại nguồn.

## Cách thử và khả năng khôi phục

1. Đọc trực tiếp catalog/tham chiếu chính bằng READ ONLY; đối chiếu ID, sản phẩm và URL từng dòng với danh sách cũ. Không tự sửa dữ liệu để đạt điều kiện xóa.
2. Xác nhận demo gốc không có dữ liệu khách/đơn/tài khoản/token hoặc dữ liệu ngoài catalog. Sao lưu schema và dữ liệu demo vào `demo-backup.json`, đồng thời tạo database backup mới và đối chiếu fingerprint dữ liệu/schema.
3. Tạo database thử mới từ backup, thêm lại đúng 298 dòng gallery catalog để có baseline 465 dòng. Chỉ copy 167 file demo vào Storage riêng; không copy ảnh ngoài gói hoặc ảnh khách hàng. Đây là fixture thử dọn, không phải catalog chính hoặc bản bàn giao đã chỉnh.
4. Lưu `scenario-before.json` chứa schema/dữ liệu trước dọn. Chạy baseline API/main ảnh lấy mẫu 8 danh mục + ID 172, rồi mới cho phép thao tác xóa.
5. DELETE trong transaction theo 227 ID allowlist; kiểm tra lại main/order/FK và dữ liệu từng dòng `FOR UPDATE`, yêu cầu số dòng xóa đúng 227. **FOREIGN_KEY_CHECKS=1 trong lúc xóa**, không dựa vào `ON DELETE SET NULL` để âm thầm làm mất liên kết biến thể. Kiểm tra tất cả bảng khác không thay đổi.
6. Lưu [deleted-manifest.json](deleted-manifest.json): đầy đủ cột của 227 bản ghi, tên database/table và checksum. Commit chỉ trên database thử. `scenario-after.json` lưu trạng thái sau dọn.
7. Thử INSERT lại từ manifest trong transaction: đối chiếu toàn bộ fingerprint baseline và FK. Sau khi đạt, rollback transaction khôi phục này để UAT quay lại trạng thái **238 dòng** đã dọn. Xem [restore-test.json](restore-test.json).

`workflow.php` guard database prefix UAT và tên trong `state.json`; không có nhánh DELETE trên database chính. Chỉ tạm tắt FK khi tạo/nạp bản clone rỗng mới, sau đó bật lại và kiểm tra toàn bộ FK; không tắt khi xóa hoặc thử khôi phục.

MySQL `SHOW CREATE TABLE` khi clone thêm biểu diễn tường minh `CHARACTER SET utf8mb4` trước cùng `COLLATE utf8mb4_unicode_ci`; fingerprint chuẩn hóa đúng phần biểu diễn dư này. Dữ liệu và cấu trúc FK vẫn được đối chiếu. Các lần chuẩn bị ban đầu dừng ở kiểm tra biểu diễn schema, chưa xóa dòng nào; không DROP database UAT của các lần thử đó.

Các file backup/manifest chỉ chứa catalog demo và schema cần thử; không phải dump database chính. `state.json` là địa chỉ runtime local của một lượt thử, không phải cấu hình portable hoặc credential.

## Kiểm thử trước/sau

| Hạng mục | Trước dọn | Sau dọn |
|---|---|---|
| Catalog API | 168 sản phẩm, main khớp MySQL | 168 sản phẩm, main khớp MySQL |
| HTTP ảnh chính | 9 sản phẩm: 8 danh mục + ID 172 đạt | **Toàn bộ 168**: 167 ảnh local + 1 placeholder đạt |
| Gallery API | 9 chi tiết mẫu khớp baseline 465 | **168 chi tiết** khớp MySQL 238 gallery |
| Gallery hiển thị local | Kịch bản chứa thêm ảnh cũ; một số file ngoài ZIP không tồn tại trong UAT | **167/167** gallery local qua HTTP đạt, hash khớp file |
| Gallery cũ được bảo vệ | Có trong baseline | 71 URL Unsplash giữ trong metadata; resolver chặn, không gọi ra ngoài |
| Ảnh biến thể | Fixture 510 → 436 qua HTTP đạt | Liên kết và ảnh fixture vẫn đạt, chọn màu Tiêu chuẩn trong browser tải đúng |
| Placeholder/lỗi | ID 172 hợp lệ; URL sai trả 404 | ID 172 hợp lệ; hai URL thiếu/sai trả 404 đúng; FE fallback tests đạt |
| FK | 18 quan hệ kiểm tra, 0 orphan | 18 quan hệ kiểm tra, 0 orphan; các bảng khác không đổi |
| FE tests | 77/77 | **77/77** |
| BE tests | 90 passed, 2 skipped, 1381 assertions | **90 passed, 2 skipped, 1381 assertions** |
| FE build | PASS | **PASS** |
| Khôi phục từ manifest | Baseline đã lưu | **PASS 227 dòng**, fingerprint khớp, rollback lại trạng thái đã dọn |

HTTP sau dọn gồm **339 request**, kiểm tra 168 main và 168 chi tiết, tái sử dụng kết quả GET cùng URL để tránh tải lặp gallery/main. Toàn bộ ảnh đang dùng và gallery local có response ảnh đúng; 71 URL ngoài giữ làm tham chiếu **không được gọi HTTP**. Không khẳng định mọi URL metadata bên ngoài đều tải được hoặc website hoạt động offline thực tế.

Lượt baseline đầu có timeout khi browser đồng thời tải nhiều ảnh trên PHP dev server. Đã giữ thông tin sự cố, chạy lại tuần tự với timeout 45 giây và đạt **21 request baseline**; không sửa URL hay dữ liệu để che lỗi, không kết luận ảnh nguồn hỏng chỉ từ timeout đó. Xem `http-baseline-initial-note.json`, `http-before.json`, `http-after.json`.

Browser FE 5196 → BE 8044: sản phẩm 1 trước dọn có 3 thumbnail, hai ảnh cũ ngoài ZIP chuyển fallback; sau dọn còn đúng 1 thumbnail minh họa, ảnh chính và fixture vẫn tải đúng. Trang chủ sau dọn quan sát **172 thẻ ảnh đã tải** (có lặp giữa ưu đãi và danh sách), ID 172 là placeholder. Không nhầm 172 thẻ DOM với 168 sản phẩm. `browser-after.json`, `browser-placeholder.json` và PNG trước/sau lưu bằng chứng.

BE suite dùng SQLite memory, không thay thế kiểm chứng MySQL. Kết luận MySQL dựa trên transaction xóa/khôi phục, FK và API UAT thật. Không thay API, phân quyền hoặc thêm tài khoản; suite backend giữ kiểm tra boundary hiện có. Không thử upload/checkout trên database mới này.

## Bảo toàn và quyết định bản nộp đồ án

[preservation.json](preservation.json) xác nhận catalog/gallery/liên kết biến thể chính không đổi, **23 URL/41 tham chiếu đơn nguyên vẹn**, **187 file ảnh chính không đổi checksum**, 167 ảnh UAT không đổi. Demo gốc và backup nguyên fingerprint. Không xóa file ảnh nào.

**Có thể trình PO duyệt cách dọn 227 dòng theo manifest và guard đã thử; không thể phê duyệt xóa nguyên 298 dòng.** Nếu tiếp tục dùng đúng bản demo ZIP hiện tại với 167 gallery và không có dữ liệu đơn, không cần chạy DELETE vì 298 ID đã vắng mặt. Không tự chuyển kịch bản UAT 238 dòng thành database nộp thầy hoặc thay ZIP gốc; PO cần chọn rõ giữ bộ demo tối giản hiện có hay tạo bản demo bảo toàn metadata trước khi đóng gói.

Đối với bản bàn giao mới, cần PO xác nhận manifest/nguồn dữ liệu, kiểm tra lại tham chiếu trên đúng snapshot đích, có backup và chạy lại verifier sau import. Bản vá FE/API còn trong working tree, chưa commit/push theo giới hạn; chưa chứng nhận clone HEAD trên máy khác hoặc offline thực tế.

## File và lệnh kiểm tra

Không sửa source ứng dụng. Tất cả file mới trong `docs/image-cleanup-uat-2026-11-20/`:

- Công cụ: `workflow.php`, `preservation.php`, `router.php`, `serve.php`, `vite-uat.mjs`, `http-check.mjs`, `tables.mjs`.
- Báo cáo/bảng duyệt: `REPORT.md`, `CANDIDATE_REVIEW.md`, `MUST_KEEP.md`; JSON audit, backup, before/after, manifest, phục hồi, FK, bảo toàn và HTTP; log tests/build cùng ảnh browser.

Đã chạy: `php workflow.php audit/prepare/verify/delete/restore-test`, `php preservation.php`, `node http-check.mjs before/after`, `node tables.mjs`; `php -l` các PHP tool, `node --check` công cụ JS; `node --test Fe/tests/*.test.mjs`, `php artisan test` và `npm run build` trước/sau. Các file kết quả nằm cùng thư mục.

Nhánh vẫn `nhanh-cua-duong`; giữ nguyên thay đổi có sẵn. Không sửa/xóa database hoặc Storage chính, không sửa lịch sử đơn, không commit/push/deploy, không chỉnh Jira/Proposal, không tác động N.Cảnh Barber. **Mọi triển khai dọn bản bàn giao hoặc database thật còn chờ PO duyệt; nhiệm vụ này chỉ hoàn tất kiểm thử UAT.**
