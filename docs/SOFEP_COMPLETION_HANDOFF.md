# SOFEP — bàn giao demo A–H

Ngày: 03/11/2026, Asia/Saigon. Nhánh: `nhanh-cua-duong`.

## Bản để trải nghiệm

- Customer/khách vãng lai — bản catalog đầy đủ: http://127.0.0.1:5175/
- Bản fixture nhỏ để đối chiếu ảnh chụp/quy trình 205.000đ: http://127.0.0.1:5174/san-pham/ao-demo-uat-5
- Admin bản catalog đầy đủ: http://127.0.0.1:5175/admin
- Dấu xác minh API bản catalog đầy đủ: http://127.0.0.1:8012/__uat/status
- Bản cổng 5175 có 167 sản phẩm và 186 file raster sao chép có xác minh SHA-256 vào UAT riêng. Không sao chép khách hàng, đơn hàng hoặc lịch sử thật; không sao chép Seller 170/171. Bản cổng 5174 giữ fixture 6 sản phẩm để kiểm thử số liệu/concurrency. Catalog nghiệp vụ được đọc từ kết nối local đã xác minh, không bị ghi thay đổi.

Tài khoản UAT: `uat-admin@sofep.example.test`, `uat-customer@sofep.example.test`. Mật khẩu bản catalog đầy đủ được lưu tại file local `C:/Users/dn133/AppData/Local/Temp/sofep_uat_20261103_0552eb8e0b6d/session.json`; không đưa giá trị vào báo cáo hoặc repository. Chỉ đăng nhập các tài khoản này tại bản UAT tương ứng. Mật khẩu của fixture cổng 5174 nằm trong session của `sofep_uat_20261103_43ebbecf81ed`, khác với bản cổng 5175. Đăng xuất Admin trước khi thử mua hàng. UAT không gửi email OTP thật; dùng các tài khoản thử đã tạo sẵn.

## Trạng thái A–H

| Nhóm | Trạng thái và bằng chứng |
|---|---|
| A — kiểm toán, môi trường | Đã kiểm tra nhánh/thay đổi có sẵn, xác minh MySQL local loopback, tách UAT và chạy regression. Không ghi hàng loạt vào database nghiệp vụ nên không thực hiện backup/restore database đó. UAT mới có thể tái tạo từ migration và fixture; chưa kiểm thử khôi phục bằng SQL dump. |
| B — Admin Search | Đã triển khai và kiểm tra browser: từ đồng nghĩa `voucher`, phím mũi tên/Enter, SKU `UAT-5`, điều hướng tới bộ lọc đúng sản phẩm; mở tìm kiếm trên mobile. Có debounce, cache ngắn, hủy request cũ, loading/lỗi/rỗng. Backend yêu cầu quyền Admin. SKU chứa số không bị sửa gần đúng sang SKU khác. |
| C — Seller cũ | Giữ nguyên sản phẩm 170/171, chủ sở hữu và lịch sử theo chỉ đạo. Hai sản phẩm vẫn ẩn; tài khoản Seller vẫn giữ role. Không xác nhận đây là hai sản phẩm trong `1.docx`. Seller không xuất hiện trong danh sách Customer hoạt động; không được dùng API Customer/Admin bằng token Seller. |
| E — voucher | Hoàn thành tính năng trong UAT: cố định/phần trăm, mức trần, đơn tối thiểu, ngày, trạng thái, tổng lượt, mỗi khách, mọi khách/khách mới/khách được chọn. Kiểm tra và tính tiền ở backend. Migration chưa áp dụng vào database nghiệp vụ. Phạm vi voucher hiện là toàn shop; schema chưa có phạm vi danh mục/sản phẩm. |
| F — cart/checkout/payment | Đã kiểm thử API, MySQL đồng thời và browser từ Product Detail → voucher → tạo đơn → thanh toán mô phỏng → Admin đang giao → đã giao → doanh thu. Backend tự tính giá/phí, khóa kho, rollback khi lỗi, chống retry trùng. Không giao dịch ngân hàng thật. |
| D — ảnh | Kiểm tra lại 167/167 ảnh chính của sản phẩm đang bán: HTTP thành công, đúng định dạng ảnh, frontend chấp nhận, không trùng SHA-256; 100% theo tiêu chí này. Không cần bổ sung ảnh. Chưa tái duyệt trực quan từng SKU trong lượt này. Ảnh UAT có nhãn minh họa. |
| G — hiệu năng | Đã đo bundle, API và Navigation/Resource Timing trước/sau bằng hai bản build thử; giảm dữ liệu Admin Search, truy vấn thống kê và JS thực tải. Số liệu local và giới hạn bên dưới; không suy rộng sang production. |
| H — UI/UX, Admin | Đã UAT Product Detail và dashboard ở 1440/768/390px, không tràn ngang toàn trang; checkout, voucher, Admin Search, quản lý sản phẩm/đơn và dashboard có ảnh chụp. Chưa UAT đầy đủ mọi trang phụ, mọi tổ hợp thiết bị và mọi thao tác CRUD ảnh trong lượt này. |

