# N5-142 — Kiểm thử quy trình mua hàng SOFEP

Thực hiện: **18/11/2026**; rà soát bằng chứng và bàn giao: **19/11/2026**, giờ Việt Nam. Nhánh: `nhanh-cua-duong`. Jira được đọc trực tiếp, đang To Do tại thời điểm bắt đầu; không tự chuyển trạng thái hoặc đổi người phụ trách. N5-147 được PM thông báo đã nghiệm thu; lượt này không phát triển lại auth/OTP.

## 1. Kết luận bàn giao

Đã kiểm tra Guest → Customer → checkout/voucher → thanh toán mô phỏng/COD → Admin xử lý → Customer cập nhật trạng thái đơn. **41 kiểm tra API trên MySQL UAT đạt**, frontend **64/64 đạt**, PHPUnit **83 bài: 81 đạt, 2 bỏ qua**, build đạt. Có bằng chứng giao diện Guest tại các viewport yêu cầu 1440, 768 và 390. Chưa xác nhận đầy đủ responsive màn hình kết quả đơn Customer ở 768/390; xem giới hạn bằng chứng bên dưới.

Đã sửa lỗi Customer không thấy trạng thái xử lý đơn tại màn hình kết quả checkout; thêm cập nhật trạng thái qua API chủ đơn đã có. Cũng sửa nhãn đơn hủy và nhãn thanh toán COD để không hiển thị sai nghĩa.

**Chưa đề nghị Done cho toàn bộ mục tiêu:** Customer chưa có chức năng tự hủy hoặc trang lịch sử/tra cứu toàn bộ đơn. Những phần này được ghi **Blocked**, không coi nút cập nhật đơn hiện tại là lịch sử đơn đầy đủ. Admin hủy đơn chưa thanh toán, hoàn kho/voucher và quyền sở hữu đơn đã được kiểm tra. PM cần nghiệm thu bản vá và quyết định phạm vi các phần Blocked.

## 2. Môi trường và ranh giới dữ liệu

| Thành phần | Môi trường sử dụng |
|---|---|
| Frontend | `http://127.0.0.1:5188`, Vite với `VITE_API_URL=http://127.0.0.1:8028/api` của tiến trình riêng |
| Backend | `http://127.0.0.1:8028`, `tests/Support/CheckoutUat.php`, chỉ loopback |
| MySQL UAT | `sofep_security_uat_20261118_d588ff07a906` — database kiểm thử đã được kiểm chứng từ N5-147 |
| Runtime | Bootstrap `SecurityBrowserBoot.php` đặt DB/cache/lock trước provider boot; chỉ connection `security_browser`, cache prefix riêng `n5142-f4971d:` |
| Session/fixture | `%TEMP%/sofep-n5142-f4971d/session.json`; pointer `%TEMP%/sofep-n5142-session-path.txt` |
| Product | ID 2, slug `ao-n142-f4971d`, SKU UAT142-f4971d; base 250.000đ, sale 200.000đ |
| Variants | ID 2: Đen/M142 +20.000đ; ID 3: Đen/L142 +30.000đ; ID 4: Xanh/M142 +0đ; bắt đầu 10 đơn vị mỗi variant |
| Voucher | `N142F4971D`, 10%, tối đa 50.000đ, đơn tối thiểu 100.000đ, 20 lượt |
| Accounts | Customer UI, Customer API, Customer đối chứng, Admin và Seller legacy riêng, email `@sofep.example.test`; không dùng tài khoản thật |

Tái sử dụng schema UAT có sẵn, chỉ thêm fixture mới có tag; không migrate, seed, reset hoặc import database. Không chọn/kết nối SQL tới `doan_nhom_5` hoặc N.Cảnh Barber. Toàn bộ connection trong tiến trình test đã bị giới hạn về UAT; cache/lock/mail/storage cũng được cô lập. Không flush/xóa cache chính. Mail chỉ array transport, không gửi email thật; không có chuyển tiền/giao hàng thật. FE/BE UAT được khởi động để kiểm thử, không phải deploy production; URL localhost chỉ truy cập được khi các tiến trình UAT còn chạy. Khi kiểm tra lại ngày 19/11, cả cổng 5188 và 8028 đều từ chối kết nối; các tiến trình của lượt UAT trước đã dừng, chưa khởi động lại vì không còn công cụ browser để tiếp tục nghiệm thu UI.

