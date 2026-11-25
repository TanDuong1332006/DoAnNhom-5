# SOFEP — Kiểm toán source, Proposal và Jira

Ngày kiểm toán: **18/11/2026**, giờ Việt Nam. Repository `DoAnNhom-5-GITHUB`, nhánh `nhanh-cua-duong`, HEAD `ece155286b4dbbd59903c1484d71fe26bc7616d5`.

## 1. Kết luận dành cho Product Owner

SOFEP có lõi thương mại điện tử Single-Brand hoạt động ở mức triển khai và kiểm thử kỹ thuật: catalog, biến thể, ảnh, giỏ hàng local, checkout có xác thực Customer, tính tiền/voucher tại server, thanh toán mô phỏng, quản lý đơn và dashboard Admin. Module góp ý riêng, chiến dịch thông báo, loyalty và ví voucher có source cùng bằng chứng kiểm thử. **Chưa đủ căn cứ nghiệm thu toàn dự án hoặc chuyển hàng loạt ticket sang Done.**

Đã chạy lại trong lượt kiểm toán: frontend **59/59 đạt**; backend **72 bài, 70 đạt, 2 bỏ qua, 1.183 assertions**; PHP lint **116 file đạt**; Vite build đạt, 168 module, chunk JS lớn nhất khoảng 408,40 kB (gzip 130,77 kB). Các kết quả này xác minh working tree hiện tại, bao gồm source chưa commit, không chỉ HEAD.

Jira trực tiếp có **93 issue nhìn thấy được** trong khoảng N5-1..N5-144: **56 Done, 5 In Progress, 32 To Do**. Có 51 số không được trả về. Không kết luận chúng đã bị xóa: có thể chưa tạo hoặc không có quyền xem. Có 88 issue trước nhóm N5-140..144; vì vậy N5-141 cần ghi rõ baseline 88 + 5 issue theo dõi = 93, thay vì coi 88 là số hiện tại.

Proposal mới nhất tìm thấy trong repository và cây workspace `D:/do_an_nhom` là `IS432CIS-Proposal_SOFEP .docx`, thời gian filesystem **03/10/2026 17:02:45**, 1.723.856 byte. Đây là ngày lưu file, không phải ngày phát hành: trang bìa ghi Version 1.1, 24/08/2026, Revision History chỉ có 1.0. Đã đọc toàn bộ nội dung XML theo thứ tự, bảng và hai hình nghiệp vụ/Scrum. Không thấy comments/tracked changes. Chưa render toàn bộ trang Word vì runtime dependency không cung cấp đường dẫn LibreOffice; không kết luận về lỗi ngắt trang hay bố cục bản in.

**Phát hiện quan trọng:** mục 10 Proposal đã ghi Vue/PHP/Laravel/MySQL/Sanctum, không còn Next.js/FastAPI/MongoDB ở bảng công nghệ chính. Phần References, ngân sách, NFR bảo mật và hình minh họa còn nội dung cũ. Không nên sửa mô tả N5-140 thành “toàn Proposal dùng stack cũ”.

## 2. Phương pháp và mức bằng chứng

| Mức | Ý nghĩa | Có thể chứng minh |
|---|---|---|
| S | Source đang được nối route/caller | Đã triển khai, chưa chứng minh chạy trên môi trường thực |
| T | Test chạy lại ngày 18/11 | Hành vi trong harness hiện tại; FE có mock/component extraction, BE SQLite bộ nhớ |
| H | JSON/log/ảnh UAT có sẵn ngày 03–07/11 | Kết quả kỹ thuật lịch sử trên fixture/môi trường ghi trong file |
| A | Biên bản nghiệm thu của PO với phạm vi và acceptance criteria | Chấp thuận nghiệp vụ để Verified Done |

Không tìm thấy A đủ để xác nhận nghiệm thu toàn bộ 93 ticket. **Verified Done theo chuẩn nghiệm thu: 0 được xác nhận trong lượt audit**, không có nghĩa 0 chức năng hoạt động. Trạng thái Done hiện có trên Jira được giữ nguyên; bảng CSV là phân loại kiểm toán và đề xuất, không phải lệnh chuyển tự động.

Nhãn `In Progress` trong đối chiếu thể hiện deliverable mới đáp ứng một phần hoặc đang được hoàn thiện bằng bằng chứng hiện có; không suy ra một thành viên đang làm việc chỉ từ code. `To Do` dùng khi thiếu luồng hoạt động hoặc thiếu nội dung xác định. `Out of Scope` là **đề xuất chờ PO xác nhận** đối với Merchant/AI/chat, không phải quyết định loại phạm vi đã ký.