**Bản demo UAT sẵn sàng góp ý; không coi toàn bộ A–H đã nghiệm thu hoàn tất.**

## Quy tắc voucher và đơn hàng

- Khách mới: tài khoản xác thực chưa có đơn còn hiệu lực; đơn đã hủy không tính. Không dựa vào email/ID khách do frontend cung cấp.
- Voucher theo khách hoặc có giới hạn mỗi khách yêu cầu đăng nhập. Khách được chọn phải có ID trong danh sách backend lưu.
- Mỗi đơn dùng tối đa một voucher. Kiểm tra trước checkout không giữ lượt hoặc trừ kho; đặt hàng luôn kiểm tra lại.
- Lượt voucher được giữ ngay khi tạo đơn. Đơn chưa thanh toán được hủy hợp lệ sẽ hoàn kho và lượt voucher đúng một lần; khi đó lượt theo khách cũng được giải phóng. Lỗi transaction tự rollback.
- Chỉ hủy đơn chưa thanh toán ở trạng thái chờ/đã xác nhận. Đơn đã thanh toán cần quy trình hoàn tiền riêng; hệ thống trả 409, không tự hoàn tiền.
- Đơn lịch sử không có cờ xác nhận giữ kho không được tự hoàn kho. Không tự backfill cờ từ dữ liệu cũ.
- Không cho mở lại đơn đã hủy, nhảy trạng thái không hợp lệ, bỏ ghi nhận thanh toán, hoặc xóa lịch sử đơn.
- Thanh toán mô phỏng chỉ hoạt động ở local/testing, cần quyền với đơn hoặc mã xác thực riêng. Không tạo giao dịch ngân hàng thật. Retry mô phỏng không ghi nhận hai lần.
- Frontend giữ thông tin tối thiểu của đơn demo đang chờ trong `sessionStorage` để tiếp tục thanh toán sau reload bằng nút tiếp tục; thông tin người nhận được lấy lại từ API có quyền với đơn. Không lưu địa chỉ hoặc tên người nhận vào bản ghi này; xóa quyền tiếp tục khi đăng xuất. Logout Admin cũng thu hồi token tại backend.

## Migration đã chuẩn bị

`themmoik27be/database/migrations/2026_11_03_000001_add_voucher_eligibility_and_order_reservations.php`:

- `ma_giam_gias`: `doi_tuong`, `gioi_han_moi_khach`, `ids_khach_hang`.
- `don_hangs`: `ton_kho_da_giu`, `voucher_da_giu`, `checkout_fingerprint`; index phục vụ đếm lượt theo khách/mã/trạng thái.
- Các cờ giữ kho/voucher mặc định false để bảo toàn đơn lịch sử; không sửa dữ liệu cũ.

Migration chỉ được chạy trên ba database UAT mới. Kiểm tra cuối lượt xác nhận database nghiệp vụ chưa có các cột mới. Khi chưa duyệt migration trên database nghiệp vụ, Admin không hiện cấu hình điều kiện khách mới và hủy đơn cũ được chặn an toàn. Không tự chạy migration vào database đó.

Migration lịch sử của repository chưa khai báo `khach_hangs.role` dù source đã dùng. Fixture UAT bổ sung cột này riêng trên UAT; không sửa schema nghiệp vụ. Đây là chênh lệch cần xử lý khi chuẩn hóa cài đặt mới.

## Dữ liệu đã ghi