## 3. Ca kiểm thử trên trình duyệt

| ID | Trường hợp | Kết quả | Bằng chứng/giới hạn |
|---|---|---|---|
| UI-01 | Guest xem sản phẩm, giá, màu và size | Passed | Chọn Đen/M142: giá 220.000đ; Xanh/M142: 200.000đ; không chọn biến thể thì CTA bị khóa |
| UI-02 | Guest tìm kiếm từ khóa | Passed | Tìm `N142 f4971d`, kết quả có đúng sản phẩm; `screenshots/search-1440.jpg` |
| UI-03 | Guest thêm biến thể vào giỏ | Passed | Giỏ hiện Đen N142/M142, đúng giá 220.000đ |
| UI-04 | Guest tăng/giảm số lượng giỏ | Passed | Thao tác + rồi − trên dòng giỏ, tổng quay về 220.000đ |
| UI-05 | Guest xóa dòng giỏ | Passed | Giỏ trở về trống, sau đó thêm lại đúng variant |
| UI-06 | Mua ngay yêu cầu login | Passed | Có Đăng nhập / Đăng ký / Quay lại cửa hàng; `guest-1440/768/390.jpg` |
| UI-07 | Giữ giỏ sau đăng nhập | Passed | Chuyển Customer login rồi trở lại checkout, đúng 1 variant Đen/M142, không thêm trùng |
| UI-08 | Voucher không hợp lệ | Passed | Hiện “Mã giảm giá không tồn tại.”, giữ giỏ, không tạo đơn; `voucher-error-390.jpg` |
| UI-09 | Voucher hợp lệ và địa chỉ/tổng tiền | Passed | 220.000 − 22.000 + 30.000 = **228.000đ**; phone/address fixture, `checkout-1440/768.jpg` |
| UI-10 | Double-click xác nhận đặt hàng | Passed | Browser dblclick tạo duy nhất đơn UI đầu ID 5; tiếp tục cùng mã sau reload, không tạo bản sao |
| UI-11 | Loading và nút thao tác | Passed | Có loading xác minh phiên; submit bị khóa trong xử lý; nút cập nhật đơn có loading, regression chặn bấm lặp |
| UI-12 | Reload đơn mô phỏng đang chờ | Passed | Có nút “Tiếp tục thanh toán demo”, mở lại đúng mã đơn ID 5 |
| UI-13 | Mô phỏng thất bại | Passed | UI báo thất bại; `failed-payment-state.json` ghi đơn ID 5 vẫn chua_thanh_toan, không trừ kho/voucher thêm |
| UI-14 | Mô phỏng thành công | Passed | Đơn ID 5 đã thanh toán, vẫn chờ Admin xác nhận; `paid-order-status-1440.jpg` |
| UI-15 | Admin nhìn thấy đơn mới/chi tiết variant | Passed | Đúng tên, Đen/M142, qty 1, tiền hàng/phí/giảm/tổng; `admin-orders-1440/768/390.jpg` |
| UI-16 | Admin xử lý đúng lifecycle | Passed | Đơn ID 5: Đã xác nhận → Đang giao → Đã giao; lưu qua UI Admin |
| UI-17 | Customer thấy đúng từng trạng thái | **Failed trước sửa → Passed sau sửa** | Bổ sung trạng thái đơn + nút cập nhật; đối chiếu UI Customer ở cả 3 bước; ảnh desktop `customer-delivered-1440.jpg`. Không dùng các ảnh desktop lặp để chứng nhận mobile/tablet |
| UI-18 | COD không ghi đã trả | Passed | Đơn ID 6: 210.000đ, Chưa thanh toán; chưa dùng mô phỏng ngân hàng |
| UI-19 | Admin hủy COD chưa trả | Passed | Đơn ID 6 hủy qua UI Admin; Customer cập nhật thấy Đã hủy; kho/lượt hoàn lại đúng |
| UI-20 | Nhãn kết quả đơn hủy | **Failed trước chỉnh nhãn → Passed sau sửa** | Ca hồi quy tạo/hủy COD ID 7; UI cuối hiển thị “Đơn Hàng Đã Hủy”, `cancelled-final-desktop.jpg` |
| UI-21 | COD đã thu không gắn nhãn mô phỏng | Passed bằng test tự động | Computed label phân biệt COD và MB_BANK; chưa thực hiện riêng một COD được Admin thu tiền trên UI trong lượt này |
| UI-22 | Customer tự hủy đơn | **Blocked** | Không có route/UI hủy dành cho Customer. Không cho phép gọi Admin API để thay thế; API Customer → Admin trả 403 |
| UI-23 | Customer xem lịch sử/tra cứu mọi đơn sau rời checkout | **Blocked** | Link tra cứu đang là `#`, chưa có danh sách lịch sử đơn/API Customer tương ứng. Bản vá chỉ cập nhật đơn đang mở tại checkout |

