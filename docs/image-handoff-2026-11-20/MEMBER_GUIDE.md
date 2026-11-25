# SOFEP — hướng dẫn chạy demo trên máy thành viên

Bản cập nhật API/ảnh đã kiểm thử FE **5194**, BE **8042**; mặc định cũ FE **5173**, BE **8000** vẫn được giữ. Bản sửa hiện ở working tree, chưa commit/push: thành viên phải nhận đúng bản source/bản vá được nhóm duyệt, không giả định GitHub HEAD đã có thay đổi.

## 1. Clone và chuẩn bị dependency

Clone vào thư mục mới, nhận bản sửa và `sofep-image-demo.zip` cùng checksum qua kênh được PO cho phép. Dùng PHP/MySQL và dependency đúng các lockfile; không chạy `composer setup` vì nó tự tạo môi trường, khóa và migration. Khi được nhóm cho phép cài dependency, có thể dùng `composer install --no-scripts` trong `themmoik27be/` và `npm ci` trong `Fe/`; không nâng phiên bản hoặc sửa lockfile. Đợt nghiệm thu này dùng dependency đã có, chưa test cài mới qua Internet.

## 2. Import database và ảnh vào UAT mới

Kiểm tra SHA-256 ZIP với `.sha256`, giải nén ra thư mục riêng. Importer nguyên bản yêu cầu Laravel clone **chưa có `.env` và config cache**, thư mục runtime đích chưa tồn tại. Vì vậy **import trước khi tạo `.env` Laravel**.

Trong terminal local riêng, khai báo `SOFEP_DEMO_DB_HOST`, `SOFEP_DEMO_DB_PORT`, `SOFEP_DEMO_DB_USERNAME`, `SOFEP_DEMO_DB_PASSWORD` bằng credential MySQL trên máy thành viên; không dùng hoặc chuyển credential máy PO. User cần quyền tạo database mới. Tại thư mục ZIP đã giải nén:

```powershell
php import.php "D:/SOFEP-clean/themmoik27be" "D:/SOFEP-UAT/runtime-new"
```

Script tạo database ngẫu nhiên `sofep_image_restore_...`, chạy migration chỉ vào database mới và nhập bảy bảng catalog. Lấy tên database đích từ `runtime-new/session.json`. Không dùng `doan_nhom_5`, database Barber hoặc database có sẵn. ZIP không có tài khoản, đơn hàng, token hay mật khẩu; đăng nhập cần tài khoản UAT tổng hợp mới được nhóm chuẩn bị, không lấy tài khoản thật.

Ảnh sau import nằm ở `runtime-new/storage/app/public`. Nếu dùng harness `server.php` như README trong ZIP, runtime được nạp tự động. Nếu chạy Laravel thông thường bằng Artisan ở bước sau, **copy** ảnh từ `media/products` trong ZIP vào `themmoik27be/storage/app/public/products` của **clone mới**. Không di chuyển/xóa file nguồn và không copy vào storage đang sử dụng của PO. Đối chiếu từng SHA-256 theo manifest. Copy `product-placeholder.svg` của ZIP vào `themmoik27be/public/` nếu muốn kiểm tra placeholder trực tiếp từ backend; frontend đã có file trong `Fe/public/`.

## 3. Cấu hình `.env` trên clone mới

Sau import, tạo `.env` Laravel của clone mới từ `.env.example`, đặt các giá trị phù hợp máy thành viên:

```dotenv
APP_ENV=local
APP_URL=http://127.0.0.1:8042
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=<ten_database_sofep_image_restore_vua_tao>
DB_USERNAME=<user_mysql_local>
DB_PASSWORD=<mat_khau_mysql_local>
CORS_ALLOWED_ORIGINS=http://127.0.0.1:5194,http://localhost:5194,http://127.0.0.1:5173,http://localhost:5173
CACHE_STORE=array
SESSION_DRIVER=array
MAIL_MAILER=array
QUEUE_CONNECTION=sync
```

