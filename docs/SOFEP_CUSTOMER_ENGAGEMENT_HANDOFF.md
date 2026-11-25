# Bàn giao SOFEP — Góp ý riêng, thông báo và voucher tri ân

Cập nhật chạy lại ngày 07/11/2026. Triển khai trên nhánh nhanh-cua-duong, giữ nguyên các thay đổi chưa commit.

## Trạng thái và URL nghiệm thu

Module đã triển khai trong source và kích hoạt trên **MySQL UAT cô lập**, chưa kích hoạt schema trên database chính.

| Chức năng | URL UAT |
|---|---|
| Góp ý riêng Guest/Customer | http://127.0.0.1:5180/gop-y |
| Admin quản lý góp ý | http://127.0.0.1:5180/admin/gop-y |
| Admin thông báo/chiến dịch | http://127.0.0.1:5180/admin/thong-bao |
| Customer xem thông báo | http://127.0.0.1:5180/thong-bao |
| Customer Ví ưu đãi | http://127.0.0.1:5180/vi-uu-dai |
| Đăng nhập Admin | http://127.0.0.1:5180/admin/login |
| Đăng nhập Customer | http://127.0.0.1:5180/dang-nhap |

API UAT: http://127.0.0.1:8016/api. /__uat/status xác nhận purpose, database và module. Banner UAT hiện rõ trên cổng 5180.

**Website chính vẫn là http://127.0.0.1:5173/**, API 8000, MySQL doan_nhom_5, 168 sản phẩm đang bán. Module mới ở website chính trả thông báo chờ kích hoạt (503) khi thiếu schema, không tự migrate. Checkout/catalog cũ tiếp tục hoạt động; ví voucher không sẵn sàng thì vẫn có thể nhập mã trực tiếp. Không thay catalog chính bằng fixture.

## Chức năng hoàn thiện

### Góp ý private

- Menu desktop/mobile chuyển thành Đóng Góp Ý Kiến; route /gop-y và alias /dong-gop-y-kien. Product Detail dẫn tới góp ý riêng, không còn phần đánh giá cộng đồng trên giao diện; lịch sử đánh giá và cấu trúc API cũ được giữ nguyên.
- Guest gửi được; Customer hợp lệ được liên kết bằng danh tính Sanctum, không tin customer_id client.
- Chủ đề sản phẩm/giao hàng/dịch vụ/website/khác, nội dung 10–5000 ký tự, email/điện thoại tùy chọn.
- Tối đa 5 ảnh JPG/PNG/WebP, 1 MB/ảnh, tối đa 6000×6000 px. Không cần cài GD hoặc sửa giới hạn upload PHP.
- Backend kiểm tra ảnh, honeypot và giới hạn 5 lượt/phút, 20 lượt/ngày theo IP đã HMAC; không lưu IP thô trong bảng góp ý.
- File lưu trên disk local private bằng tên UUID; API ảnh yêu cầu auth Admin, kiểm tra feedback/image ownership, MIME và nosniff, cache private/no-store. Không đưa token vào URL ảnh. Vue dùng blob URL có Authorization, hủy URL khi đóng/chuyển nội dung.
- Admin tìm/lọc theo chủ đề, trạng thái, nội dung/email; xem ảnh, ghi chú nội bộ và trạng thái chưa đọc/đang xử lý/đã xử lý. Không có API xóa góp ý hoặc công khai góp ý.
- Phản hồi riêng vào hộp thư Customer; email chỉ có đường mã được bảo vệ bằng cấu hình tắt mặc định và transport được cho phép. Không gửi email/SMS thật trong UAT. Guest chưa có hộp thư có thể được xử lý qua ghi chú/kênh liên hệ đã xác minh; email tự động chưa bật.
- Nội dung render bằng nội suy Vue, không dùng v-html. Phiên hết hạn khi gửi giữ nguyên nội dung và cho phép đăng nhập hoặc gửi lại dưới dạng Guest.

### Chiến dịch và đối tượng