Không chạy script UAT MySQL cũ vì có thể tạo database, đơn, voucher và chiến dịch. Không gọi API ghi, gửi email/SMS, migrate, seed, sửa `.env`, cài dependency, commit, push, deploy hoặc cập nhật Jira. Khi kiểm tra listener, chỉ thấy MySQL 3306 trong các cổng 5173/8000/5180/8016/3306; không thấy FE/BE trên các cổng local đã ghi trong bàn giao. Vì vậy không có browser UAT mới ngày 18/11 và không khẳng định website đang phục vụ được tại thời điểm bàn giao.

## 3. Kiến trúc thực tế

| Thành phần | Bằng chứng | Kết luận |
|---|---|---|
| FE | `Fe/package.json`, lockfile, `src/main.js`, `src/router/index.js` | Vue 3.3.4, Vite 4.4.8, Vue Router 4.0.13, Axios 1.20.0 theo lockfile; SPA, lazy route/layout |
| BE | `themmoik27be/composer.json`, lockfile, `bootstrap/app.php` | PHP yêu cầu ^8.3; Laravel v13.25.0, Sanctum v4.3.3, Socialite v5.31.0 theo lockfile |
| API | `routes/api.php` | REST JSON tiếng Việt; Customer/Admin tách middleware; không có route Seller hoạt động |
| Auth | `AuthController`, `AdminMiddleware`, `RequireCustomer`, `Fe/src/services/authSessions.js`, `main.js` | Sanctum Bearer personal access token; không phải JWT; FE lưu token localStorage, tách Customer/Admin; đăng xuất thu hồi token server |
| Admin | `AdminMiddleware.php:14` | Model `User` trong bảng users được coi là Admin; không có mô hình phân quyền Admin chi tiết |
| Customer | `RequireCustomer.php:14`, `DemoPayment.php:19` | Chỉ KhachHang role customer, hoạt động và không khóa; thanh toán kiểm tra chủ đơn |
| DB | migrations + `docs/sofep-engagement-activation.json` | MySQL 8.4.3 local theo bằng chứng 07/11; không xác minh schema live ngày 18/11 |
| Jobs | `routes/console.php`, `CustomerCampaigns.php` | Scheduler giao chiến dịch theo lô; bằng chứng 07/11 có schedule:work, chưa chứng minh tự khởi động sau reboot |
| Ảnh | `ProductImageController`, `FeedbackController` | Ảnh sản phẩm public raster; ảnh góp ý private local disk, endpoint Admin; hai chính sách tách biệt |
| Deployment | `vite.config.js`, CORS, tài liệu setup | Local dev server + MySQL; chưa thấy bằng chứng production URL, TLS, deploy pipeline, giám sát hay restore drill production |

Luồng chính: Vue route/component → Axios interceptor/base URL → Laravel route → Sanctum + middleware vai trò → validation/controller/service → Eloquent/DB transaction → JSON → UI. Cart hiện tại là `Fe/src/stores/cartStore.js` lưu localStorage; sự tồn tại controller/model Cart tiếng Anh không chứng minh giỏ server đã tích hợp. Các controller Profile/Order/Cart cũ không được nối trong `routes/api.php` hiện tại.

## 4. Danh sách chức năng và mức hoàn thiện

