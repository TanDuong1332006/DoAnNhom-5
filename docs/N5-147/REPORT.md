# N5-147 — Gia cố login, OTP, token và kiểm chứng migration

Ngày 18/11/2026. Nhánh `nhanh-cua-duong`. Đã đọc trực tiếp Jira N5-147 và đối chiếu F01/F02/F03 trong `docs/audit-2026-11-18/REPORT.md`. Không tự đổi trạng thái, assignee hoặc priority của Jira; PM đánh giá trước Done.

**Cập nhật nghiệm thu sau bản vá:** Jira đã được PM chuyển **In Progress**. Đã kiểm tra UI desktop/mobile với mailbox giả lập và cache chính chỉ đọc; xem mục **“Nghiệm thu sau bản vá — 18/11/2026”** cuối báo cáo. Những dòng “chưa browser UAT” trong phần triển khai bên dưới là trạng thái trước lượt nghiệm thu này. Kết luận hiện tại: giữ In Progress để PM xác nhận bước lưu mật khẩu mới trên UI; email thật là **Pending Real Email UAT**, không tự gửi ra ngoài.

## Kết quả

- Đã bổ sung giới hạn login Customer/Admin, đăng ký, xác thực OTP và gửi lại OTP reset. Các alias login dùng chung hạn mức; verify/reset dùng chung hạn mức đoán mã.
- OTP reset sinh bằng `random_int`, cache chỉ lưu hash có thời hạn và dấu vết mật khẩu hiện hành, không lưu plaintext mới. Mã hết hạn, đã dùng hoặc bị thay bằng mã gửi lại không được reset mật khẩu.
- Reset thành công thay mật khẩu và thu hồi toàn bộ Sanctum token của đúng Customer trong transaction. Token Admin cùng ID và Customer khác được giữ. Reset sai không đổi mật khẩu hoặc thu hồi phiên hợp lệ.
- Login và reset khóa cùng dòng Customer để tránh cấp token từ hash cũ sau khi reset đã commit. Xác thực OTP đăng ký cũng có transaction/row lock; chặn ngay tại mốc hết hạn, không chỉ sau mốc.
- Bổ sung migration `role` có điều kiện; **26 migration chạy thành công từ database MySQL UAT rỗng**, không vá schema bằng tay trong runner mới.
- PHPUnit cuối: **83 bài, 81 đạt, 2 exporter tùy chọn bỏ qua, 1.304 assertions**. Frontend **63/63 đạt**; build đạt; lint **10 file PHP đạt**. MySQL UAT sau sửa harness: **45 kiểm tra đạt** trên MySQL **8.4.3**.

## Nguyên nhân đã xác minh và bản vá

| Phát hiện | Trước | Sau / bằng chứng |
|---|---|---|
| Audit F01 — brute-force login/OTP | Các route auth không có throttle; đổi alias hoặc dùng trực tiếp reset có thể bỏ qua bước UI | `AppServiceProvider` khai báo 5 limiter, `routes/api.php` gắn đúng route; test alias, IP, shared verify/reset budget, thời gian chờ và 429 |
| Audit F02 — token sau reset | Controller chỉ lưu mật khẩu và xóa mã cache, token cũ còn hợp lệ | Reset + delete tokens theo morph relation trong cùng transaction; token cũ trả 401, Admin/Customer khác vẫn dùng được |
| OTP reset cache / replay | `rand`, cache mã plaintext, hai request có thể cùng đọc mã trước khi xóa | Service `PasswordResetOtp`: HMAC cache key, Hash::make, expires_at, credential binding, cache lock dùng chung send/verify/reset; consumed code bị từ chối |
| OTP đăng ký đúng mốc hết hạn | Điều kiện `greaterThan` cho phép tại đúng thời điểm expiry | `greaterThanOrEqualTo`; test đúng mốc/hết hạn/thử sai/thành công/replay |
| Audit F03 — thiếu baseline role | Auth tạo role nhưng migrations không có cột; harness cũ tự thêm role che lỗi | Migration mới thêm `varchar(20) default customer` khi chưa có; migration thật SQLite và MySQL + đăng ký hoạt động |
| FE quên mật khẩu không theo UAT base | Hardcode `localhost:8000`, không khớp URL legacy mà interceptor đang chuyển | Dùng VITE_API_URL hoặc fallback `127.0.0.1:8000/api`; test gửi đúng API UAT |
| Phiên local sau reset | Browser có thể giữ token Customer đã bị server thu hồi | Thành công xóa phiên Customer cùng email; giữ Admin, giỏ và phiên tài khoản khác; lỗi 429 giữ dữ liệu để thử lại |

