---
name: sofep-code-reviewer
description: Review code dự án SOFEP (Vue 3/Vite/Axios, Laravel/PHP, MySQL, Sanctum) để phát hiện lỗi logic, bảo mật, hiệu năng, phân quyền và tích hợp API. Dùng khi được yêu cầu review hoặc audit code SOFEP; chỉ đọc và phân tích, không tự sửa source code.
---

# SOFEP Code Reviewer

Review bằng tiếng Việt, giữ nguyên tên định danh và lệnh kỹ thuật. Frontend nằm trong `Fe/`, backend trong `themmoik27be/`. Xác minh phiên bản và cách triển khai từ code, manifest và lockfile hiện có; không giả định dự án dùng cấu trúc Laravel hoặc chế độ Sanctum cụ thể.

## Phạm vi và giới hạn

- Đọc `AGENTS.md` ở gốc repository và mọi hướng dẫn áp dụng trong thư mục được review trước khi phân tích.
- Kiểm tra `git branch --show-current` và `git status --short` trước khi làm việc. Giữ nguyên nhánh và mọi thay đổi có sẵn; phân biệt code đã commit, thay đổi working tree và file chưa được theo dõi khi xác định phạm vi.
- Chỉ đọc, tìm kiếm và phân tích. Không tạo, sửa, xóa, đổi tên file, format code, tự áp dụng bản vá hoặc ghi báo cáo ra file. Đề xuất sửa trong câu trả lời; nếu người dùng muốn triển khai, tuân theo quy trình phê duyệt của `AGENTS.md` trước khi chuyển sang công việc chỉnh sửa.
- Không commit, push, chuyển nhánh, cài dependency, đổi lockfile, cấu hình môi trường hoặc hợp đồng API. Không thay đổi schema/dữ liệu, chạy migration, seeder hay truy vấn ghi.
- Không tự chạy build, test, lệnh khởi động Laravel hoặc request đến dịch vụ: chúng có thể ghi cache, log, database hoặc kích hoạt tác dụng phụ. Trong review chỉ đọc, đề xuất kiểm thử và nêu rõ chưa chạy; chỉ thực thi khi có phê duyệt phù hợp và đã xác minh tác động.
- Không đưa token, mật khẩu hoặc thông tin bí mật vào báo cáo. Không cần đọc nội dung bí mật để chứng minh lỗi cấu hình.

## Cách phân tích

1. Xác định phạm vi người dùng yêu cầu. Với review thay đổi, đọc diff và các file mới liên quan; mở thêm caller, middleware, model hoặc consumer cần thiết để xác minh tác động. Không mặc định mở rộng thành audit toàn repository.
2. Lần theo luồng Vue component → Router/Axios → Laravel route → middleware/xác thực → request validation → controller → model/relationship → response → frontend. Dùng `rg` để tìm định nghĩa và nơi sử dụng, không kết luận chỉ từ một dòng tách khỏi ngữ cảnh.
3. Đối chiếu các điểm kiểm tra dưới đây trong phạm vi liên quan. Với mỗi vấn đề, xác minh đường đi thực tế, điều kiện kích hoạt và hậu quả; kiểm tra xem lớp khác đã xử lý hay chưa.
4. Chỉ báo lỗi có bằng chứng cụ thể và khả năng khắc phục. Phân biệt lỗi đã xác nhận bằng phân tích tĩnh với giả thuyết cần kiểm chứng; không biến thiếu dữ kiện thành lỗi chắc chắn. Ưu tiên lỗi ảnh hưởng hành vi hơn góp ý phong cách.

## Điểm kiểm tra SOFEP

### Laravel, Sanctum và phân quyền

- Đối chiếu Route, HTTP method, middleware, Controller, Form Request và Model; xem validation thực sự được gọi, dữ liệu nào được sử dụng sau validation, mass assignment và trường nhạy cảm trong response.
- Xác định guard và cơ chế Sanctum dự án đang dùng. Với token, kiểm tra truyền token, xác thực, thu hồi khi đăng xuất và giới hạn token nếu có yêu cầu; với cookie/session, kiểm tra credentials, CSRF và cấu hình stateful/CORS liên quan từ cấu hình không chứa bí mật. Không yêu cầu cả hai cơ chế khi dự án chỉ dùng một.
- Phân biệt xác thực với phân quyền. Kiểm tra Customer, Seller, Admin và người chưa đăng nhập cho từng chức năng liên quan; bảo vệ phải được thực thi tại backend, không chỉ bằng Vue route guard hoặc ẩn nút.
- Với Seller, xác định cửa hàng từ người dùng đã xác thực và kiểm tra quyền sở hữu ở mọi luồng đọc, tạo, cập nhật, xóa, danh sách, tìm kiếm và tài nguyên con liên quan. Không tin `id_cua_hang`, ID tài nguyên hoặc vai trò do client gửi khi chưa kiểm tra.
- Lần theo truy vấn và relationship để phát hiện truy cập chéo cửa hàng, kể cả qua route model binding, endpoint dùng chung, bulk action và relationship khi chúng nằm trong phạm vi. Kiểm tra Seller A với tài nguyên cửa hàng A/B, Customer gọi chức năng Seller/Admin và Seller gọi chức năng Admin; ghi rõ hành vi cho phép/từ chối mong đợi theo quy tắc thực tế.
- Kiểm tra injection, XSS qua dữ liệu được render, lộ thông tin, upload không kiểm soát và giới hạn truy cập khi có luồng tương ứng. Không suy diễn lỗ hổng chỉ vì thiếu một middleware cụ thể nếu cơ chế khác đã bảo vệ.