- Admin soạn tiêu đề, nội dung, loại chung/khuyến mãi/tri ân/voucher, voucher liên kết, thời điểm gửi và điều kiện người nhận.
- Backend chỉ tính Customer đã kích hoạt, không khóa, đúng role; không lấy tập người nhận từ frontend.
- Lọc số đơn hoàn tất và tổng thực chi bằng ngưỡng tối thiểu/tối đa/khoảng, kết hợp AND. Không đặt ngưỡng tương đương tất cả Customer hợp lệ; marketing tôn trọng tùy chọn nhận tin.
- Chỉ tính đơn da_giao + da_thanh_toan; loại đơn hủy/chưa thanh toán/hoàn tiền. Kỳ tuần/tháng/năm theo **ngày tạo đơn đã hoàn tất**, ghi rõ trong UI. Ranh giới tính theo Asia/Ho_Chi_Minh, chuyển sang UTC để truy vấn timestamps hiện có. Không suy đoán ngày hoàn tất lịch sử khi schema chưa có mốc riêng.
- Preview trả số người nhận và HMAC tập người nhận/voucher/điều kiện; Admin xem nội dung rồi xác nhận. Backend kiểm tra lại preview, từ chối 409 nếu tập/voucher thay đổi.
- Tập ứng viên đóng băng lúc xác nhận; trước mỗi lượt giao kiểm tra lại tài khoản, marketing preference và điều kiện. Người mất điều kiện bị bỏ qua, không được cấp mã. Customer mới đủ điều kiện sau lúc chốt không tự được thêm vào chiến dịch đã xác nhận.
- Nếu voucher có audience selected/new/giới hạn cá nhân cũ, preview giao điều kiện chiến dịch với điều kiện voucher đó.
- Voucher gắn chiến dịch trở thành mã phát riêng. Không chuyển voucher công khai đã có lịch sử dùng thành mã riêng. Không xóa voucher có lịch sử chiến dịch.
- Campaign request_key + fingerprint chống gửi lại cùng request; recipients, notifications và grants có unique key chống trùng.
- Hàng đợi bền vững trong campaign_recipients; worker xử lý tối đa 100 lượt/lô, khóa từng recipient trong transaction, lỗi thử lại tối đa 3 lần. Có lịch sử số ứng viên, đã giao, chờ, lỗi, bỏ qua.
- Lệnh sofep:deliver-campaigns và scheduler mỗi phút đã có trong source. UAT có worker riêng kiểm tra mỗi 10 giây; cũng có nút Admin giao một lô đến hạn. Không dùng email/SMS cho chiến dịch.

### Thông báo và Ví ưu đãi

- Chuông Header có badge chưa đọc, đồng bộ API định kỳ và khi đọc/đăng nhập/đăng xuất; bỏ response của phiên cũ, nghe storage event giữa các tab.
- Customer chỉ xem thông báo/ví của mình; bộ lọc tất cả/chưa đọc/khuyến mãi-voucher/hệ thống, đánh dấu từng thông báo hoặc tất cả đã đọc, trạng thái lưu ở backend.
- Có loading/empty/error, phân trang và nút làm mới. Đăng nhập từ trung tâm quay lại đúng trang, tách khỏi cờ quay lại checkout.
- Lưu mã một lần bằng unique key, không tiêu thụ lượt. Voucher riêng chỉ lưu/áp dụng nếu có grant của chính Customer và còn đủ điều kiện.
- Quyền nhận mã, lưu mã và dùng mã tách biệt. Grant giữ điều kiện lúc giao; tùy chọn recheck bắt buộc kiểm tra lại điều kiện ở lúc lưu/checkout. Các điều kiện nền của voucher vẫn luôn được kiểm tra.
- Ví có nhóm có thể dùng/chưa đủ điều kiện/đã sử dụng/hết hạn; bộ lọc hiển thị trong trang hiện tại của ví phân trang. Voucher nhiều lượt còn khả dụng vẫn ở nhóm có thể dùng cho đến khi hết giới hạn.
- Hiển thị mức giảm, trần giảm, đơn tối thiểu, hạn dùng, lượt tổng/cá nhân và điều kiện được cấp; không trả danh sách Customer khác.

