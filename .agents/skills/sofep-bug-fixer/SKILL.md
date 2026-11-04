---
name: sofep-bug-fixer
description: Xác minh nguyên nhân và sửa lỗi SOFEP trên Vue 3/Vite, Laravel/PHP và MySQL sau khi người dùng phê duyệt bản vá. Dùng khi yêu cầu sửa bug hoặc xử lý phát hiện từ sofep-code-reviewer; không dùng cho yêu cầu chỉ review code.
---

# SOFEP Bug Fixer

Sửa đúng nguyên nhân, giữ tương thích frontend `Fe/` và backend `themmoik27be/`. Trao đổi, đề xuất và báo cáo bằng tiếng Việt.

## Quy trình bắt buộc

1. Đọc `AGENTS.md` tại gốc repository và các hướng dẫn áp dụng cho file liên quan.
2. Chạy `git status --short` và `git branch --show-current`. Nhận diện thay đổi có sẵn; không ghi đè hoặc hoàn tác chúng.
3. Đọc báo cáo của `sofep-code-reviewer` nếu có trong ngữ cảnh hoặc tài liệu được cung cấp. Nếu chưa có, tiếp tục từ source code; không bắt buộc chạy review mới. Kiểm chứng phát hiện trong báo cáo thay vì mặc định đó là nguyên nhân.
4. Xác minh nguyên nhân từ source code: lần theo component/router/Axios đến Laravel routes, middleware, requests, controllers, models và truy vấn liên quan. Nêu bằng chứng bằng đường dẫn, dòng code và điều kiện gây lỗi; phân biệt nguyên nhân đã xác nhận với giả thuyết. Nếu chưa đủ dữ kiện, chỉ rõ thông tin cần bổ sung, không sửa theo phỏng đoán.
5. Đề xuất bản vá nhỏ nhất: hành vi trước/sau, danh sách chính xác file cần tạo/sửa/xóa, ảnh hưởng và kế hoạch kiểm thử. Không mở rộng sang refactor hoặc lỗi khác.
6. Xin phép và chờ người dùng đồng ý với bản vá cùng danh sách file trước khi chỉnh sửa. Chỉ đọc và phân tích trong lúc chờ; không chạy lệnh ghi file hoặc đổi trạng thái chưa được duyệt. Nếu đã có phê duyệt rõ ràng cho chính bản vá và các file này trong hội thoại, không hỏi lại.
7. Chỉ sửa file và nội dung được phê duyệt, theo cấu trúc và phong cách hiện có. Nếu cần thêm file hoặc đổi hướng xử lý, trình bày và xin phê duyệt bổ sung trước khi mở rộng. Lỗi ngoài phạm vi chỉ được báo cáo, không tự sửa.
8. Kiểm thử phù hợp theo hướng dẫn bên dưới, sau khi xác nhận không tác động database thật hoặc dịch vụ bên ngoài.
9. Báo cáo bằng tiếng Việt: nguyên nhân và bằng chứng; danh sách file đã tạo/sửa/xóa và nội dung chính; lệnh kiểm tra cùng kết quả thực tế; kiểm tra chưa chạy, lý do và hạn chế còn lại.

## Giới hạn bản vá

- Không tự commit, push, chuyển nhánh, merge, rebase, reset, clean hoặc viết lại lịch sử Git. Giữ nguyên thay đổi ngoài phạm vi được duyệt.
- Không tự thay đổi hợp đồng API: endpoint, HTTP method, request/response, mã trạng thái hoặc cơ chế xác thực. Nếu cần, trình bày ảnh hưởng và chờ phê duyệt riêng trước khi sửa.
- Không tự thay đổi schema, dữ liệu, migration hoặc seeder; quyền sửa code không bao gồm quyền thay đổi database. Mọi thay đổi database phải được phê duyệt riêng. Không tự chạy migrate, rollback, migrate:fresh, db:wipe, db:seed, import SQL hoặc truy vấn ghi dữ liệu.
- Không tự sửa `.env`, `.env.*`, kết nối database, khóa ứng dụng hoặc cấu hình triển khai. Không chạy `composer setup` vì có thể tạo môi trường, sinh khóa và chạy migration.
- Không tự cài đặt/nâng cấp dependency, sửa lockfile, chuyển database hoặc nâng phiên bản công nghệ. Không đưa bí mật vào code hoặc báo cáo.

## Phân quyền liên quan đến lỗi

Kiểm tra Customer, Seller và Admin tại backend Laravel cho chức năng đang sửa, gồm trường hợp được phép và bị từ chối. Với Seller, xác định cửa hàng từ người dùng đã xác thực và kiểm tra quyền sở hữu tài nguyên; không tin ID cửa hàng hoặc tài nguyên từ client nếu chưa kiểm tra. Bao gồm trường hợp Seller truy cập dữ liệu cửa hàng khác và Customer/Seller truy cập chức năng Admin. Việc ẩn giao diện hoặc bảo vệ route Vue chưa đủ. Nếu thiếu phân quyền ngoài phạm vi duyệt, báo cáo và đề xuất bản vá để xin phép.

## Kiểm thử an toàn

- Frontend: cân nhắc `npm run build` trong `Fe/`; đọc scripts hiện có, không giả định có test hoặc lint. Build thành công không thay thế kiểm thử chức năng.
- Backend: dùng `php -l <file>` cho PHP đã sửa. Chỉ chạy `php artisan test` trong `themmoik27be/` sau khi đọc cấu hình và các test liên quan để xác nhận dùng database kiểm thử cô lập, không gọi dịch vụ thật. Không tự sửa môi trường để chạy test.
- PHPUnit khai báo SQLite trong bộ nhớ không đủ chứng minh tương thích MySQL. Với lỗi truy vấn MySQL, nêu giới hạn kiểm chứng; không thử thao tác ghi trên database đang sử dụng.
- Kiểm tra đường đi gây lỗi, hành vi sau sửa và hồi quy liên quan, gồm phân quyền khi áp dụng. Nếu thiếu môi trường an toàn hoặc công cụ, nêu rõ phần chưa kiểm thử; không báo thành công cho kết quả chưa xác minh.