Không thêm endpoint hoặc đổi payload thành công. Mã trạng thái cũ được giữ cho luồng thông thường; **429 + Retry-After** là hành vi gia cố được yêu cầu trong N5-147. Không đổi cơ chế Sanctum sang JWT/OAuth.

## Chính sách giới hạn triển khai

Hạn mức đếm **tổng yêu cầu**, bao gồm thành công, sai và validation; không phải chỉ đếm sai mật khẩu. Cách này giới hạn cả tải xác thực/gửi mail. Email dùng trim/lowercase khi lập bucket, key được HMAC; bucket account không phụ thuộc IP để giảm việc đổi IP, đồng thời có bucket IP để giảm việc đổi email.

| Nhóm | Theo tài khoản | Theo IP | Dùng chung |
|---|---|---|---|
| Login | 10/phút | 30/phút | `/dang-nhap`, `/admin-login`, `/auth/admin-login` |
| Đăng ký | 3/giờ | 10/giờ | `/dang-ky` |
| OTP đăng ký | 5/10 phút | 30/10 phút | `/xac-thuc-otp` |
| Gửi/gửi lại OTP reset | 1/phút và 3/giờ | 10/giờ | `/forgot-password/send-otp` |
| Kiểm tra/reset bằng OTP | 5/5 phút | 30/5 phút | `/forgot-password/verify-otp` và `/forgot-password/reset-password` |

Gửi lại reset OTP không xóa quota đoán mã; mã cũ mất hiệu lực khi bản ghi được thay thế. Thông báo 429 có `message` cho các form hiện hữu; FE không tự retry. Hạn mức dùng cache chia sẻ của ứng dụng, cần cache/lock backend hoạt động; chưa chứng minh chống DDoS hoặc tải phân tán lớn.

Hệ thống hiện **không có endpoint gửi lại OTP đăng ký**; bản vá không tự thêm chức năng/API đó. Route đăng ký và OTP hiện hữu đã bị giới hạn, nhưng UX khôi phục đăng ký khi mã hết hạn vẫn là hạn chế cũ cần PM xác định phạm vi tiếp theo. Đã kiểm tra expiry và replay; không báo đã giải quyết hoàn chỉnh UX resend đăng ký.

## Chính sách token và phạm vi

- Cơ chế đang hoạt động là Sanctum Bearer personal access token. Thành công reset Customer → thu hồi mọi token của Customer đó → người dùng đăng nhập lại; không tự cấp token mới từ reset.
- Transaction bao gồm đổi hash và xóa token; row lock ở login và reset tuần tự hóa thao tác trên cùng Customer. Cache lock 30 giây, chờ tối đa 3 giây; không lấy được lock trả 429. Có test giữ lock để xác minh bị chặn, chưa có stress nhiều tiến trình reset đồng thời.
- Mã reset gắn với hash mật khẩu lúc cấp; ngay cả khi xóa cache sau commit gặp lỗi, mã cũ không khớp credential mới. OTP plaintext format cũ không được chấp nhận sau bản vá.
- Customer không vào Admin, Guest không tạo đơn, Seller legacy bị từ chối theo middleware hiện có: đã hồi quy, không phải chức năng phân quyền mới được viết trong task này.
- Không có endpoint đổi mật khẩu sau đăng nhập hoặc reset Admin đang nối route; không tự thêm. Không tuyên bố đã triển khai thu hồi cookie session/remember-me cho luồng chưa tồn tại. `sanctum.expiration = null` vẫn giữ nguyên vì task chưa định nghĩa thời hạn token mới; token có expires_at, token giả, token bị thu hồi vẫn được kiểm tra bằng test.

## Migration và dữ liệu

Migration mới chỉ thêm `khach_hangs.role` nếu thiếu. Không đổi role, default hay kiểu của cột đã có. `down()` cố ý không xóa cột vì schema legacy có thể đã có role trước migration; đây là migration **forward-only**, không được kỳ vọng rollback để bỏ phân quyền. Có test up/down trên schema đã có role giữ nguyên role seller của fixture.

Runner mới `tests/Support/MySqlSecurityUat.php`:

1. Chỉ CLI, từ chối cached config, kiểm tra môi trường MySQL local loopback.
2. Kết nối server không chọn database nghiệp vụ, tạo tên ngẫu nhiên mới `sofep_security_uat_YYYYMMDD_<12 hex>` bằng CREATE không IF EXISTS.
3. Đặt connection mặc định, cache và lock về UAT **sau LoadConfiguration, trước boot provider**; session/mail/queue cô lập trong tiến trình, không sửa `.env`.
4. Guard kiểm tra database thực tế, connection của cache trong limiter và lock cùng trỏ đúng UAT trước request.
5. Chạy `migrate --database=security_uat --force`, không migrate:fresh/seed/reset; chạy lại migrate để xác nhận không có việc mới.
6. Tạo tài khoản fixture với mật khẩu ngẫu nhiên trong bộ nhớ; Mail fake, không lưu token/mật khẩu vào báo cáo.

DB UAT đạt: `sofep_security_uat_20261118_d588ff07a906`, giữ lại để review. DB UAT lượt đầu: `sofep_security_uat_20261118_c2866b3d1514`, cũng giữ nguyên. Không xóa UAT tự động.

Kết quả chứng minh **schema cho chức năng hiện hành** dựng được và đăng ký/auth hoạt động; không chứng minh tái tạo dữ liệu catalog/ảnh, restore backup, seeder hoặc schema lịch sử giống main từng cột. Không tạo lại `cua_hangs`/Merchant đã loại khỏi route; quan hệ model legacy vẫn tồn tại và không được coi là chức năng đã phục hồi. Không áp dụng migration mới lên `doan_nhom_5` trong task này.

### Sự cố harness ở lượt đầu — cần giữ trong hồ sơ

Lượt đầu boot Laravel trước khi đổi connection/cache sang UAT. Do `AppServiceProvider` đã resolve RateLimiter, DatabaseStore của limiter giữ connection `doan_nhom_5`; request thử đã ghi **cache runtime rate-limit** của main. Kiểm tra đối tượng cache không cần truy vấn đã xác nhận kiểu DatabaseStore và tên database này. Dữ liệu tài khoản/OTP trong DB và migration của lượt đó trỏ DB UAT mới, nhưng không thể mô tả lượt đầu là hoàn toàn không ghi main.

Không chạy migrate/reset/seed, không đổi tài khoản hoặc dữ liệu nghiệp vụ của main; không chọn database N.Cảnh Barber. Không tự dọn/xóa cache chính để tránh thêm thao tác ngoài phạm vi. Bộ đếm có TTL theo limiter; không có token/mật khẩu thật được dùng trong request thử. Chưa chạy đối chiếu hash toàn bộ main để khẳng định độc lập không có thay đổi nào khác; giới hạn này được ghi nhận thay vì tuyên bố main bất biến tuyệt đối.

Harness đã sửa cấu hình **trước boot provider**, thêm guard cache/lock và chạy lại trên DB UAT khác đạt 45 kiểm tra. Bằng chứng lượt đầu được giữ trong `mysql-uat-initial-failed.json`; đồng thời lượt đầu có lỗi assertion mong 429 ở lần thứ năm thay vì lần thứ sáu. Đây là lỗi đếm trong runner, đã sửa, không thay ngưỡng ứng dụng để làm test đạt.

## Kiểm thử và bằng chứng

| Lệnh | Kết quả cuối | Bằng chứng / giới hạn |
|---|---|---|
| `php vendor/bin/phpunit --no-progress --do-not-cache-result` tại BE | 83 bài: 81 đạt, 2 bỏ qua; 1.304 assertions | `phpunit.log`; SQLite :memory: có guard; 2 exporter tùy chọn không có SOFEP_UAT_DIR |
| `node --test tests/*.test.mjs` tại Fe | 63 đạt, 0 lỗi | Component/VM/mocked API; không thay browser E2E |
| `npm run build -- --outDir ../docs/N5-147/build-check` tại Fe | Đạt, 168 modules, Vite 4.4.8 | Không ghi đè Fe/dist hiện có; output kiểm chứng giữ trong build-check |
| `php -l` 10 file PHP sửa/tạo | 10/10 đạt | `php-lint.json` |
| `php tests/Support/MySqlSecurityUat.php` tại BE | Sau sửa harness: 45 checks đạt, 26 migrations, MySQL 8.4.3 | `mysql-uat.json`; qua Laravel HTTP kernel, không qua browser hoặc socket HTTP server |
| `git diff --check` | Đạt, có warning CRLF file hiện có | Không format các file ngoài task |

Các test mới gồm account/IP quota, alias login, OTP budget và expiry đúng mốc, resend/hour quota, gửi lại không reset budget, hash OTP, mã cũ/replay, revoke nhiều token, giữ Admin/Customer khác, lock conflict, migration thật và đăng ký trên schema mới. Test cũ toàn bộ route Admin/checkout/Seller legacy vẫn đạt.