- `sofep_uat_20261103_43ebbecf81ed`: fixture, đơn thử concurrency, đơn browser, voucher thử, tài khoản thử và token phiên UAT. Một đơn browser đã đi đến `da_giao`/`da_thanh_toan`, tổng 214.500đ.
- `sofep_uat_20261103_bb3ce6b8e317`: fixture mới để chạy lại concurrency sau bản sửa, gồm kiểm tra giới hạn mỗi khách.
- `sofep_uat_20261103_0552eb8e0b6d`: bản demo catalog đầy đủ 167 sản phẩm/186 file ảnh; chỉ sao chép các bảng catalog, tạo tài khoản và voucher demo riêng. Không sao chép tài khoản/đơn/đánh giá của khách thật. Bản này phục vụ cổng 5175/8012; nguồn và hash file ảnh được bảo toàn trong quá trình sao chép. Đã tạo một đơn demo và hoàn tất, tổng 228.000đ; không có đơn thật được sao chép.
- Ba database đều được tạo mới trên MySQL loopback sau khi kiểm tra môi trường. Không sao chép khách hàng hoặc lịch sử mua thật vào UAT. Không xóa database UAT để người dùng kiểm tra.
- Không thay đổi database nghiệp vụ, secrets, `.env`, cấu hình triển khai, dependencies hoặc lockfile; không commit/push/deploy.

## Kết quả kiểm thử

| Kiểm tra | Kết quả |
|---|---|
| `node --test tests/*.test.mjs` trong `Fe` | 32/32 đạt; log `sofep-frontend-tests.log` |
| `npm run build` trong `Fe` | Đạt; log `sofep-build-latest.log` |
| `php -d extension=pdo_sqlite vendor/bin/phpunit --no-progress` | 45 test: 44 đạt, 1 bỏ qua; 889 assertions. Test bỏ qua là xuất fixture browser SQLite tùy chọn. |
| `php -l` các controller/model/service/request/migration mới hoặc sửa | Đạt; không đổi cấu hình PHP để bật extension vĩnh viễn. |
| `node tests/mysql-uat.mjs sofep_uat_20261103_bb3ce6b8e317` | Đạt: tranh chấp món cuối, lượt voucher cuối, khóa đơn trùng, giới hạn mỗi khách; hủy hai lần hoàn một lần; dùng lại voucher sau hủy; mô phỏng lặp. `sofep-mysql-uat.json` |
| Toàn bộ 15 API GET Admin không có tham số đường dẫn | HTTP 200 trên MySQL UAT: danh mục, thương hiệu, màu/size, sản phẩm/biến thể, khách, voucher, đơn, đánh giá, thống kê và lịch sử giao dịch. `sofep-admin-api-audit.json` |
| Phân quyền backend | Guest/Customer/Seller bị từ chối API Admin; Seller bị từ chối checkout; thanh toán cần quyền với đơn. Có regression tests. |
| Browser IAB | Đã mở local, mua demo thành công, quản lý đơn và tạo voucher khách mới; không có error/warn trong log tại các màn hình Admin cuối đã kiểm tra. Không khẳng định toàn bộ website không có lỗi console. |
| Responsive | Product Detail: 1440/768/390; dashboard Admin: 1440/768/390; Admin Search mobile hoạt động. Có ảnh chụp. |
| Bản UAT catalog đầy đủ | 167/167 ảnh chính HTTP hợp lệ, hash riêng; tạo đơn 220.000đ, giảm 22.000đ, phí 30.000đ, tổng 228.000đ; mô phỏng, trừ kho một lần, hoàn tất ở Admin và đối chiếu doanh thu đều đạt. Hủy đơn đã thanh toán bị từ chối. `sofep-full-catalog-uat.json` |
| Catalog thật, chỉ đọc | 167/167 ảnh chính hợp lệ, 0 ảnh chính trùng hash; `sofep-catalog-current.json` |
| `git diff --check` | Đạt; giữ nguyên nhánh hiện tại. |

SQLite không thay thế MySQL. Kiểm thử MySQL dùng migration thật trên database mới và nhiều tiến trình PHP độc lập. Chưa kiểm thử tải cao, nhiều máy hoặc sự cố hạ tầng.

## Số liệu hiệu năng