| Chức năng | Source hoạt động | Test mới | UAT lịch sử | Còn thiếu trước nghiệm thu |
|---|---|---|---|---|
| Login Customer/Admin, OTP đăng ký, quên mật khẩu | AuthController + ForgotPasswordController + UI auth | Có test auth/role, mail fake | Auth checkout HTTP UAT 06/11 | OTP/email thực tế, chống dò mã/tài khoản, PO ký |
| Hồ sơ Customer | Modal chỉ hiển thị trong ClientLayout | Không chứng minh cập nhật hồ sơ | Chưa thấy | Endpoint và UI sửa hồ sơ/sở thích/body; Google OAuth chưa có route |
| Catalog/search/danh mục/giá | ClientController, TimKiem, TrangChu | Có | 168 chi tiết, 500 biến thể, 275 URL ảnh hợp lệ trong audit 06/11 | Size/color filtering toàn catalog theo FR03 chưa thấy đầy đủ; kiểm tra dữ liệu mới |
| Product Detail 2.1 | ProductDetailController + ChiTietSanPham + services | Có race, variant, ảnh, lỗi API | Có ảnh 1440/768/390, mẫu catalog 07/11 | Không coi ảnh gallery là nhiều góc thật của cùng mẫu nếu chưa xác minh |
| Quản lý ảnh/biến thể Admin | ProductImageController, ChiTietSanPhamController | Có upload/order/ownership ảnh theo product | Có một phần | Browser CRUD đầy đủ, hồi quy dữ liệu catalog thực |
| Giỏ hàng | cartStore local, quantity/variant persistence | Có | Guest/browser và checkout lịch sử | Đồng bộ nhiều thiết bị không có; ghi đúng giới hạn vào Proposal |
| Checkout Customer | CheckoutService, CheckoutModal | Có Guest/Admin/Seller/owner, giá, kho, voucher, idempotency | `sofep-auth-catalog-mysql-uat.json` đạt | Chuỗi browser đăng ký OTP → checkout đầy đủ, ký UAT |
| COD và mô phỏng MB_BANK | giaoDichController + DemoPayment | Có failed/success/repeat/COD | Có HTTP MySQL | Mô phỏng bị chặn ngoài local/testing; chưa giải quyết cách demo hosting an toàn |
| Admin quản lý đơn | DonHangController + OrderLifecycle | Có transition/hủy/khôi phục một lần | Full catalog UAT 03/11 có hoàn tất đơn | Customer chưa có lịch sử/track toàn bộ đơn; chưa có hoàn tiền/trả hàng |
| Voucher | MaGiamGia + CheckoutService + Admin UI | Có đối tượng/hạn dùng/quota/per-user | Có MySQL race và engagement 07/11 | Rule danh mục/sản phẩm chưa hỗ trợ; chốt chính sách với PO |
| Dashboard/doanh thu | ThongKeController + chart UI | Có chỉ đếm đơn giao và đã trả, aggregate | Có đối chiếu doanh thu | Không phải analytics tìm kiếm/click hay AI |
| Góp ý riêng | FeedbackController + Engagement UI | Có privacy/attachment/spam/role | HTTP UAT 07/11, Guest browser lịch sử | Chưa phải hệ thống tranh chấp/hoàn trả đầy đủ; Admin browser thực còn thiếu |
| Loyalty/campaign/thông báo/ví | CustomerCampaigns, LoyaltyAudience, VoucherWallet, CustomerInbox | Có AND rules/timezone/retry/IDOR/opt-out | HTTP UAT 07/11 + main read-only smoke | Scheduled browser toàn luồng, nhiều worker/tải; loyalty dựa ngày tạo đơn |
| Seller/Merchant | Không có route; đăng ký/login Seller bị từ chối | Có regression vai trò cũ | Tài liệu catalog giữ nguyên dữ liệu cũ | PO duyệt Single-Brand; không dọn dữ liệu lịch sử trong audit |
| Chat/AI image search/prediction | Không thấy route/service tích hợp tương ứng | Không | Không | Đề nghị hoãn/loại scope có Change Log, không báo hoàn thành |

**Phần thực sự hoàn thành ở mức kỹ thuật trong phạm vi test:** các invariant có test chạy đạt cho xác thực vai trò, giỏ/biến thể, định giá checkout server, quyền chủ đơn, mô phỏng retry, vòng đời/hủy đơn, voucher, privacy ảnh góp ý và ownership ví/thông báo. Không đồng nhất chúng với toàn bộ FR hay toàn bộ module đã được PO nghiệm thu.

### Mâu thuẫn lịch sử đã xử lý

- `SOFEP_CUSTOMER_ENGAGEMENT_HANDOFF.md` và `sofep-engagement-main-check.json` nói main chưa migrate. `SOFEP_ENGAGEMENT_ACTIVATION.md` và JSON activation/smoke ngày 07/11 xác nhận đã chạy đúng hai migration có duyệt, backup/restore và smoke main. Lấy bản sau làm mốc trạng thái lịch sử mới hơn; giữ bản trước để truy vết.
- Catalog UAT 167 sản phẩm khác catalog main 168 vì có thêm ID 172; không kết luận mất một sản phẩm. `sofep-pre-migration-safety.json` ghi rõ khác biệt này.
- Browser/UAT đầu tháng có thể dùng checkout Guest cũ; bằng chứng auth checkout 06/11 mới hơn xác nhận Guest 401, Admin 403 và chủ đơn từ server. Không dùng ảnh success cũ để chứng minh chuỗi OTP/auth mới.

## 5. Phát hiện và rủi ro cần xử lý

### F01 — High: đường login/OTP thiếu giới hạn số lần thử trong application