| Ca tiêu biểu trên MySQL UAT đạt | Status |
|---|---:|
| Chưa xác thực email → login | 403 |
| Customer vào Admin | 403 |
| Admin vào danh mục | 200 |
| Guest tạo đơn | 401 |
| Token giả, hết hạn, token Customer cũ sau reset | 401 |
| Vượt cooldown gửi OTP, budget verify/reset hoặc login alias | 429 + Retry-After |
| Mã reset sai/hết hạn/dùng lại | 400 |
| Mã đăng ký sai/dùng lại | 422 |
| Mã đúng và reset đúng | 200 |

## Danh sách file thay đổi trong N5-147

Đây là phần thay đổi của task, không nhận các diff có sẵn là do task này tạo.

| File | Thay đổi |
|---|---|
| `themmoik27be/app/Providers/AppServiceProvider.php` | Sửa: thêm limiter auth, giữ limiter feedback có sẵn |
| `themmoik27be/routes/api.php` | Sửa: gắn throttle đúng route/alias, giữ middleware phân quyền/engagement có sẵn |
| `themmoik27be/app/Http/Controllers/AuthController.php` | Sửa: transaction/row lock login và OTP, expiry >= |
| `themmoik27be/app/Http/Controllers/Api/ForgotPasswordController.php` | Sửa: dùng service OTP, reset transaction + token revocation |
| `themmoik27be/app/Services/PasswordResetOtp.php` | Tạo: hash/TTL/credential binding/cache lock và invalidate |
| `themmoik27be/database/migrations/2026_11_18_000001_add_customer_role_baseline.php` | Tạo: bổ sung role khi thiếu; chưa chạy trên main |
| `themmoik27be/tests/Feature/ApiAccessBoundaryTest.php` | Sửa: lấy OTP từ mail fake thay vì đọc plaintext cache cũ |
| `themmoik27be/tests/Feature/AuthSecurityTest.php` | Tạo: 10 test bảo mật xác thực/OTP/token/limiter |
| `themmoik27be/tests/Feature/MigrationBaselineTest.php` | Tạo: schema từ migrations thật + guard bộ nhớ |
| `themmoik27be/tests/Support/MySqlSecurityUat.php` | Tạo: runner MySQL UAT có guard trước provider boot |
| `Fe/src/components/QuenMatKhau/index.vue` | Sửa: API base đúng môi trường, clear phiên Customer đúng email khi reset thành công |
| `Fe/tests/admin-login.test.mjs` | Sửa: thêm case 429, giữ các test checkout/inbox có sẵn |
| `Fe/tests/password-reset-security.test.mjs` | Tạo: API UAT, giữ Admin/cart/other session, phản hồi 429 |
| `docs/N5-147/` | Tạo báo cáo, JSON kết quả/hashes, log PHPUnit, output build kiểm chứng |

Không xóa file source; không sửa dependency/lockfile, `.env`/secrets hoặc cấu hình triển khai; không commit/push/deploy. `verification.json` ghi hash file và đối chiếu source ngoài phạm vi với snapshot audit để kiểm tra không ghi đè thay đổi có sẵn.

## Phần còn lại / giới hạn trước Done

1. PM review chính sách quota/thu hồi token, bản vá và sự cố harness; quyết định chấp thuận N5-147. Không tự chuyển Done.
2. Chưa browser UAT N5-147, chưa gửi email/OTP thật và chưa đo tải lớn/concurrency reset nhiều tiến trình; mail fake, lock contention và API/kernel chỉ là kiểm thử tự động.
3. Gửi lại OTP đăng ký cho account chưa kích hoạt vẫn chưa có luồng/API; không mở rộng tính năng mới trong task bảo mật này.
4. Reset/send-otp vẫn phân biệt email tồn tại theo hợp đồng cũ. Chưa đổi sang phản hồi chống enumeration để tránh thay nghiệp vụ/API ngoài bản vá; rate limiting giảm tần suất, không xóa hoàn toàn rủi ro đó.
5. Chưa thay token lifetime mặc định hoặc thêm reset Admin/đổi mật khẩu sau login; không nhận các luồng chưa có là đã hoàn thành.
6. Chưa triển khai migration/code lên môi trường chính. Mọi lần áp dụng migration main hoặc thay cấu hình cần kế hoạch/duyệt riêng; không chạy migrate mặc định trên main từ hướng dẫn UAT này.
7. Các vấn đề audit ngoài task như “Bán chạy” theo view, Customer profile/order history, refund và deployment vẫn giữ nguyên.