Các viewport đã yêu cầu trong lượt browser là 1440×900, 768×1024 và 390×844; không phải thiết bị vật lý. Ảnh chụp có thể được crop theo phần tử/nội dung nên kích thước file không luôn bằng viewport. Ở modal Guest mobile, documentWidth = clientWidth = 375 px (scrollbar chiếm phần còn lại), không thấy tràn ngang tại phép đo đó. Không suy ra mọi trang đều không tràn từ một phép đo.

**Rà soát bằng chứng ngày 19/11:** sau khi mở tab Admin, thao tác đổi viewport không xác nhận lại chiều rộng tab Customer. Năm ảnh Customer mang hậu tố 390/768 thực tế vẫn thể hiện bố cục desktop (1425×891), đã đổi tên thành `cod-form-desktop.jpg`, `customer-cancelled-desktop.jpg`, `cancelled-final-desktop.jpg`, `customer-delivered-desktop-capture-2.jpg` và `customer-delivered-desktop-capture-3.jpg`. Không coi những ảnh đó là bằng chứng tablet/mobile. `screenshot-manifest.json` lưu kích thước pixel và SHA-256 từng ảnh. Ảnh Guest và voucher mobile có bố cục hẹp; ảnh Admin/checkout có thể crop, cần đối chiếu theo nội dung thay vì chỉ tên file.

**Blocked — kiểm chứng responsive bổ sung:** cần chạy lại màn hình kết quả đơn Customer ở 768/390 và xác nhận viewport của từng tab, đồng thời chụp toàn trang Admin/checkout ở ba kích thước để có ma trận ảnh nhất quán. Công cụ điều khiển trình duyệt không còn khả dụng ở lượt chốt báo cáo nên chưa chạy lại được phần này. Các ca nghiệp vụ trên desktop và bằng chứng API vẫn giữ nguyên kết quả; không ghi nhận thêm ca mobile đã đạt. Một số ảnh trung gian phản ánh trước bản vá nhãn cuối; `cancelled-final-desktop.jpg` là kết quả cuối của ca hủy.

Khi tự động hóa có hai lần cần quan sát lại: chờ modal Admin đóng animation trước mở bước tiếp, và đợi xác minh phiên xong trước điền form sau HMR. Không bỏ qua request lỗi nghiệp vụ; log HTTP được giữ. Loading/HMR không được diễn đạt thành lỗi thanh toán thật.

## 4. Kiểm thử API và invariant MySQL

Chi tiết **41 ca Passed** nằm trong `api-uat.json`, mỗi ca có expected/actual hoặc invariant. Tóm tắt phạm vi:

| Nhóm | Trường hợp / kết quả |
|---|---|
| Vai trò | Guest tạo đơn 401; Seller/Admin không tạo Customer order 403; Customer gọi Admin 403 |
| Validation | Vượt kho, voucher sai, thiếu địa chỉ đều 422; dữ liệu không được ghi dở dang |
| Giá server | Client khai tổng 1đ/giá 1đ bị bỏ qua; quote qty 2 Đen/M142 = 440.000 − 44.000 + 30.000 = **426.000đ** |
| Quote | Không giữ kho, không tiêu lượt voucher |
| Idempotency | Cùng UUID và payload trả cùng order ID; chỉ trừ qty 2 và dùng 1 lượt; payload khác cùng UUID trả 409 |
| Ownership | Customer B đọc hoặc mô phỏng thanh toán đơn A đều 403 |
| Thanh toán | failed giữ chưa trả; success và retry ghi đã trả mà không trừ kho/voucher thêm |
| Lifecycle | Nhảy thẳng Đã giao bị 409; Admin chuyển đúng 3 bước 200; owner đọc từng bước từ API khớp |
| Paid cancellation | Hủy đơn đã trả bị 409; không tự hoàn tiền hoặc kho |
| COD | Tạo COD chua_thanh_toan; không dùng mô phỏng MB_BANK (409); không hoàn tất trực tiếp khi chưa trả; hủy qua Admin hợp lệ |
| Hủy lặp | Hủy COD hai lần không hoàn kho/lượt hai lần; Customer gọi Admin cancel vẫn 403 |