**Bằng chứng tĩnh:** `routes/api.php:28–39` khai báo đăng ký, login, xác thực/reset OTP không gắn throttle; `bootstrap/app.php` không cấu hình throttle chung, `AppServiceProvider.php:22` chỉ định limiter feedback. `AuthController::xacThucOtp` kiểm tra hạn và hash nhưng không tăng số lần sai. `ForgotPasswordController.php:22,43,67` tạo/so OTP 6 số không có bộ đếm thất bại. Chưa xác minh WAF/reverse proxy bên ngoài, không tuyên bố đã chiếm tài khoản.

**Tác động:** có thể gửi liên tục yêu cầu login, dò mã trong thời hạn, gây tải Hash/Mail; reset/send-otp còn phân biệt email tồn tại. Đề xuất limiter theo IP + định danh được hash, quota gửi lại và budget số lần OTP; lỗi không tiết lộ tài khoản. File dự kiến: routes, provider, controller auth/reset và tests. Không sửa trong audit. Test: đúng/sai/hết hạn, vượt ngưỡng 429, nhiều IP/tài khoản, không gửi mail thật; việc thay HTTP status/response cần duyệt hợp đồng API trước.

### F02 — Medium: reset mật khẩu không thu hồi token đã cấp

`ForgotPasswordController::resetPassword` lưu hash mới và xóa OTP nhưng không thu hồi Sanctum tokens; `config/sanctum.php` để expiration null, `AuthController::dangNhap` tạo token không chỉ định expiresAt. Người giữ token cũ có thể tiếp tục truy cập sau reset nếu chưa bị thu hồi. Xác nhận từ code, chưa khai thác live. Đề xuất PO chốt chính sách phiên rồi bổ sung thu hồi token trong reset và test token cũ 401, login mới thành công, giữ đúng vai trò; không sửa cấu hình trong audit.

### F03 — High đối với bàn giao: migration repository thiếu baseline đang được source sử dụng

Migrations hiện có không tạo cột `khach_hangs.role` và không tạo bảng `cua_hangs`; AuthController tạo role khi đăng ký, LoyaltyAudience truy vấn role. `tests/Support/MySqlUat.php` phải bổ sung role bằng nhánh `Schema::hasColumn`; `IsolatedApiTestCase.php` tự tạo schema có role và cua_hangs. Vì vậy test bộ nhớ đạt không chứng minh clone mới + migrate/seed tái tạo được DB local hiện có. `SOFEP_LOCAL_SETUP.md` đang hướng dẫn quy trình đó.

Đề xuất lập diff schema chỉ đọc, chốt phần legacy cần giữ và trình riêng migration/baseline không mất dữ liệu. **Không chạy fresh/migrate/seed** để kiểm tra trên DB hiện dùng. Kiểm thử sau duyệt trên DB mới dùng một lần, đối chiếu constraint/index/default/role + đăng ký/login; cần duyệt database riêng.

### F04 — Medium: “Bán chạy” trên trang tìm kiếm được sắp theo lượt xem

`Fe/src/components/Client/TimKiem.vue:529–530` dùng `b.luot_xem - a.luot_xem` cho `sortBy === 'ban_chay'`. UI có thể xếp sản phẩm nhiều lượt xem nhưng chưa bán lên đầu. Dashboard có cách tính từ đơn giao đã thanh toán nhưng không được dùng ở sort này. Đề xuất tối thiểu đổi nhãn sang “Xem nhiều” sau PO xác nhận, hoặc định nghĩa và triển khai sort theo số lượng bán; nếu thêm response field phải duyệt API. Test fixture A nhiều view/0 sales, B ít view/nhiều sales và kiểm tra thứ tự/nhãn mong đợi.

### F05 — Medium: chưa đạt căn cứ nghiệm thu hiệu năng và khả năng mở rộng

`TimKiem.vue:501` tải `san-phams?all=1`; `ClientController` cho all=1 trả toàn catalog cùng relations; nhiều API Admin dùng get() không phân trang. Đây là tải tăng theo catalog/dữ liệu, chưa phải bằng chứng sự cố hiện tại. Benchmark 03/11 chỉ 6 sản phẩm/7 mẫu, có sample 2.357,83 ms; page catalog response end sau tối ưu median khoảng 2.477,8 ms. Không thể ký NFR01 mọi thao tác ≤2s hay NFR04 tải lớn từ số đo này.