## Nghiệm thu sau bản vá — 18/11/2026

### Phạm vi và môi trường

Theo xác nhận bổ sung của PM, dùng **email giả lập**, không gửi Gmail/SMTP thật. Không sửa lại logic ứng dụng hoặc phát triển resend đăng ký. Tạo thêm harness nghiệm thu trong `tests/Support/` và bằng chứng trong thư mục này; hash 13 file source/test của bản vá trước giữ nguyên.

- FE: `http://127.0.0.1:5187`, chạy Vite với API base riêng trong tiến trình.
- BE: `http://127.0.0.1:8027`, router chỉ nhận loopback; không chạy API chính 8000.
- DB: `sofep_security_uat_20261118_d588ff07a906` đã có từ lượt migration đạt; không chạy lại migration/seeder, không tạo lại database.
- Desktop: viewport 1440×900. Mobile: viewport 390×844 trong browser, chưa phải điện thoại vật lý. Ví dụ checkout mobile có documentWidth = clientWidth = 375 px (phần còn lại dành cho scrollbar), dialog khoảng 375,2 px; không thấy tràn ngang trang ở phép đo này.
- Mail: Laravel **array transport**, vẫn đi qua render/gửi thư của Laravel rồi ghi MIME `.eml` vào mailbox local. Không dùng `Mail::fake()` cho đường đi trình duyệt. Event guard chỉ cho đích `@sofep.example.test`, mailer duy nhất được đăng ký là array; không có SMTP fallback.
- Mailbox/session fixture nằm dưới `%TEMP%/sofep-n5147-browser-9903d2c7/`, không đưa OTP hoặc mật khẩu vào artifact báo cáo. `simulated-email-evidence.json` chỉ chứa người nhận fixture, subject, timestamp và SHA-256 MIME; đã nhận **3 thư**: đăng ký desktop, đăng ký mobile, reset mật khẩu.

### Kết quả UI và API bổ sung

| Ca nghiệm thu | Kết quả | Bằng chứng và giới hạn |
|---|---|---|
| Desktop đăng ký Customer mới, nhận OTP | Passed — HTTP 201, form chuyển Xác Thực Email, có thư MIME | `browser-http-requests.json`, `simulated-email-evidence.json`; thư chỉ giao mailbox giả lập |
| Desktop OTP sai | Passed — HTTP 422, UI báo “Mã xác thực không chính xác.” | Hai lần thử qua UI; ảnh `browser-evidence/desktop-otp-wrong.jpg` ghi màn hình form; thông báo transient được quan sát qua UI |
| Desktop OTP đúng | Passed — HTTP 200, chuyển sang đăng nhập | Dùng mã thực sự lấy từ thư giả lập, không đặt lại hash mã của Customer desktop bằng fixture |
| OTP đăng ký đã dùng | Passed qua API — HTTP 422 | `browser-api-regression.jsonl`, case used_registration_otp; UI tự chuyển trang sau xác thực nên không gắn nhãn browser replay Passed |
| Mobile đăng ký mới | Passed — HTTP 201 và nhận thư MIME thứ hai | Tài khoản mobile riêng, form sử dụng được ở 390×844 |
| Mobile OTP hết hạn | Passed — HTTP 422, UI báo hết hạn | Chỉ đặt `otp_expires_at` của fixture mobile chưa kích hoạt về quá khứ trong UAT, không sửa mã. Không chờ tự nhiên 10 phút; `mobile-otp-expired.jpg` là form sau thử, còn message được quan sát qua AX/UI và request log |
| Thử sai login nhiều lần trên mobile | Passed — 10 lần HTTP 401 rồi HTTP 429 | 11 lần bấm submit thực qua UI, không prime quota bằng request API; `mobile-login-429.jpg` và request log; form vẫn ở đăng nhập |
| Mobile quên mật khẩu, nhận mã | Passed — HTTP 200, nhận MIME thứ ba | Mailbox giả lập và form chuyển bước nhập mã |
| Mobile mã reset sai | Passed — HTTP 400, lỗi được hiển thị trong form | Request log và quan sát DOM “Mã OTP không hợp lệ hoặc đã hết hạn.” |
| Mobile mã reset đúng | Passed — HTTP 200, mở màn hình “Tạo mật khẩu mới” | `mobile-reset-verified.jpg` |
| Nút LƯU MẬT KHẨU MỚI trên UI | **Pending Manual UI Reset Submit** | Không nhập mật khẩu mới hoặc bấm lưu bằng công cụ trình duyệt: quy tắc browser yêu cầu người dùng thực hiện thay đổi credential. Không đánh đồng với API reset Passed |
| Reset cuối bằng API UAT | Passed — HTTP 200 | `SecurityBrowserControl.php reset-and-regression`, dùng mã từ cùng thư đã nhận; không tạo endpoint đổi mật khẩu mới |
| Token cũ sau reset | Passed — API 401; UI trở về trạng thái cần đăng nhập Customer | `browser-api-regression.jsonl` và `mobile-old-session-rejected.jpg`; token cũ của browser cũng bị API phiên từ chối |
| Login lại bằng mật khẩu sau reset trên mobile | Passed — HTTP 200, vào trung tâm thông báo | Dùng credential đã đổi qua API, UI hiển thị đúng tên Customer |
| Guest mở checkout | Passed — UI yêu cầu đăng nhập, giữ giỏ | `desktop-guest-checkout.jpg`; chưa đăng nhập không tạo đơn |
| Guest gọi trực tiếp tạo đơn | Passed — API 401 | Case guest_checkout_api_denied |
| Customer truy cập Admin | Passed — UI chuyển về đăng nhập; API 403 | Hai lớp được kiểm tra riêng, không dùng route guard thay bằng chứng backend |
| Admin đăng nhập và xem đơn | Passed desktop/mobile — API 200 | `desktop-admin-orders.jpg`, `mobile-admin-orders.jpg`; phiên Admin vẫn dùng được sau reset Customer |
| Customer checkout desktop | Passed — tạo đơn, mô phỏng thất bại rồi thành công | Đơn ID 1, 280.000đ, MB_BANK, đã thanh toán mô phỏng; `desktop-checkout-paid.jpg` |
| Customer checkout mobile COD | Passed — tạo đơn và vẫn chưa thanh toán | Đơn ID 2, 280.000đ, COD; `mobile-checkout-form.jpg`, `mobile-checkout-cod.jpg`; tồn fixture 20 → 18 sau hai đơn |
| Email giao qua SMTP/Gmail thật | **Pending Real Email UAT** | PM cho phép dùng giả lập trong lượt này; không gửi thử ra đích ngoài UAT |