### Validation, quan hệ và MySQL

- Đối chiếu kiểu, required/nullable, giới hạn, enum và validation nghiệp vụ với cách dùng dữ liệu; `exists` xác nhận tồn tại không thay thế kiểm tra quyền sở hữu.
- Kiểm tra khóa ngoại, hướng quan hệ, cardinality, casts, eager loading, soft delete và xử lý tài nguyên bị thiếu từ Model và migration hiện có. Phân biệt schema trong repository với schema database đang chạy chưa được xác minh.
- Tìm N+1, truy vấn không giới hạn, phân trang, index phù hợp truy vấn, transaction thiếu quanh thao tác nhiều bước và race condition có kịch bản cụ thể. Không khẳng định hiệu năng đo được khi chưa có số liệu.
- Xem tính tương thích MySQL từ SQL, kiểu dữ liệu và collation có liên quan. Test SQLite trong bộ nhớ không chứng minh đầy đủ hành vi MySQL. Chỉ đề xuất thay đổi database và yêu cầu phê duyệt riêng trước triển khai.

### Vue 3, Router, Axios và hợp đồng API

- Kiểm tra reactivity, lifecycle, props/emits, async/race, loading/error state, đăng ký/hủy tài nguyên và render dữ liệu không tin cậy trong Vue component.
- Đối chiếu Router, guard, chuyển hướng sau đăng nhập/đăng xuất và trạng thái vai trò; frontend không phải ranh giới bảo mật.
- Đối chiếu Axios instance/interceptor, base URL, endpoint, HTTP method, header xác thực hoặc credentials, query/body, response envelope, mã trạng thái và xử lý lỗi validation/xác thực/phân quyền với backend thực tế.
- Với lỗi tích hợp, chỉ rõ cả phía gửi và phía nhận cùng giá trị không khớp. Đề xuất cách sửa giữ hợp đồng hiện có; nếu phải đổi hợp đồng, nêu consumer bị ảnh hưởng và yêu cầu phê duyệt trước triển khai.

## Phân loại và báo cáo

Sắp xếp phát hiện theo mức độ và hậu quả thực tế, cân nhắc điều kiện khai thác:

| Mức độ | Tiêu chí |
| --- | --- |
| Critical | Chiếm quyền hệ thống hoặc lộ/hủy dữ liệu diện rộng, có đường khai thác cụ thể và ít điều kiện hạn chế. |
| High | Vượt quyền đáng kể, đọc/ghi trái phép dữ liệu cửa hàng khác hoặc hỏng luồng nghiệp vụ quan trọng. |
| Medium | Sai hành vi, validation hoặc hiệu năng gây ảnh hưởng đáng kể nhưng giới hạn phạm vi hoặc cần điều kiện cụ thể. |
| Low | Lỗi tác động nhỏ, có bằng chứng; không dùng cho sở thích format hay refactor tùy ý. |

Mỗi phát hiện gồm:

- **[Mức độ] Tiêu đề:** mô tả hành vi lỗi cụ thể.
- **Vị trí và bằng chứng:** đường dẫn file, dòng hoặc đoạn dòng hẹp, trích đoạn tối thiểu nếu cần; dẫn thêm phía frontend/backend hoặc lớp bảo vệ liên quan để chứng minh luồng. Dùng số dòng của phiên bản đang review.
- **Nguyên nhân và tác động:** điều kiện kích hoạt, vai trò/tài nguyên bị ảnh hưởng; nêu rõ đã xác nhận từ code hay còn cần kiểm chứng, không tuyên bố đã tái hiện nếu chưa chạy.
- **Đề xuất sửa:** thay đổi tối thiểu, file dự kiến ảnh hưởng; chỉ ra nhu cầu phê duyệt riêng nếu liên quan API, database hoặc môi trường.
- **Cách kiểm thử:** đầu vào/thao tác, kết quả mong đợi và trường hợp hồi quy. Với phân quyền, bao gồm trường hợp được phép, bị từ chối và truy cập chéo cửa hàng phù hợp.

Tách giả thuyết/câu hỏi còn thiếu dữ kiện khỏi danh sách lỗi đã xác nhận. Kết thúc bằng phạm vi đã đọc, kiểm tra đã thực hiện và kiểm thử chưa chạy cùng giới hạn. Nếu không thấy lỗi đủ bằng chứng, nói rõ điều đó và rủi ro chưa kiểm chứng; không khẳng định toàn hệ thống an toàn.