Đề xuất định nghĩa endpoint/thao tác, dataset, đồng thời, p95/error rate rồi benchmark UAT; chuẩn bị kế hoạch phân trang/filter server và index sau duyệt tương ứng. Không thay hợp đồng hoặc schema trong audit.

### F06 — Medium: bảo toàn dữ liệu Seller chưa tương đương quy tắc lọc catalog Single-Brand

`SanPham::scopeCatalog` lọc trạng thái và biến thể nhưng không lọc `id_cua_hang`; CheckoutService đọc sản phẩm active theo ID, không loại ownership Seller. Seller token bị từ chối, song Guest/Customer có thể thấy/mua một sản phẩm Seller cũ **nếu** nó còn active. Snapshot catalog lịch sử không chứng minh đang có dữ liệu như vậy; đây là rủi ro có điều kiện, không khẳng định rò dữ liệu hiện tại. Không báo ID 170/171 đã xóa hoặc phải xóa khi chưa có quyết định dữ liệu.

PO cần chọn giữ/ẩn/chuyển ownership từng nhóm dữ liệu. Sau duyệt, test Guest/Customer/Admin và Seller cũ, sản phẩm ownership NULL/khác NULL, đơn lịch sử bất biến. Dọn dữ liệu cần phê duyệt DB riêng.

### Những giới hạn nghiệp vụ đã xác nhận

- Profile/fashion preference/body info chưa có route cập nhật; OrderController tiếng Anh không được nối route Customer. Customer hiện chỉ tiếp tục đơn demo đã lưu và xem thanh toán có quyền sở hữu, không có trang lịch sử đơn đầy đủ. N5-83/84/87/88/95/96 không đủ cơ sở coi Done theo FR02.
- Google OAuth: có dependency Socialite, cột google_id và lời hứa trong Proposal, chưa có route callback/login tương ứng. Không coi dependency là tính năng.
- OrderLifecycle chặn hoàn tiền mới và chặn hủy đơn đã thanh toán (`OrderLifecycle.php:22–24`); đây là bảo vệ hiện hữu, không phải quy trình refund/return. Chưa có hoàn tiền một phần.
- Thông báo hiện có campaign + phản hồi góp ý, chưa thấy hook tự động cho mọi thay đổi đơn/tài khoản như FR11 mô tả.
- Gợi ý size ở SizeAdvisorModal dùng công thức height/weight và ngưỡng hardcode; các confidence 95–99 không phải metric mô hình AI được đánh giá. Chỉ ghi là gợi ý theo quy tắc nếu còn sử dụng.
- Mô phỏng thanh toán chỉ local/testing. Hosting với APP_ENV production sẽ bị 403 ở mô phỏng; không đổi production thành local để né guard. PO cần chọn demo local hay sandbox hosting được thiết kế riêng.
- `AdminMiddleware` tin toàn bộ model User là Admin. Phù hợp nếu users chỉ chứa Admin; cần giữ điều kiện này khi mở rộng account, không có bằng chứng bypass bởi Customer hiện tại.
- Token localStorage và expiration null là giới hạn thiết kế cần nêu khi bảo vệ; chưa có bằng chứng XSS cụ thể để tuyên bố lộ token.

## 6. Đánh giá Proposal

**Ưu điểm:** đủ 15 mục chính; có mục tiêu đo được, FR01–FR12/NFR01–04, phạm vi loại trừ tiền thật, Scrum 4 sprint, kế hoạch người/phần việc, rủi ro kho/thanh toán/privacy và danh sách deliverables. Mục 10 và assumption đã gần stack thực tế. Các mục checkout/voucher/transaction hiện có có thể dùng làm bằng chứng tốt cho hướng thương mại điện tử.

**Nhược điểm:** phạm vi Merchant và Single-Brand chưa đồng bộ; hình Scrum còn personalized recommendation, image search, trend prediction; hình “architecture” thực chất context diagram ba actor, không thể hiện Vue/API/Laravel/MySQL; thiếu traceability đến source/test/Jira. Version 1.1 không có trong Revision History; Project Information kết thúc 24/08 trong khi kế hoạch đến 20/11; “3 tháng” và “4 tháng” khác nhau. References còn Next/FastAPI/MongoDB/JWT; ngân sách còn MongoDB/Docker và chỉ là dự toán, không có hóa đơn deployment. NFR ≤2s/scalability chưa có workload và thước đo nghiệm thu; OAuth/JWT không phản ánh Sanctum Bearer hiện hành. FR02/06/08/10/11 và deliverables đang rộng hơn thực tế.