- Hai bản build so sánh trong thư mục tạm dùng cùng snapshot source lúc đo; so sánh layout Admin/Blank eager + mount ngay với async + chờ router sẵn sàng. `App.vue` mặc định chọn layout Admin khi route chưa sẵn sàng; bản sửa tránh tải nhầm layout trên trang Customer. Chunk lớn nhất: **578,86 kB → 392,82 kB**; gzip **187,66 kB → 125,88 kB**. Bản sau không còn cảnh báo chunk >500 kB. Đây không phải phép đo toàn bộ dung lượng JS của mọi route. Build cuối sau bổ sung tiếp tục thanh toán demo có chunk lớn nhất khoảng 394 kB, vẫn dưới 500 kB.
- Admin Search trước dùng danh sách đầy đủ có biến thể/gallery; sau dùng `search_options=1` chỉ trả ID/tên/SKU. UAT 6 sản phẩm: **7.014 → 423 bytes**.
- Benchmark API: 1 lần warmup + 7 mẫu, median **1.025,29 → 973,97 ms**. Mẫu dao động lớn, chưa đủ kết luận cải thiện thời gian phản hồi ổn định; xem `sofep-api-benchmark.json`.
- Thống kê tháng: **24 truy vấn tổng/count → 1 truy vấn aggregate**, xác minh bằng query log trong regression test; giữ đủ 12 tháng với tháng trống bằng 0.
- Đã dùng `Fe/tests/performance-build.config.mjs` và server đọc build để so sánh. Instrumentation chỉ được chèn vào HTML của hai bản đo thử, không có trong build demo thông thường. Cùng tab 1280×720, cùng UAT 6 sản phẩm, 1 warmup + 3 mẫu mỗi bản: median DOMContentLoaded **429,3 → 349,7 ms**, sự kiện load **637,2 → 362,5 ms**, catalog response end **2.882 → 2.477,8 ms**; JS encoded body size của trang chủ **626.084 → 439.783 bytes**. Dữ liệu từng mẫu: `sofep-page-benchmark.json`. Browser không cung cấp Performance API trực tiếp trong evaluate nên build test xuất số đo ra DOM để đọc bằng API hợp lệ. Không đo Core Web Vitals, không throttle thiết bị/mạng; external media và tải máy vẫn ảnh hưởng.

## File source thay đổi trong lượt tiếp tục

Frontend:

- `Fe/src/layout/wrapper/ClientLayout.vue`: bỏ quảng bá mã voucher hardcode chưa được xác minh; xóa phiên tiếp tục thanh toán khi logout.
- `Fe/src/main.js`: tải layout Admin/Blank theo nhu cầu và chờ router sẵn sàng trước mount để không tải nhầm Admin trên trang Customer.
- `Fe/src/layout/components/HeaderRocker.vue`, `AdminSearch.vue`: tìm kiếm mobile, dữ liệu gợi ý nhẹ và thu hồi token backend khi logout Admin.
- `Fe/src/services/adminSearch.js`: không tìm gần đúng SKU chứa số sang SKU khác.
- `Fe/src/components/Admin/MaGiamGia/index.vue`: tạo/sửa voucher, ngày, đối tượng, giới hạn khách, trạng thái và lỗi backend.
- `Fe/src/components/Admin/DonHang/index.vue`: bỏ xóa lịch sử; hiện lỗi chuyển trạng thái.
- `Fe/src/components/Client/CheckoutModal.vue`: kiểm tra voucher/tổng tiền, giữ phiên đơn demo, chặn thao tác lặp, xử lý giỏ sau tạo đơn, COD/mô phỏng đúng trạng thái và tăng tương phản lựa chọn thanh toán.
- `Fe/tests/admin-search.test.mjs`, `checkout-flow.test.mjs`, `performance-build.config.mjs`, `serve-performance.mjs`.

Backend:

- `app/Services/CheckoutService.php`, `DemoPayment.php`, `OrderLifecycle.php` trong `themmoik27be`: xác thực giá/khách/voucher, idempotency không lộ khóa, transaction và vòng đời đơn.
- `app/Http/Controllers/ClientController.php`, `routes/api.php`: API kiểm tra voucher trước đặt hàng.
- `app/Http/Controllers/Admin/DonHangController.php`, `KhachHangController.php`, `MaGiamGiaController.php`, `SanPhamController.php`, `ThongKeController.php`: bảo toàn lịch sử, loại Seller khỏi Customer hoạt động, quản lý voucher, dữ liệu tìm kiếm nhẹ, thống kê đúng đơn hoàn thành đã thanh toán.
- `app/Http/Requests/MaGiamGiaRequest.php`; `app/Models/MaGiamGia.php`, `DonHang.php`.
- Migration nêu trên; `tests/Feature/CheckoutIntegrityTest.php`; `tests/Support/MySqlUat.php`, `MySqlUatRouter.php`, `PrepareCatalogDemo.php`; `tests/mysql-uat.mjs`.