Các đơn chỉ là fixture, không giao hàng hoặc chuyển tiền thật. Không chạy lại toàn bộ PHPUnit/build vì source ứng dụng không thay đổi; kết quả 83 bài / 81 đạt / 2 skip và FE 63/63 của lượt trước vẫn được ghi là kết quả **trước lượt nghiệm thu**, không giả nhận là vừa chạy lại. Đã lint các harness PHP mới, kiểm tra hash và `git diff --check` trong lượt này.

Console của tab lúc kiểm tra Admin mobile không có error/warn được ghi nhận tại thời điểm đọc; không chứng nhận mọi trang không có lỗi console. Chưa stress nhiều tiến trình hoặc đo hiệu năng production.

### Cache chính: xác định bộ đếm và ảnh hưởng

`SecurityCacheInspection.php` nạp cấu hình nhưng chuyển cache sang array trước provider boot, rồi dùng một PDO riêng với **START TRANSACTION READ ONLY**. Chỉ SELECT các khóa được tính từ định danh fixture của lượt lỗi; không gọi Cache::get vì Laravel DatabaseStore có thể xóa bản ghi hết hạn ngay khi đọc. Không gửi login/OTP request tới main để “thử ảnh hưởng”, vì chính request đó sẽ ghi bộ đếm.

Khóa được dựng theo đúng code limiter: HMAC định danh bằng app key trong bộ nhớ → bucket → MD5(limiter name + bucket) → cache prefix; không xuất app key hoặc giá trị OTP. Đã đối chiếu **26 khóa dự kiến**: 12 bộ đếm + 12 timer, 1 bản ghi OTP reset và 1 khóa OTP legacy. Có **24 dòng bộ đếm/timer** còn lưu; hai khóa OTP không có. Lock của fixture reset không còn tồn tại.

| Nhóm | Dòng còn lưu (counter + timer) | Giá trị counter | Dòng còn hiệu lực tại snapshot |
|---|---:|---|---:|
| auth-register | 4 | account 1, IP 1 | 4 |
| auth-register-otp | 4 | account 3, IP 3 | 0 |
| auth-login | 6 | Customer 4, Admin 1, IP 5 | 0 |
| auth-reset-send | 6 | cooldown account 1, hourly account 1, IP 1 | 4 |
| auth-reset-otp | 4 | account 5, IP 5 | 0 |