Đề xuất **v1.2 — bản tái lập phạm vi chờ PO duyệt**, giữ nguyên v1.1, thông tin học phần, nhóm và mentor. Có Revision History mới, Change Log theo FR và lý do, không viết lại lịch sử như thể chưa từng có Merchant/AI. Kế hoạch chi tiết nằm trong `PROPOSAL_V1_2_CHANGE_PLAN.md`; bảng FR/NFR và Jira nằm trong `ALIGNMENT.md`.

## 7. Đánh giá Jira và đề xuất chỉnh

**Điểm tốt:** có task theo giai đoạn và subtask tách tầng; owner/priority có trên phần lớn ticket; nhóm N5-140..144 đã có acceptance criteria và nhận diện đúng công việc đồng bộ/UAT. Chưa có thay đổi owner hay lịch sử nào trong audit.

**Điểm yếu:** 56 issue không có description trong snapshot; nhiều tên Front-End/Back-End/Inventory/Checkout thiếu tầng trong title; Done quá rộng khi Profile/Preferences chưa nối luồng; nhiều Admin task To Do dù đã có code/test; Merchant vẫn In Progress/To Do; các Epic `ds`, `hight` và Story `User Story` chưa đủ nội dung để đánh giá. Acceptance/evidence ở source chưa được nối về từng ticket. Không dùng tỉ lệ 56/93 để báo tiến độ tính năng vì parent/subtask và phần scope cũ trùng công đếm.

**Phân biệt trùng tên:** Login N5-81 dưới N5-10 (design) khác N5-85 dưới N5-11 (FE); N5-126/127 dưới N5-15 là UI design, khác N5-112/113 dưới N5-16 là implementation. Promotions N5-132 là FE, N5-136 là BE; Inventory N5-100 (DB), N5-116 (BE core), N5-131 (FE), N5-135 (BE Admin). Đây là trùng tên cần bổ sung ngữ cảnh, chưa đủ để đóng Duplicate. N5-1/2/3 với N5-140 là baseline và thay đổi phạm vi, không được xóa bản cũ.

| Nhóm ticket | Hành động đề xuất | Lý do |
|---|---|---|
| N5-19/20/21/23 | Tách rõ phần Admin hiện có và Merchant legacy, đổi tên sau PO duyệt | Mixed scope, không đóng cả ticket chỉ vì Admin chạy |
| N5-22 | Tách Merchant Approval ra Out of Scope đề xuất; content moderation cần định nghĩa riêng | CRUD sản phẩm không đồng nghĩa workflow moderation |
| N5-128/134/138 | Đề xuất Out of Scope + liên kết N5-140, giữ lịch sử | Không còn route Merchant |
| N5-129..133,135..137,139 | Implemented/Pending UAT cho phần Admin phù hợp; bỏ mơ hồ Dashboard | Có source/test; còn thiếu browser CRUD/PO sign-off |
| N5-83/84/87/88/95/96 | Review Done, đưa phần chưa có về To Do/In Progress sau PO xác nhận | Profile/sở thích thiếu UI/API nối hoạt động |
| N5-24/25/26/29 | In Progress, bổ sung evidence và phần test còn thiếu | Tích hợp/auth đã có nhưng chưa nghiệm thu toàn flow |
| N5-27/30/31 | In Progress, thêm F01–F06 và kế hoạch đo | Đã có tối ưu/test; còn bảo mật/performance/backlog |
| N5-32 | Giữ To Do | Chưa có production deployment evidence |
| N5-33/144 | In Progress | Có tài liệu kỹ thuật nhưng chưa thống nhất/rehearsal/được duyệt |
| N5-38/39/40/41/42/45 | Giữ To Do, PM xác định mục tiêu/AC/parent | Không suy diễn nội dung từ tên chung |
| N5-140/141 | In Progress cho đến khi PO review tài liệu/bảng đầy đủ | Audit này là deliverable hỗ trợ, chưa tự nghiệm thu |
| N5-142/143 | In Progress, liệt kê test đã có và case Blocked | Có UAT kỹ thuật lịch sử; chưa UAT tài khoản giới hạn đầy đủ |

`JIRA_RECONCILIATION.csv` có **một dòng cho từng issue thực sự tồn tại**, bao gồm title/description/assignee/priority/status hiện tại, parent/type, nhóm kiểm toán, tên đề xuất, bằng chứng và hành động. Phân nhóm: **0 Verified Done, 32 Implemented/Pending UAT, 45 In Progress, 13 To Do, 3 Out of Scope đề xuất**; tổng 93. Đây là phân loại deliverable theo phạm vi và bằng chứng, không phải tỷ lệ hoàn thành tính năng. Không tự chuyển trạng thái. Trường nhóm kiểm toán có thể giữ ngoài workflow như label/report; PM phải kiểm tra transition hợp lệ trước cập nhật. Không tự giao N5-140..144 cho cá nhân; các issue này đang chưa có assignee.