Tài liệu/bằng chứng: file này; `sofep-catalog-audit.mjs`, `sofep-api-benchmark.mjs`, `sofep-full-catalog-uat.mjs`, các JSON/log cùng prefix `sofep-`, thư mục `sofep-uat-screenshots`. Không có file source bị xóa. Các thay đổi có sẵn trước lượt tiếp tục được giữ lại, không mặc nhiên tính là thay đổi mới của lượt này.

## Hướng dẫn demo

1. Mở cổng 5175 ở chế độ khách vãng lai hoặc đăng nhập Customer UAT. Nếu đang là Admin, đăng xuất trước.
2. Chọn một sản phẩm còn hàng trong catalog, chọn đúng màu/size và bấm Mua ngay. Nếu muốn đối chiếu ví dụ giá 205.000đ cùng ảnh chụp trước đó, dùng fixture riêng cổng 5174, Áo demo UAT 5.
3. Điền thông tin demo, nhập `DEMO10`, bấm kiểm tra mã. Server tính mức giảm 10%, tối đa 100.000đ. Ví dụ fixture 5174: tiền hàng 205.000đ, giảm 20.500đ, phí 30.000đ, tổng 214.500đ. Bản catalog 5175 còn có `NEW15` cho khách mới và `VIP20` cho Customer UAT được chọn; hai mã này giới hạn một lượt mỗi khách và cần đăng nhập.
4. Xác nhận đơn và bấm mô phỏng thanh toán. Không chuyển tiền thật. COD phải hiện chờ thanh toán khi nhận hàng.
5. Đăng nhập Admin UAT, kiểm tra đơn vừa tạo; chuyển đã xác nhận → đang giao → đã giao. Dashboard chỉ ghi nhận thực thu của đơn đã giao và đã thanh toán.
6. Thử tạo đơn chưa thanh toán khác rồi hủy trong Admin; kiểm tra tồn kho và lượt voucher được hoàn. Đơn đã thanh toán bị từ chối hủy trực tiếp.
7. Admin Search: nhập `voucher`, dùng mũi tên/Enter; thử `san pham`, `UAT-5`; xem bộ lọc đúng sản phẩm. Tạo/sửa voucher khách mới/khách được chọn và giới hạn một lượt. Trên bản catalog đầy đủ dùng SKU thực trong danh sách; `UAT-5` chỉ thuộc fixture nhỏ.

## Khởi động lại UAT nếu tiến trình đã dừng

Không chạy setup, migrate hoặc seed vào database nghiệp vụ. Dùng hai terminal riêng:

Backend, tại `themmoik27be`:

```powershell
$env:SOFEP_MYSQL_UAT_SESSION = 'C:\Users\dn133\AppData\Local\Temp\sofep_uat_20261103_0552eb8e0b6d\session.json'
php -S 127.0.0.1:8012 tests/Support/MySqlUatRouter.php
```

Frontend, tại `Fe`:

```powershell
$env:VITE_API_URL = 'http://127.0.0.1:8012/api'
npm run dev -- --host 127.0.0.1 --port 5175 --strictPort
```

Đây là biến của tiến trình terminal; không sửa `.env`. Kiểm tra `/__uat/status` đúng purpose và database trước khi thử thao tác ghi. Nếu file session hoặc database UAT không còn, chuẩn bị fixture UAT mới bằng công cụ đã có và dùng đường dẫn session mới; không chuyển sang database nghiệp vụ để thay thế.

## Phần còn lại trước nghiệm thu toàn bộ

- Phê duyệt riêng nếu muốn áp dụng migration vào database đang dùng; cần kế hoạch backup/khôi phục trước thao tác đó.
- Xác minh hai sản phẩm được nhắc trong tài liệu gốc trước bất kỳ dọn dữ liệu Seller nào.
- Quy trình hoàn tiền/hoàn trả cho đơn đã thanh toán chưa triển khai; hủy trực tiếp được chặn.
- Chưa có Core Web Vitals, benchmark nhiều thiết bị/mạng hoặc kiểm thử tải cao; page-load đã đo ở local UAT với mẫu nhỏ.
- Chưa UAT đầy đủ tất cả trang phụ, tất cả thao tác CRUD ảnh/biến thể và mọi kích thước màn hình. Không coi gallery đủ ảnh nhiều góc chỉ vì ảnh chính hợp lệ.
- Các thay đổi có sẵn rất lớn và chưa commit; cần review diff trước khi người dùng quyết định commit/deploy.