Thay placeholder bằng cấu hình local, không chia sẻ `.env`. Sinh `APP_KEY` riêng bằng `php artisan key:generate` **chỉ tại clone UAT**. Không chạy migration/seeder vào database thật; schema demo đã được importer tạo. Không dùng khóa/mật khẩu thật trong tài liệu. Nếu cấu hình đang cache, xử lý cache chỉ trong clone UAT theo quy trình nhóm.

Trong `Fe/.env.local` của clone mới:

```dotenv
VITE_API_URL=http://127.0.0.1:8042/api
```

Giá trị gồm `/api`, không có query, fragment hoặc credential. Origin CORS là scheme + host + port frontend, không có `/` cuối/path; không dùng wildcard. `localhost` và `127.0.0.1` là hai origin khác nhau. Nếu không đặt `VITE_API_URL`, frontend mặc định dùng `http://127.0.0.1:8000/api`; CORS mặc định vẫn chấp nhận local 5173 và localhost 3000 như trước.

## 4. Chạy website và kiểm tra

Terminal backend tại `themmoik27be/` của clone mới:

```powershell
php artisan serve --host=127.0.0.1 --port=8042
```

Terminal frontend tại `Fe/`:

```powershell
npm run dev -- --host 127.0.0.1 --port 5194 --strictPort
```

Mở `http://127.0.0.1:5194/`. Đổi port phải cập nhật tương ứng `VITE_API_URL`, `APP_URL` và origin frontend trong CORS; khởi động lại Vite sau khi đổi env. Không chuyển qua backend thật 8000 để né lỗi. Kiểm tra danh mục/sản phẩm, tìm kiếm, chi tiết, chọn màu/size, thêm giỏ, đăng nhập, checkout và Admin theo vai trò trong UAT. Chỉ dùng dữ liệu giao hàng/đơn hàng tổng hợp.

Dùng browser Network xác nhận request API/ảnh nội bộ tới backend mới. `/api/product-images/...` phục vụ ảnh upload bằng Laravel, không cần symlink `public/storage`; URL `/storage/...` truyền thống cần cấu hình link phù hợp **trên clone**. Nếu ảnh 404, kiểm tra file nằm đúng storage của chế độ server đang chạy, không chỉ đúng URL trong database.

Có thể dùng `node verify.mjs <BE-origin> <report.json>` trong ZIP ở **chế độ harness** (`server.php`, trước khi tạo `.env`); verifier có route `__demo` nên không dùng nguyên bản cho Artisan thông thường. Với Artisan, kiểm tra `/api/client/san-phams?all=1`, `/api/client/demo/san-pham/1`, URL ảnh main/gallery/variant trả về, placeholder và CORS trực tiếp. Đợt này cả hai chế độ đều đã được kiểm tra qua HTTP.

## 5. Hiểu đúng ảnh demo

167 file AI được giữ làm tư liệu demo/transport, chưa có bằng chứng đúng SKU nên frontend bán hàng tiếp tục dùng placeholder. ID 172 chưa có ảnh trong gói; hai nguồn cũ của nó đang HOLD, không tự đưa lại vào hiển thị. Kết quả hiện tại **168 sản phẩm còn placeholder, 0 sản phẩm có ảnh đúng SKU đã được xác minh**. Đây không còn là lỗi kết nối API: các ảnh trong gói vẫn tải được qua HTTP.

Ảnh được PO duyệt đúng SKU/quyền dùng có thể upload mới với loại photo và hiển thị bình thường; không đổi tên file illustration để vượt bộ lọc. 20 file HOLD, URL gốc và 23 URL lịch sử đơn được bảo toàn. Không sửa snapshot đơn khi thay ảnh catalog.

Offline thực tế chưa nghiệm thu: ảnh local/placeholder cần server và MySQL local còn chạy; ảnh URL ngoài cần Internet. CSP chặn nguồn ngoài trong test không tương đương tắt Internet. Không tự xóa ảnh, reset database, commit/push/deploy hoặc chỉnh Jira/Proposal theo hướng dẫn này.