Tổng **8 dòng còn hiệu lực = 4 counter + 4 timer**, không phải 8 tài khoản bị khóa. Các dòng hourly còn hiệu lực có expiration khoảng **17:30:23–17:30:35 ngày 18/11/2026 giờ Việt Nam**. TTL là hiệu lực logic; dòng vật lý có thể còn trong bảng sau expiry cho đến lần cleanup bình thường. Audit này không kích hoạt cleanup.

Định danh bị tác động: Customer/Admin fixture của lượt đầu `customer-147@sofep.example.test`, `admin-147@sofep.example.test`, IP fixture `127.0.0.147`. Browser UAT mới dùng IP loopback `127.0.0.1`, account khác và DB/cache prefix riêng. Các bucket còn sống là đăng ký/gửi reset hourly của fixture, giá trị chỉ 1 và dưới ngưỡng; login/verify OTP của lượt lỗi đã hết hạn tại snapshot. Không thấy bằng chứng chúng đang chặn tài khoản thật hoặc login/OTP thông thường. Đây là đánh giá từ key/TTL/quota, không phải đăng nhập thử tài khoản thật trên main.

**Bổ sung phạm vi sự cố so với báo cáo đầu:** CacheManager giữ DatabaseStore theo tên driver. Trong harness cũ, RateLimiter và Cache::store() cùng dùng driver `database` đã được resolve trước đổi connection. Phân tích luồng đã thực thi cho thấy PasswordResetOtp cũng đi qua store main khi ghi/xóa bản ghi reset và acquire/release lock, không chỉ bộ đếm; đây là kết luận từ vòng đời driver và caller, chưa có log SQL để đối chiếu từng câu lệnh cũ. Hiện không còn bản ghi reset/lock fixture; không thể khôi phục toàn bộ lịch sử thao tác chỉ từ snapshot hiện tại. DatabaseLock còn có lottery dọn expired locks; không có log SQL lịch sử để xác định nhánh đó có chạy trong lượt lỗi hay không. Không suy diễn thành đã xóa lock đang hoạt động, cũng không khẳng định ảnh hưởng cũ chỉ có INSERT bộ đếm.

**Kết quả trước/sau lượt UI:** hash nội dung của toàn bộ 24 dòng được theo dõi **không đổi**; số dòng khớp giữ nguyên. Prefix của browser UAT mới có **0 dòng trên cache main**. Đã giữ `cache-main-before.json`, `cache-main-after.json`; không flush, delete, reset hoặc khôi phục cache chính.

**Xử lý thủ công:** hiện không cần xóa cache để mở khóa người dùng thật theo bằng chứng đang có. Đề nghị giữ nguyên và để TTL hết hiệu lực. Nếu PM muốn dọn dòng vật lý hoặc kiểm tra log SQL/backup để mở rộng điều tra, cần phê duyệt riêng theo danh sách khóa; không dùng cache:clear/flush hoặc xóa theo pattern rộng.

### Xác minh cache UAT đã cô lập

`SecurityBrowserBoot.php` đặt cấu hình sau LoadConfiguration và **trước boot providers**, bỏ tất cả connection khác khỏi config của tiến trình UAT, chỉ giữ `security_browser`. Guard kiểm tra cả default cache store, cache bên trong RateLimiter, lock connection, prefix và SELECT DATABASE() trước mỗi request. Mail chỉ array, session array, storage root riêng, logging null.

`SecurityBrowserEvidence.php` đã ghi một witness bằng Cache::put và một witness qua RateLimiter::hit, rồi SELECT kiểm tra chúng thực sự nằm trong UAT. `browser-isolation.json` xác nhận:

- default database, cache, limiter và lock đều là DB UAT đã chỉ định;
- chỉ có connection `security_browser` trong tiến trình;
- 31 dòng cache thuộc prefix UAT tại lúc kiểm tra, hai witness đều có trên UAT;
- prefix đó có 0 dòng trên main; các khóa main cũ có hash không đổi trước/sau.

Kết luận áp dụng cho **MySqlSecurityUat đã sửa và harness browser mới được sử dụng**. Các harness cũ như `MySqlUat.php` / `EngagementUatRouter.php` còn kiểu boot rồi mới đổi cấu hình; không dùng chúng cho N5-147 và chưa sửa ngoài phạm vi lượt nghiệm thu. Không tuyên bố toàn bộ script UAT lịch sử đều đã an toàn; cần rà/sửa riêng trước tái sử dụng.

