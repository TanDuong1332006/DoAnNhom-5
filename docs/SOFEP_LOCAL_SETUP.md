# Chạy thử SOFEP từ nhánh của Dương

Yêu cầu PHP 8.3+, Composer, Node.js 22+ và MySQL local. Tạo một database MySQL riêng để chạy thử.

```sh
git clone --branch nhanh-cua-duong https://github.com/TanDuong1332006/DoAnNhom-5.git
cd DoAnNhom-5/themmoik27be
composer install
```

Sao chép `.env.example` thành `.env` nếu chưa có. Điền `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` và `DB_PASSWORD` theo MySQL của bạn. Với file môi trường mới, chạy `php artisan key:generate` một lần.

```sh
php artisan migrate
php artisan db:seed
php artisan serve --host=127.0.0.1 --port=8000
```

Chỉ chạy `db:seed` khi muốn nạp dữ liệu mẫu vào database demo mới. Tài khoản Admin demo nằm trong `database/seeders/DatabaseSeeder.php`. Dùng trang đăng ký để tạo tài khoản Customer thử nghiệm.

Ở terminal khác, từ thư mục gốc repository:

```sh
cd Fe
npm ci
npm run dev -- --port 5173
```

Mở [cửa hàng](http://localhost:5173/) hoặc [trang Admin](http://localhost:5173/admin). Frontend mặc định gọi backend tại `http://127.0.0.1:8000/api`.

Migration tạo cấu trúc cho voucher, giữ tồn kho và ảnh theo màu. Dữ liệu nhập riêng trên máy Dương, bao gồm ảnh tải lên, liên kết màu của áo polo và lịch sử đã dọn, thuộc database và storage local. Trên máy khác, dùng dữ liệu mẫu hoặc tạo sản phẩm và tải ảnh qua Admin để thử tính năng.

Chạy `node --test tests/*.test.mjs` và `npm run build` trong `Fe` để kiểm tra frontend. Backend có bộ `php artisan test` sử dụng SQLite trong bộ nhớ theo `phpunit.xml`.