Đây là kernel/API UAT trên MySQL thật của fixture, không phải Selenium/browser cho toàn bộ 41 ca; UI đã được kiểm tra riêng ở mục 3. Cùng-key retry là kiểm tra tuần tự và dblclick UI, chưa phải stress nhiều worker đồng thời. Bộ PHPUnit có các ca hồi quy khác nhưng SQLite không thay bằng chứng khóa MySQL.

## 5. Đối chiếu stock/voucher/đơn

| Mốc | Stock Đen/M142 (ID 2) | Stock Xanh/M142 (ID 4) | Voucher đã dùng |
|---|---:|---:|---:|
| Fixture mới | 10 | 10 | 0 |
| Sau 41 ca API | 8 | 10 | 1 |
| Browser đơn MB_BANK ID 5, failed rồi success | 7 | 10 | 2 |
| Browser COD có voucher ID 6, trước hủy | 7 | 9 | 3 |
| Sau Admin hủy ID 6 | 7 | 10 | 2 |
| COD hồi quy nhãn ID 7 không voucher, hủy xong | 7 | 10 | 2 |

Variant Đen/L142 (ID 3) giữ 10. `before-browser-cancel.json`, `after-browser-cancel.json`, `final-state.json` là snapshot UAT. Mã đơn browser:

- ID 5: `HD_C14BAEB7A2D80A5EF34D621B615F5437`, MB_BANK, 228.000đ, đã trả, đã giao.
- ID 6: `HD_B1B766474D5AA713547A4C2B08CAB027`, COD, 210.000đ, chưa trả, đã hủy.
- ID 7: `HD_ABEF8C8764BA65BE662C99F3BD0E670C`, COD, 230.000đ, chưa trả, đã hủy; dùng kiểm tra nhãn cuối.

DB UAT còn các fixture N5-147 trước đó; không coi chúng là đơn mới của N5-142 và không xóa chúng để làm sạch báo cáo. API actor và UI actor khác nhau, tên/tag phân biệt rõ. Không dùng số lượng tổng đơn của toàn DB làm số đơn N5-142.

## 6. Lỗi phát hiện và bản vá

### F142-01 — Không hiện trạng thái xử lý đơn cho Customer

Trước sửa, phần success trong `Fe/src/components/Client/CheckoutModal.vue` chỉ hiển thị trạng thái **thanh toán**. API `GET /api/kiem-tra-thanh-toan/{ma_hoa_don}` đã trả `don_hang.trang_thai_don_hang`, nhưng UI không render nên khách không biết Admin đã xác nhận/giao.

Đã thêm nhãn Chờ xác nhận/Đã xác nhận/Đang giao/Đã giao/Đã hủy và nút **Cập nhật trạng thái đơn** dùng API hiện hữu. Nút có loading, chống gọi lặp; giữ màn hình kết quả hủy để khách đọc trạng thái, không đẩy về form đặt hàng. Không thêm endpoint, request field hoặc sửa response/backend authorization.

### F142-02 — Nhãn kết quả hủy và thanh toán chưa phản ánh loại đơn

Khi giữ màn hình để xem đơn đã hủy, tiêu đề success cố định “Đặt Hàng Thành Công” gây hiểu nhầm. Đã dùng tiêu đề “Đơn Hàng Đã Hủy” cho ca này và hồi quy bằng một COD fixture mới. Nhãn đã trả trước đây cố định “Đã thanh toán mô phỏng”; đã phân biệt MB_BANK mô phỏng và COD “Đã thanh toán”. Nhãn COD đã trả được test tự động, không giả nhận đã UAT UI thu COD.

File source sửa **chỉ** `Fe/src/components/Client/CheckoutModal.vue`; test sửa `Fe/tests/checkout-flow.test.mjs` thêm ca refresh API owner, chặn bấm lặp, trạng thái hủy và nhãn COD/MB_BANK. Test mới kiểm tra hành vi thực của methods/computed với API giả lập, không gọi dịch vụ ngoài.