### Đề xuất gửi lại OTP đăng ký — chưa triển khai

Hiện UI chỉ có “Quay lại đăng ký”; API đăng ký báo email đã sử dụng cho account chưa kích hoạt. Mã hết hạn khiến người dùng chưa có luồng tự phục hồi. Đây là khoảng trống UX đã tái hiện bằng mobile fixture, không được giải quyết bằng việc bỏ xác thực email hoặc cho phép login trước OTP.

Đề xuất tối thiểu để PM duyệt riêng:

1. Endpoint dự thảo `POST /api/gui-lai-otp-dang-ky`, request email; chỉ cấp lại cho Customer chưa kích hoạt, không khóa. Không đổi role, mật khẩu, thông tin hồ sơ hoặc tạo account trùng.
2. Mã 6 số từ random_int, hash bằng cơ chế hiện tại, hết hạn sau 10 phút; cấp mới vô hiệu mã cũ. Lock đúng Customer trong transaction để tránh hai mã cấp đồng thời.
3. Gửi tối đa 1 lần/phút, 3 lần/giờ/account và 10 lần/giờ/IP, cộng quota đăng ký hiện hữu nếu cần dùng chung ngân sách gửi thư; 429 có Retry-After. Resend không xóa budget nhập sai `auth-register-otp`.
4. Response mới không tiết lộ account tồn tại/đã kích hoạt; email không hợp lệ vẫn validation, abuse vẫn bị throttle. Không thêm marketing hoặc tự đăng nhập sau xác thực.
5. UI nút “Gửi lại mã” ở màn OTP, countdown theo server, khóa double-submit; không tự gửi khi refresh. Gửi lỗi không báo thành công giả, không reset quota để người dùng spam lại.
6. Acceptance: mã cũ/sai/hết hạn/đã dùng bị từ chối; mã mới đúng kích hoạt một lần; account active/khóa/Seller/Admin không được cấp lại; race/resend/quota nhiều IP, mail fail, mobile và privacy response có test.

Đây là **đề xuất hợp đồng API và thay đổi chức năng**, chưa sửa routes/component/schema hoặc tạo ticket. PM cần quyết định làm trong ticket tiếp theo hay bổ sung scope N5-147 trước triển khai; không dùng đề xuất này để coi resend đã hoàn thành.

### File bổ sung, trạng thái bàn giao và điều kiện đóng

File mới phục vụ nghiệm thu: `tests/Support/SecurityCacheInspection.php`, `SecurityBrowserBoot.php`, `SecurityBrowserUat.php`, `SecurityBrowserControl.php`, `SecurityBrowserEvidence.php`; JSON cache/isolation/mail/request evidence, `browser-api-regression.jsonl`, `browser-evidence/*.jpg`, `post-patch-uat.json` và bản cập nhật báo cáo. Không sửa 13 file app/test của bản vá trước, không sửa `.env`, dependency/lockfile, database chính hoặc N.Cảnh Barber. Không commit/push/deploy hay cập nhật Jira.

FE/BE loopback UAT giữ chạy ở 5187/8027 để PM xem; đây là môi trường nghiệm thu có mail giả lập, không phải production deployment. Tài khoản mobile hết hạn được giữ nguyên để review gap resend; hai đơn fixture và mailbox không bị xóa.

**Kết luận: chưa đề nghị đóng N5-147 ngay.** Phần bảo mật cốt lõi, regression, email giả lập và cô lập cache đã có bằng chứng đạt; còn bước **Manual UI Reset Submit** cần PM/người kiểm thử hoàn tất trực tiếp trên account UAT. Quy tắc công cụ browser yêu cầu người dùng tự nhập/xác nhận/thực hiện thay credential, nên lượt này dừng UI ở form mật khẩu mới và dùng API để kiểm chứng phần server. Frontend handler reset đã có test tự động, nhưng không thay cho thao tác UI còn thiếu.

Email thật giữ **Pending Real Email UAT** theo quyết định PM, không tự coi là thất bại của mail giả lập và không tự gửi thử. Sau khi PM xác nhận thao tác UI cuối, chấp thuận phạm vi email giả lập và quyết định xử lý/hoãn resend, có thể đánh giá đóng ticket theo AC; chưa được gọi toàn bộ sản phẩm đã nghiệm thu. Không yêu cầu xóa cache chính để đóng ticket.