### Checkout

- Chọn mã từ ví ngay trong CheckoutModal hoặc chọn từ trang ví; giữ giỏ, variant ID và số lượng.
- Dòng kiểm tra mã xanh/đỏ nêu lý do và giá trị giảm. Đổi mã hoặc giỏ làm vô hiệu quote cũ, chặn response cũ ghi đè.
- Server luôn xác thực Customer, grant, thời hạn, đối tượng, giới hạn, đơn tối thiểu và tính lại tiền cuối cùng. Guest không dùng mã riêng.
- Giữ transaction/idempotency/khóa tồn kho của CheckoutService và hoàn quota một lần của OrderLifecycle. Lưu mã và nhận thông báo không thay tồn kho/quota. Luồng Guest → Login/Register → Checkout giữ nguyên.

## Audit schema và migration

Trước triển khai, database chính chỉ có danh_gia_san_phams và trait Notifiable, các placeholder menu; chưa có schema góp ý private, hộp thư Customer, campaign hay saved voucher. Vì vậy tái sử dụng auth, Customer, DonHang, MaGiamGia và checkout hiện có; bổ sung module lưu trữ độc lập.

Migration mới:
`themmoik27be/database/migrations/2026_11_06_000001_create_customer_engagement_tables.php`

Tạo 8 bảng: private_feedback, feedback_attachments, customer_preferences, customer_campaigns, campaign_recipients, customer_notifications, voucher_grants, saved_vouchers. Thêm campaign_restricted và mo_ta vào ma_giam_gias. Có down() và test rollback trong SQLite cô lập, không chạy rollback ở database chính/UAT hiện có.

Migration phụ thuộc đã có trong source:
`themmoik27be/database/migrations/2026_11_03_000001_add_voucher_eligibility_and_order_reservations.php`

Migration này thêm doi_tuong/gioi_han_moi_khach/ids_khach_hang vào voucher, cờ giữ kho/giữ voucher và checkout_fingerprint vào đơn, cùng index tra cứu. UAT đã có phần phụ thuộc; database chính hiện **chưa có cả hai nhóm cột**. Migration mới từ chối chạy nếu thiếu phần phụ thuộc.

Chỉ migration mới đã được áp dụng trên sofep_uat_20261103_0552eb8e0b6d qua PrepareEngagementUat.php có kiểm tra manifest, tên database, host loopback và config cache. Script từ chối tái tạo module nếu bảng đã tồn tại. Không chạy migrate:fresh, seed, truncate, xóa hoặc tạo lại database. Không sửa dữ liệu Seller lịch sử.

**Chưa áp dụng migration nào lên doan_nhom_5.** Muốn bật trên 5173 cần phê duyệt riêng hai migration trên sau khi sao lưu schema/dữ liệu chính và kiểm tra lịch sử migration. Không chạy bulk migration khác ngoài phạm vi. Rollback schema mới sẽ mất dữ liệu các bảng mới nếu không sao lưu; file private không được tự xóa.

## File tạo/sửa trong nhiệm vụ

Frontend mới:
- Fe/src/components/Engagement/FeedbackPage.vue
- Fe/src/components/Engagement/AdminFeedback.vue
- Fe/src/components/Engagement/AdminCampaigns.vue
- Fe/src/components/Engagement/CampaignVoucherForm.vue
- Fe/src/components/Engagement/CustomerCenter.vue
- Fe/src/components/Engagement/NotificationBell.vue
- Fe/src/services/engagement.js
- Fe/src/assets/css/engagement.css
- Fe/tests/customer-engagement.test.mjs