## 8. Kế hoạch từ 18/11 đến mốc 20/11 trong Proposal

Đây là kế hoạch đề xuất, chưa xác nhận ngày bảo vệ hay cam kết hoàn tất của nhóm.

| Thứ tự | Công việc | Điều kiện hoàn thành | Jira |
|---|---|---|---|
| P0 — 18/11 | PO chốt Single-Brand, scope thiếu, demo local/hosting; review F01/F03 | Quyết định và residual risks có người chấp thuận; không mở rộng tính năng âm thầm | 140,141,26,30,31 |
| P0 — 18–19/11 | Sửa lỗi bảo mật được duyệt; lập baseline schema tái tạo | Patch review + test; mọi migration/env cần duyệt riêng; DB hiện dùng không đổi | 12,30,31,144 |
| P0 — 19/11 | N5-142 full browser UAT trên fixture MySQL riêng đã xác minh | Từng case Passed/Failed/Blocked + screenshot/log; không ghi main | 142,24,25,29 |
| P0 — 19/11 | N5-143 engagement qua tài khoản giới hạn | IDOR/opt-out/preview drift/retry/hẹn giờ/voucher đã kiểm tra UI lẫn API | 143 |
| P1 — 19/11 | Benchmark có workload, kiểm tra NFR; chốt refund chưa hỗ trợ | Kết quả đo và giới hạn rõ; không tuyên bố đạt nếu thiếu | 27,30,118 |
| P1 — 19–20/11 | Proposal v1.2, Jira mapping, hướng dẫn demo thống nhất | Revision History/Change Log/FR traceability, PO review | 140,141,33,144 |
| P1 — 20/11 | Rehearsal + đóng băng snapshot source | Rehearsal đúng dataset/port/role; nhóm quyết định commit; không tự push | 144 |
| Chỉ sau quyết định PO | Hosting production hoặc phát triển Profile/order history/refund/Google | Scope, thời gian, API/DB được duyệt và kiểm thử riêng | 32 và backlog liên quan |

Không cố triển khai đồng thời mọi chức năng cũ trước mốc 20/11. Các phần chưa có phải được phát triển có kế hoạch hoặc ghi hoãn được PO chấp thuận. Không biến việc đổi Proposal thành cách che phần còn thiếu.

### Checklist UAT chuyển giao cho QA/PO

1. Xác minh tên/purpose/schema của DB UAT trước ghi; không dùng main/production thay fixture. Không chạy lại script tạo fixture cũ nếu chưa review thao tác ghi.
2. Guest xem catalog, chọn đúng variant, thêm/sửa/giữ giỏ; checkout bị chặn. Customer đăng ký OTP bằng mail sink, login quay về giỏ đúng. Sai/hết hạn OTP, khóa account và phiên hết hạn phải có kết quả rõ.
3. Chạy checkout COD và MB_BANK: giá client giả bị bỏ qua, hết kho/variant chéo product bị từ chối, mã sai/hết hạn/hết lượt/sai audience giải thích đúng. Lặp cùng Idempotency-Key không tạo thêm đơn; payload khác bị từ chối.
4. Customer B không đọc/thanh toán đơn A; Guest/Admin/Seller cũ bị từ chối checkout và ví; Customer/Seller bị từ chối Admin. Admin hợp lệ CRUD tài nguyên theo scope; không có chức năng Seller chéo cửa hàng để nghiệm thu khi role đã loại.
5. Mô phỏng failed/success/retry; COD không dùng mô phỏng; Admin đi đúng vòng đời; hủy đơn chưa trả hoàn kho/voucher một lần; đơn trả rồi bị chặn và hiển thị lý do refund chưa hỗ trợ.
6. Campaign preview → thay điều kiện → phải xác nhận lại; opt-out trước giao; hẹn giờ; retry không trùng; Customer khác không lưu mã riêng; lưu không tiêu quota, checkout mới tiêu quota.
7. Góp ý private/attachment đúng định dạng/giới hạn; Admin xem ảnh; Customer/Guest/Seller không đọc attachment; phản hồi riêng đúng người; Guest không được báo gửi in-app giả.
8. Desktop 1440, tablet 768, mobile 390; keyboard, lỗi mạng, API rỗng, reload, hai phiên Admin/Customer; không có dữ liệu tài khoản thật trong ảnh/log chia sẻ.
9. Ký từng acceptance criterion, ghi tester/ngày/snapshot và defect còn mở; PO ký thì mới đề xuất Verified Done cho đúng phạm vi.