File mới `themmoik27be/tests/Support/CheckoutUat.php` chỉ là harness/fixture/query evidence, tái sử dụng bootstrap an toàn của N5-147. Các file ứng dụng/backend/database khác không sửa trong task này; giữ mọi thay đổi có sẵn. Không sửa migrations, seeder, `.env`, dependency/lockfile, tài khoản thật; không commit/push/deploy.

## 7. Kết quả regression sau sửa

| Lệnh | Kết quả | Bằng chứng |
|---|---|---|
| `node --test tests/*.test.mjs` trong Fe | **64/64 Passed**, 0 fail | `frontend-tests.log`; đã chạy lại sau chỉnh nhãn cuối |
| `php vendor/bin/phpunit --no-progress --do-not-cache-result` trong BE | **83 bài, 81 Passed, 2 skipped**, 1.304 assertions | `phpunit.log`; memory SQLite guard, mail fake/array; skip là exporter tùy chọn |
| `npm run build -- --outDir ../docs/N5-142/build-check` | Passed | `build.log`; output riêng không ghi đè Fe/dist; đã chạy lại sau chỉnh nhãn cuối |
| `php -l tests/Support/CheckoutUat.php` | Passed | Harness cú pháp hợp lệ |
| `php tests/Support/CheckoutUat.php api` | **41 Passed** | `api-uat.json`; MySQL UAT, API/lifecycle/invariant |
| `git diff --check` | Passed, warning CRLF một số file có sẵn | Không format ngoài scope |

Không chạy migration/seed, không gửi email thật, không kiểm thử ngân hàng/giao vận thật. Không chạy load test hay đánh giá production security trong N5-142. Console tại lần đọc cuối không có warn/error được ghi nhận, không chứng nhận toàn website không lỗi console.

## 8. Vấn đề còn mở và quyết định PM

1. **Blocked — Customer tự hủy:** cần PO quyết định chính sách trạng thái nào được hủy, kho/voucher/refund và hợp đồng API riêng; không cho Customer quyền Admin để né thiếu chức năng.
2. **Blocked — lịch sử/tra cứu đơn đầy đủ:** bản vá chỉ hỗ trợ đơn hiện tại trong checkout. Khi rời hoàn toàn màn hình kết quả, Customer chưa có trang để mở lại mọi đơn; không gọi đó là lịch sử đơn đã hoàn thành.
3. **Refund/return:** chưa triển khai; hủy đơn đã trả bị chặn an toàn, không có hoàn tiền thật hay hoàn một phần.
4. Bộ fixture chỉ có 3 tổ hợp biến thể, hai tổ hợp đã thao tác UI; không đại diện toàn catalog/ảnh/sản phẩm thật hoặc tất cả CRUD Admin.
5. Jira N5-142 còn nhắc thông báo/ví/góp ý. Lượt này theo yêu cầu PM tập trung mua hàng; không nghiệm thu lại toàn module engagement hoặc email thật, tham chiếu bằng chứng riêng N5-147/N5-143 khi PM chốt AC tổng.
6. **Blocked — bằng chứng responsive bổ sung:** hoàn tất ma trận kết quả đơn Customer 768/390 và ảnh toàn trang theo ghi chú mục 3 trước khi nghiệm thu giao diện đầy đủ.
7. Không tự chuyển Jira Done. Đề nghị PM review 41 ca API, UI/screenshots và bản vá, rồi quyết định xử lý các mục Blocked trong N5-142 hay tách backlog; không đóng toàn quy trình nếu Customer self-cancel/history vẫn là AC bắt buộc.


## UAT bổ sung ngày 19/11/2026 — N5-142 và N5-143

Xem [báo cáo UAT bổ sung](../uat-final-2026-11-19/REPORT.md) và 123 kiểm tra HTTP MySQL. Lượt này sửa race phiên ở MyOrders; lịch sử đơn đã có source/API/test, không còn mô tả là thiếu hoàn toàn. Phạm vi nghiệm thu hiện hành là desktop local; các Blocked mobile/self-cancel trong phần lịch sử phía trên không còn là điều kiện Done N5-142. Browser mới vẫn chờ xác nhận; không tự chuyển Done.