Frontend mở rộng:
- Fe/src/App.vue: banner chỉ cho tiến trình VITE_ENGAGEMENT_UAT=true.
- Fe/src/router/index.js: route mới và alias.
- Fe/src/layout/wrapper/ClientLayout.vue: menu, chuông, ví, Header mobile.
- Fe/src/layout/components/MenuRocker.vue: menu Admin mới; giữ đánh giá lịch sử.
- Fe/src/services/adminSearch.js: tìm chức năng góp ý/chiến dịch.
- Fe/src/services/authSessions.js: báo sự kiện thay đổi phiên cho chuông, giữ tách Customer/Admin.
- Fe/src/components/DangNhap/index.vue: quay lại trung tâm sau login.
- Fe/src/components/Client/CheckoutModal.vue: chọn voucher từ ví, thông báo giảm tiền.
- Fe/src/components/Client/ChiTietSanPham.vue: chuyển trình bày đánh giá công khai sang góp ý riêng, giữ gallery/biến thể.
- Fe/src/stores/cartStore.js: ý định chọn voucher, không chứa token.
- Fe/tests/admin-login.test.mjs và Fe/tests/customer-header.test.mjs: cập nhật hồi quy và mock.

Backend mới:
- app/Http/Controllers/FeedbackController.php
- app/Http/Controllers/CustomerInboxController.php
- app/Http/Controllers/Admin/CustomerCampaignController.php
- app/Http/Requests/CampaignRequest.php
- app/Http/Middleware/EngagementReady.php
- app/Services/LoyaltyAudience.php
- app/Services/CustomerCampaigns.php
- app/Services/VoucherWallet.php
- config/engagement.php
- database/migrations/2026_11_06_000001_create_customer_engagement_tables.php
- tests/Feature/CustomerEngagementTest.php
- tests/Support/PrepareEngagementUat.php
- tests/Support/EngagementUatRouter.php
- tests/Support/EngagementUatWorker.php

Backend mở rộng (đều trong themmoik27be):
- app/Models/MaGiamGia.php: grants, điều kiện nền, ngày hiệu lực giờ Việt Nam.
- app/Http/Requests/MaGiamGiaRequest.php: mô tả voucher khi schema hỗ trợ.
- app/Http/Controllers/Admin/MaGiamGiaController.php: giữ lịch sử campaign khi xóa mã.
- app/Providers/AppServiceProvider.php: rate limiter góp ý.
- routes/api.php và routes/console.php: API, command/scheduler.

Tài liệu/bằng chứng mới:
- docs/sofep-customer-engagement-uat.mjs + .json
- docs/sofep-engagement-main-check.json
- docs/SOFEP_CUSTOMER_ENGAGEMENT_HANDOFF.md

Các thay đổi checkout/cấu hình UI từ lượt trước được giữ nguyên; không commit/push/deploy, không đổi dependency/lockfile/.env.

## API và bảo mật

- Public POST /api/client/gop-y: readiness + rate limit + optional Sanctum Customer; token sai bị từ chối, không tin ID tài khoản client.
- /api/admin/gop-y và các route chi tiết/ảnh/cập nhật/phản hồi: auth:sanctum + admin, sau đó readiness.
- /api/admin/chien-dich, /xem-truoc, /giao-lo: cùng quyền Admin.
- /api/client/thong-bao, /count, /doc-tat-ca, /{id}/da-doc; /tuy-chon-thong-bao; /vi-uu-dai và /{id}/luu: auth:sanctum + RequireCustomer + readiness.
- Ownership lấy từ phiên server; không cho Customer xem/thay đổi hộp thư/ví/ảnh của người khác. Customer/Admin/Seller bị phân tách theo middleware hiện có.
- Private attachment không thuộc public storage, không có token trong URL; không có API công khai góp ý.

## Kết quả kiểm thử