## 9. Các quyết định cần Product Owner xác nhận

- Single-Brand Customer/Admin là baseline chính thức? Merchant Approval/Chat/AI được hoãn hay bỏ; giữ liên kết lịch sử nào?
- FR02, Google auth, Customer order tracking và FR08 behavior analytics: triển khai tiếp hay hoãn rõ sang phiên bản sau? Không được mặc định đã hoàn thành.
- FR09 chỉ góp ý riêng hay cả dispute/return/refund? FR11 chỉ campaign/system feedback hay cả tự động order/account?
- Chính sách hàng Seller cũ, hoàn tiền, giữ kho đơn bỏ dở, voucher danh mục/sản phẩm và thời hạn token.
- Mốc bảo vệ thực tế; demo local hay hosting; ai ký UAT; NFR threshold/workload nào bắt buộc?
- Chủ sở hữu N5-140..144 và Epic/Story tên chung do PM quyết định; audit không đổi người phụ trách.
- Có cho phép giai đoạn sau sửa source, thay API, bổ sung baseline/migration, môi trường hay deploy? Mỗi phạm vi chịu phê duyệt tương ứng.

## 10. Kiểm tra đã chạy, giới hạn và tài liệu bàn giao

| Lệnh/kiểm tra | Kết quả | Phạm vi |
|---|---|---|
| `git branch --show-current`, `git status --short`, `git rev-parse HEAD` | Đúng nhánh; nhiều thay đổi có sẵn | Không đổi nhánh/hoàn tác |
| `node --test tests/*.test.mjs` tại Fe | 59 đạt, 0 lỗi | Không có script npm test/lint; gọi Node test trực tiếp |
| `php vendor/bin/phpunit --no-progress --do-not-cache-result` tại BE | 72 bài, 70 đạt, 2 exporter tùy chọn skip; 1.183 assertions | SQLite :memory:, guard kiểm tra testing/sqlite/:memory:/DB_URL; mail fake/array, Storage fake |
| `php -l <file>` trên app/routes/tests/migrations | 116 file đạt | Cú pháp; không chứng minh nghiệp vụ |
| `npm run build -- --outDir ../docs/audit-2026-11-18/build-check` tại Fe | Đạt, 168 modules, Vite 4.4.8 | Output riêng, không ghi đè dist hiện có; thư mục build-check được giữ lại vì bộ kiểm tra phê duyệt tự động từ chối thao tác dọn xóa đệ quy |
| `git diff --check` | Exit 0; có warning CRLF của file có sẵn | Không format/chỉnh source |
| JQL trực tiếp, kiểm tra isLast | 93 issue, hết trang | Chỉ issue được tài khoản kết nối nhìn thấy |

Chưa chạy: browser UAT mới, MySQL concurrency mới, OTP/email thật, stress/load, production deployment/TLS/backup mới, clean-install schema test, render QA Word toàn trang. Lý do: môi trường app local chưa phục vụ ở cổng ghi trong tài liệu; giữ DB thật; các test tích hợp ghi MySQL/ngoại dịch vụ cần fixture được xác minh riêng; không có đường dẫn renderer Word bundled. Không báo các kiểm tra này đã đạt.

Chỉ tạo tài liệu/bằng chứng trong `docs/audit-2026-11-18/`; không sửa source ứng dụng. `source-manifest.json` ghi hash source snapshot để nhận diện nội dung được kiểm thử; không phải danh sách khẳng định đã đọc từng asset/vendor. `git-status-baseline.txt` là snapshot Git trong lúc kiểm toán, đã bao gồm một số artifact audit mới; không phải trạng thái sạch ban đầu. Phạm vi đọc tập trung route/caller/model/service/request/middleware, component/store/auth API, migrations/tests, Proposal và các bằng chứng docs; không phải chứng nhận không còn lỗi trong mọi nhánh code hoặc dependency.

Các deliverable: báo cáo này; `ALIGNMENT.md`; `PROPOSAL_V1_2_CHANGE_PLAN.md`; `JIRA_RECONCILIATION.csv`; snapshot Jira/hierarchy; `verification.json`, lint và source hashes. Proposal gốc giữ nguyên; không ghi đè lịch sử Jira.
