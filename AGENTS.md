# Hướng dẫn AI Coding Agent — SOFEP

## Phạm vi và công nghệ
- Áp dụng cho toàn bộ repository SOFEP.
- Frontend: Vue 3 và Vite trong `Fe/`.
- Backend: Laravel trong `themmoik27be/`.
- Hỗ trợ MySQL theo cấu hình được người dùng phê duyệt.
- Không tự chuyển đổi database hoặc nâng cấp phiên bản công nghệ.

## Ngôn ngữ
- Luôn giải thích, trao đổi, lập kế hoạch và báo cáo bằng tiếng Việt.
- Giữ nguyên tên định danh, đường dẫn và lệnh kỹ thuật khi cần.

## Quy trình trước khi chỉnh sửa
1. Đọc hướng dẫn và các file liên quan.
2. Luôn kiểm tra nhánh Git hiện tại và `git status` trước khi làm việc
   để nhận diện nhánh đang sử dụng và các thay đổi có sẵn.
3. Khi sửa lỗi, phân tích luồng xử lý và nguyên nhân trước.
   Nêu bằng chứng; phân biệt nguyên nhân đã xác nhận với giả thuyết.
4. Trình bày phương án, các file dự kiến sửa, ảnh hưởng và cách kiểm thử.
5. Xin phép trước khi chỉnh sửa ngoài phạm vi đã phê duyệt.
   Giai đoạn 1 được chủ động sửa source và test liên quan Seller, không cần
   xin phép từng file. Giữ nguyên mọi giới hạn database, môi trường và Git.
6. Chỉ thực hiện trong phạm vi đã được duyệt.
   Nếu cần mở rộng phạm vi, xin phép bổ sung.

Có thể đọc, tìm kiếm và phân tích mà không cần xin phép.
Không chạy lệnh có thể ghi file hoặc thay đổi trạng thái khi chưa được duyệt.

## Quy tắc triển khai
- Tuân theo cấu trúc và phong cách hiện có của từng file.
- Vue 3: giữ tương thích component, router và cách gọi API hiện tại.
- Laravel: sử dụng đúng routes, middleware, requests, controllers
  và Eloquent models theo cấu trúc dự án.
- Giữ nhất quán hợp đồng API giữa frontend và backend.
- Không tự ý thay đổi hợp đồng API giữa Vue và Laravel, bao gồm endpoint,
  HTTP method, cấu trúc request/response, mã trạng thái và cơ chế xác thực.
  Nếu cần thay đổi, trình bày ảnh hưởng và chờ người dùng phê duyệt trước.
- Sửa đúng phạm vi; tránh refactor hoặc format các file không liên quan.
- Không ghi đè hoặc hoàn tác thay đổi có sẵn của người dùng.
- Không tự cài đặt, cập nhật dependency hoặc thay đổi lockfile.
- Không đưa mật khẩu, token hoặc thông tin bí mật vào code hay báo cáo.

## Phân quyền Customer, Seller và Admin
- Luôn kiểm tra phân quyền Customer, Seller và Admin đối với chức năng
  liên quan; kiểm tra cả trường hợp được phép và bị từ chối truy cập.
- Thực thi phân quyền tại backend Laravel; không chỉ dựa vào việc ẩn
  giao diện hoặc bảo vệ route trong Vue.
- Seller chỉ được xem và quản lý dữ liệu thuộc cửa hàng của mình.
  Xác định cửa hàng từ người dùng đã xác thực và kiểm tra quyền sở hữu
  khi đọc, tạo, cập nhật hoặc xóa dữ liệu; không tin tưởng ID cửa hàng
  hoặc ID tài nguyên do client gửi lên nếu chưa kiểm tra.
- Kiểm tra trường hợp Seller truy cập dữ liệu của cửa hàng khác;
  không để Customer hoặc Seller truy cập chức năng dành riêng cho Admin.
- Nếu phát hiện thiếu phân quyền, báo cáo nguyên nhân và đề xuất cách sửa;
  vẫn phải chờ phê duyệt trước khi chỉnh sửa code.

## Database và môi trường
- Không tự ý thay đổi schema, dữ liệu, migration hoặc seeder.
- Mọi thay đổi database phải được trình bày và phê duyệt riêng.
- Không tự chạy migrate, rollback, migrate:fresh, db:wipe,
  db:seed, import SQL hoặc truy vấn ghi dữ liệu.
- Không tự sửa `.env`, `.env.*`, cấu hình môi trường,
  kết nối database, khóa ứng dụng hoặc cấu hình triển khai.
- Không tự chạy `composer setup`: script hiện tại có tạo `.env`,
  sinh khóa ứng dụng và chạy migration.
- Quyền sửa code không mặc nhiên cho phép thay đổi database
  hoặc cấu hình môi trường.

## Git và GitHub
- Luôn kiểm tra nhánh Git hiện tại trước khi làm việc.
- Không tự ý chuyển nhánh, merge hoặc rebase.
- Không tự commit hoặc push.
- Chỉ commit hoặc push khi người dùng yêu cầu rõ ràng.
- Không tự reset, clean, checkout đè file hoặc viết lại lịch sử Git.
- Giữ nguyên các thay đổi ngoài phạm vi công việc đã duyệt.

## Kiểm thử sau khi sửa
- Chọn kiểm tra phù hợp với thay đổi đã được duyệt.
- Frontend: có thể dùng `npm run build` trong `Fe/`.
  Hiện chưa có script test hoặc lint; không tự giả định chúng tồn tại.
- Backend: có thể dùng `php -l <file>` để kiểm tra cú pháp PHP
  và `php artisan test` trong `themmoik27be/` khi môi trường phù hợp.
- Trước khi chạy test, xác nhận không tác động database đang sử dụng
  hoặc dịch vụ bên ngoài. Không tự sửa môi trường để chạy test.
- PHPUnit hiện khai báo SQLite trong bộ nhớ; kết quả đó không chứng minh
  đầy đủ tính tương thích với MySQL.
- Build thành công không thay thế kiểm thử chức năng.
- Nếu không chạy được kiểm thử, nêu rõ lý do; không báo thành công giả.

## Báo cáo sau mỗi lần sửa
Luôn báo cáo bằng tiếng Việt:
- Nguyên nhân lỗi hoặc mục tiêu thay đổi.
- Danh sách file đã tạo, sửa hoặc xóa và nội dung chính.
- Lệnh kiểm tra đã chạy và kết quả đạt hoặc không đạt.
- Các kiểm tra chưa chạy, lý do và hạn chế còn lại.
