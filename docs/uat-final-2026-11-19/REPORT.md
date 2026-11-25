# UAT desktop local N5-142 và N5-143

Ngày 19/11/2026. Nhánh `nhanh-cua-duong`. Chỉ kiểm thử SOFEP; không sửa Jira/Proposal/assignee, không commit/push/deploy. Giữ các thay đổi chưa commit đã có.

## Kết quả theo ticket

| Ticket | Passed | Failed/lỗi sửa | Blocked |
|---|---|---|---|
| N5-142 | HTTP MySQL: Guest tìm catalog/chi tiết; Customer/Admin đăng nhập; COD chưa trả; Admin ghi nhận COD và chuyển đúng lifecycle; mô phỏng failed/success/retry; giá/voucher/stock server; idempotency; lịch sử/chi tiết owner; Customer khác 403/404, token thu hồi 401 | Đã xác nhận từ code và tái hiện bằng test: MyOrders giữ dữ liệu cũ khi logout/đổi Customer, response cũ có thể cập nhật lại. Đã sửa và regression đạt | Chưa có UAT browser mới cho toàn luồng, đặc biệt lịch sử đơn sau bản vá |
| N5-143 | HTTP MySQL: Customer góp ý kèm ảnh; Admin đọc/xử lý/phản hồi; Guest/Customer không đọc được góp ý/ảnh qua API Admin; preview AND đúng 1 khách; retry không gửi trùng; thông báo/ví/voucher theo chủ tài khoản; quote sai người 422; hoàn lượt hủy lặp; opt-out loại đúng khách đủ điều kiện | Không phát hiện lỗi ứng dụng mới trong các ca HTTP đã chạy | UAT browser form góp ý, Admin xử lý, preview/send và Customer đọc/lưu/dùng voucher |

Có **110 kiểm tra trong `http-uat.json` + 13 kiểm tra bổ sung trong `http-extra.json`, tất cả đạt**. Những con số này gồm HTTP status và invariant, không phải 123 ca browser. Giỏ Guest và giữ giỏ qua đăng nhập có test frontend và bằng chứng browser lịch sử N5-142; không giả nhận đã thao tác lại trong lượt này.

## Môi trường và dữ liệu

- FE UAT: `http://127.0.0.1:5190`; BE: `http://127.0.0.1:8030`.
- MySQL: `sofep_security_uat_20261118_d588ff07a906`, schema UAT từ N5-147, không migrate/seed/reset/import lượt này.
- Cache/lock prefix riêng: `final142143-a1ff7601:`; bootstrap trước provider chỉ cho connection `security_browser` vào UAT. `/__uat/status` đã xác nhận.
- Fixture riêng tag `a1ff7601`, sản phẩm slug `final-uat-a1ff7601`, một variant Đen/M142; đầu 30, cuối 26.
- Hai COD 250.000đ đã được Admin ghi đã thu/đã giao; mô phỏng qty 2: 440.000 − 44.000 + 30.000 = **426.000đ**, failed vẫn chưa trả, sau success/đã giao.
- Đơn dùng voucher riêng: 228.000đ, hủy hai lần; kho và lượt chỉ hoàn một lần. Public voucher còn 1 lượt dùng, private voucher về 0. Snapshot `database-state.json`.
- Tài khoản Customer A/B và Admin riêng `@sofep.example.test`, không tài khoản thật. Mật khẩu ngẫu nhiên chỉ trong manifest `%TEMP%/sofep-final-uat-a1ff7601/session.json`; không đưa vào report/log.
- Mail array, phản hồi và chiến dịch chỉ in-app; không gửi email/SMS/tiền/giao hàng thật. Ảnh góp ý private ở storage trong TEMP riêng.
- Campaign chỉ chọn đúng một fixture, dựa ba đơn giao và đã trả, tổng 926.000đ. Opt-out của chính khách đủ điều kiện làm preview về 0; khôi phục preference preview về 1. Không gửi chiến dịch rộng.
- Các tiến trình local cần còn chạy để PO truy cập; URL này không phải deployment.