| Kiểm tra | Kết quả |
|---|---|
| node --test tests/*.test.mjs trong Fe | 59/59 đạt |
| npm run build trong Fe | Đạt |
| php -d extension=pdo_sqlite vendor/bin/phpunit --no-progress | 72 tests: 70 đạt, 2 exporter tùy chọn bỏ qua; 1183 assertions |
| php -l source PHP sửa/tạo | 28 file đạt; FeedbackController kiểm tra lại sau cập nhật điện thoại: đạt |
| git diff --check | Đạt |
| HTTP MySQL UAT | Đạt chuỗi góp ý/ảnh/Admin/preview/giao/retry/grant/ví/checkout/hủy/đọc |
| Browser UAT | Guest gửi thành công, form và Header ở 1440/768/390px; Guest vào thông báo thấy yêu cầu đăng nhập |
| Kiểm tra lại chạy ngày 07/11 | Website chính 168 sản phẩm, UAT 167, ảnh UAT HTTP 200, worker --once kết nối và trả delivered=0 |

Backend regression kiểm tra spam, file sai định dạng/số lượng, privacy, Admin/Seller/Guest, ownership, kỳ tuần/tháng/năm và ranh giới giờ Việt Nam, preview drift, retry, lịch gửi, opt-out trước giao, mất điều kiện trước giao, giới hạn voucher và grant, lưu một lần, quota không giảm lúc lưu, giá server, hủy hoàn lượt đúng một lần, rollback giữ catalog.

Báo cáo HTTP UAT cuối ngày 06/11: campaign ID 3, order ID 9 (đã hủy trong UAT), giảm 22.000đ, tổng server 228.000đ; preview 1 người; ảnh catalog origin 8016 hợp lệ; không email/SMS thật. Chạy test nhiều lần chỉ tạo dữ liệu kiểm thử trong UAT và giữ lịch sử.

## Hướng dẫn demo Product Owner

1. Mở cổng 5180; kiểm tra banner UAT. Không dùng 5173 để thử thao tác ghi của module chưa migrate.
2. Admin: tài khoản `uat-admin@sofep.example.test`; mật khẩu nằm trường password trong file session UAT cũ: C:/Users/dn133/AppData/Local/Temp/sofep_uat_20261103_0552eb8e0b6d/session.json. Không đưa mật khẩu vào Git/báo cáo.
3. Customer đủ điều kiện và Customer đối chứng nằm trong accounts.eligible/accounts.other của file cùng thư mục `engagement-session.json`; password của file đó chỉ dành cho hai Customer demo. Mở file tại máy để xem, không chia sẻ ra ngoài.
4. Đăng nhập Admin → /admin/thong-bao → Tạo voucher mới: ví dụ mã riêng, giảm 10%, trần 50.000đ, đơn tối thiểu 50.000đ, tổng 10 lượt, mỗi Customer 1 lượt, ngày hợp lệ.
5. Chọn loại Voucher, liên kết mã mới. Kỳ Tất cả thời gian, số đơn hoàn tất ≥2 AND thực chi ≥2.000.000đ. Customer eligible có ba đơn lịch sử UAT tổng 3 triệu đồng; đối chứng không có các đơn này.
6. Bấm Xem trước → kiểm tra số người/nội dung/điều kiện → Tiếp tục xác nhận → Xác nhận xếp hàng gửi. Có thể đặt lịch tương lai theo giờ Việt Nam.
7. Worker UAT đang chạy mỗi 10 giây; có thể dùng nút Giao một lô đến hạn để xử lý ngay. Bấm Làm mới để xem delivered/failed/skipped; thời điểm tương lai chưa đến sẽ không được giao.
8. Ở tab khác, đăng nhập Customer eligible → /thong-bao → Làm mới → mở thông báo, Lưu mã. Đăng nhập Customer đối chứng để xác nhận không nhận/dùng được mã riêng. Phiên Admin và Customer được lưu tách vai trò.
9. Customer → /vi-uu-dai → xem điều kiện/hạn dùng → thêm sản phẩm/biến thể vào giỏ → Chọn mã để thanh toán, hoặc chọn mã trong dropdown ví tại checkout → Kiểm tra mã và tổng tiền. Dòng xanh hiển thị mức giảm từ server; mã lỗi hiện đỏ với lý do.
10. Góp ý Guest ở /gop-y hoặc gửi với Customer; Admin /admin/gop-y xem ảnh, đổi trạng thái/ghi chú. Với góp ý Customer, gửi phản hồi riêng vào hộp thư; Guest chưa có hộp thư không được báo gửi in-app giả.

## Khởi động lại trên Windows

MySQL Laragon phải chạy với cấu hình/data directory hiện có. Khi tiếp tục ngày 07/11, đã xác minh và khởi động đúng mysqld của C:/laragon/bin/mysql/mysql-8.4.3-winx64/my.ini, datadir C:/laragon/data/mysql-8.4 chứa cả hai database SOFEP hiện hữu. Không initialize, không tạo database, không thay file cấu hình, không truy vấn database Barber.

Các lệnh sau chạy ở các PowerShell riêng; không ghi .env:

```powershell
cd D:/do_an_nhom/code_chinh/DoAnNhom-5-GITHUB/themmoik27be
$env:SOFEP_MYSQL_UAT_SESSION = 'C:/Users/dn133/AppData/Local/Temp/sofep_uat_20261103_0552eb8e0b6d/session.json'
php -S 127.0.0.1:8016 tests/Support/EngagementUatRouter.php
```

```powershell
cd D:/do_an_nhom/code_chinh/DoAnNhom-5-GITHUB/Fe
$env:VITE_API_URL = 'http://127.0.0.1:8016/api'
$env:VITE_ENGAGEMENT_UAT = 'true'
npm run dev -- --host 127.0.0.1 --port 5180 --strictPort
```

```powershell
cd D:/do_an_nhom/code_chinh/DoAnNhom-5-GITHUB/themmoik27be
php tests/Support/EngagementUatWorker.php C:/Users/dn133/AppData/Local/Temp/sofep_uat_20261103_0552eb8e0b6d/session.json
```

Worker có --once để chỉ giao một lô. Dừng bằng Ctrl+C khi xong. Router UAT đổi origin ảnh 8012 cũ sang origin 8016 trong response catalog, không sửa database/ảnh, không tác động API chính.

Website chính dùng API 8000 và Vite 5173 với VITE_API_URL=http://127.0.0.1:8000/api, không đặt VITE_ENGAGEMENT_UAT=true. Không chạy lại PrepareEngagementUat.php để tạo lại module đã có; không chạy migrate:fresh/seed/reset.

## Chưa nghiệm thu / cần phê duyệt riêng

- Chưa migrate database chính; để bật module trên 5173 cần phê duyệt riêng hai migration đã liệt kê, có backup trước triển khai. Theo mục F.5 của yêu cầu, không tự thực hiện bước này.
- Chưa bật/xác minh email phản hồi và không gửi marketing email/SMS. config/engagement.php mặc định false; backend còn kiểm tra transport được cho phép. Guest chỉ có contact chưa có hộp thư cần kênh liên hệ đã xác minh.
- Luồng Admin/Customer đầy đủ đã kiểm tra bằng HTTP và unit/integration, chưa nghiệm thu bằng browser sau đăng nhập các tài khoản; browser đã kiểm tra Guest và responsive của form/trung tâm chưa đăng nhập.
- Chưa chạy stress nhiều worker/tải lớn; khóa/unique/retry đã kiểm tra ở SQLite và MySQL UAT. Chưa chứng minh hiệu năng ở quy mô lớn.
- Ví phân trang 20 mã, bộ lọc trạng thái áp dụng các mã ở trang hiện tại. Cần xem trang khác nếu có nhiều hơn 20 mã đã lưu.
- Điều kiện danh mục/sản phẩm của voucher chưa có trong hệ thống cũ và chưa được bổ sung; phần yêu cầu này là tùy hỗ trợ. Điều kiện Customer, tiền đơn, thời hạn và lượt đã có.
- Kỳ loyalty dựa ngày tạo đơn đã hoàn tất, không phải thời điểm giao thực tế vì schema cũ chưa có mốc hoàn tất đáng tin cậy. Hoàn tiền dùng trạng thái hiện có; chưa có mô hình hoàn tiền một phần.
- Không commit, push, deploy hoặc thay secrets; không sửa database chính/Barber hoặc xóa lịch sử.