## File thay đổi trong lượt này

1. `Fe/src/pages/client/MyOrders.vue`: theo dõi thay đổi phiên Customer, xóa dữ liệu cũ khi logout/đổi tài khoản; epoch bỏ response danh sách/chi tiết thuộc phiên trước; hủy listener khi unmount.
2. `Fe/tests/sprint34.test.mjs`: thêm test logout → login B trong lúc request A chờ, không khôi phục đơn A hoặc làm tắt loading B.
3. `themmoik27be/tests/Support/FinalPurchaseEngagementUat.php`: harness riêng, bootstrap cô lập, fixture có tag, loopback HTTP server và snapshot chỉ dữ liệu fixture.
4. `docs/uat-final-2026-11-19/`: hai runner HTTP, JSON evidence, logs test/build, snapshot và báo cáo. Các thay đổi Dashboard/API history của lượt Sprint 3/4 được giữ nguyên, không ghi là mới sửa trong lượt này.

## Kiểm thử sau sửa

| Lệnh | Kết quả |
|---|---|
| `node --test tests/*.test.mjs` trong Fe | 68/68 Passed, 0 fail; có race session/response mới |
| `php vendor/bin/phpunit --no-progress --do-not-cache-result` trong BE | 90 bài: 88 Passed, 2 exporter tùy chọn skipped; 1.374 assertions; SQLite :memory: |
| `npm run build -- --outDir ../docs/uat-final-2026-11-19/build-check` | Passed, output riêng |
| `node docs/uat-final-2026-11-19/http-uat.mjs` | 110 kiểm tra Passed trên HTTP/MySQL UAT, trước bản vá FE-only |
| `node docs/uat-final-2026-11-19/http-extra.mjs` | 13 kiểm tra Passed, opt-out/history/token |
| `php -l tests/Support/FinalPurchaseEngagementUat.php` | Passed |

Runner HTTP chính cần fixture mới từ mode `prepare`; không chạy lại trên fixture đã có đơn rồi diễn đạt như một lượt sạch. HTTP tests không đo hiệu năng hay chứng minh chống tải nhiều worker; race MySQL riêng ở `docs/sprint34-2026-11-19/mysql-uat.json`. Không đồng nhất SQLite với MySQL.

## Checklist PO nghiệm thu desktop còn lại

- [ ] N5-142: Guest tìm sản phẩm fixture, chọn màu/size, thêm–sửa–xóa giỏ; checkout yêu cầu đăng nhập và giữ giỏ.
- [ ] N5-142: đặt COD/mô phỏng, thử voucher đúng/sai, đối chiếu giá/kho; Admin nhận và cập nhật; Customer mở `/my-orders`, chi tiết và trạng thái mới.
- [ ] N5-142: đăng xuất tại lịch sử đơn, đăng nhập B; không còn đơn A. Kiểm tra retry khi API lỗi và phiên hết hạn.
- [ ] N5-143: Customer gửi góp ý/ảnh, Admin đọc/xử lý/phản hồi; B không thấy phản hồi hoặc tài nguyên của A.
- [ ] N5-143: Admin preview đúng nhóm, gửi thông báo, retry; A đọc và lưu mã một lần, dùng đúng điều kiện; B không nhận/lưu/dùng mã riêng.
- [ ] Ghi ảnh desktop/log thao tác, kết quả và xác nhận PO. Không yêu cầu mobile/tablet hoặc email thật.

## Kết luận nghiệm thu

API/test **Passed**; nghiệm thu browser toàn ticket **Blocked/chờ PO**. Không tự chuyển Done. Trước khi lượt này bắt đầu, Computer Use đã dừng ở lượt trước do không xác định đủ chắc URL Chrome; chưa coi đó là lỗi website. Đã thử lại: input báo thiếu geometry, cây Chrome và ảnh chụp khác ứng dụng; dừng thao tác để tránh nhập sai cửa sổ. Chi tiết `browser-status.md`. Không có ảnh SOFEP mới được nghiệm thu. Các checklist chưa đánh dấu không được tính Passed.
