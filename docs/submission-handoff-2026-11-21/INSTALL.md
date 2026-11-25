# Cài SOFEP từ gói bàn giao

Gói gồm source working tree đã kiểm thử (chưa commit/push), lockfile, tests, catalog demo và 167 ảnh local. ID 172 dùng placeholder. Không có database/khách/đơn thật, tài khoản hoặc mật khẩu cài sẵn. Bộ tạo tài khoản chạy riêng trên máy người cài.

## 1. Chuẩn bị công cụ

- PHP theo `themmoik27be/composer.json`/lockfile: PHP 8.3 trở lên tương thích lockfile; cần PDO MySQL, PDO SQLite cho tests, mbstring, OpenSSL, cURL, fileinfo, XML/DOM và các extension Composer yêu cầu.
- Composer 2; Node.js LTS còn được hỗ trợ và npm có thể đọc package-lock v3 (môi trường nghiệm thu dùng Node 24); MySQL 8, tài khoản local có quyền tạo database demo. Navicat là tùy chọn để xem bảng.
- Dùng bản cài chính thức của PHP/Composer/Node/MySQL hoặc môi trường học tập tương đương. Kiểm tra `php -v`, `composer --version`, `node --version`, `npm --version`; bật MySQL trước bước setup. Không chuyển sang SQLite cho demo ứng dụng.

Không có vendor/node_modules trong ZIP: lần đầu cài dependency cần Internet hoặc cache package hợp lệ. Cài đúng lockfile, không dùng update.

## 2. Giải nén và cài dependency

Giải nén ZIP vào thư mục mới, không đè source/môi trường đang dùng. Kiểm SHA-256 ZIP theo file đi kèm. Nếu tải source GitHub HEAD riêng, phải đối chiếu SOURCE_MANIFEST: các bản vá working tree chưa có trên HEAD; ưu tiên đúng ZIP đã nghiệm thu.

```text
cd <thu-muc-giai-nen>/themmoik27be
composer install --prefer-dist --no-interaction --no-scripts
composer check-platform-reqs
cd ../Fe
npm ci --ignore-scripts --no-audit --fund=false
```

Không chạy `composer setup`, `db:seed`, `migrate:fresh` hoặc importer SQL từ database PO. Seeder cũ bị loại khỏi bộ bàn giao vì không dùng cho bộ demo này. Script Composer có thể tự tạo môi trường/migration nếu chạy nhầm; hướng dẫn dùng `--no-scripts` và setup có kiểm tra riêng.

## 3. Tạo MySQL demo, môi trường và ảnh

Từ thư mục gốc giải nén, đặt biến môi trường tiến trình bằng credential MySQL **của máy người cài**. Ví dụ PowerShell (nhập giá trị thật tại máy mình, không gửi vào Git):

```powershell
$env:SOFEP_DEMO_DB_HOST = '127.0.0.1'
$env:SOFEP_DEMO_DB_PORT = '3306'
$env:SOFEP_DEMO_DB_USERNAME = 'root'
# Đặt SOFEP_DEMO_DB_PASSWORD tại máy mình nếu MySQL yêu cầu.
php handoff/setup.php 8000 5173
```

Không nhập database `doan_nhom_5`. Installer luôn tạo database mới `sofep_demo_<ngày>_<ngẫu nhiên>`, từ chối bản giải nén đã có `.env`/config cache, kiểm checksum rồi chạy migration, import 7 bảng catalog và copy 167 file. Nó tạo `.env` FE/BE riêng và APP_KEY mới; không lấy file cấu hình trên máy PO. Mọi dữ liệu là catalog demo. Kết quả ở `install-result.json`; tài khoản Admin/Customer và mật khẩu ngẫu nhiên chỉ sinh trong `themmoik27be/storage/app/private/demo-access.json`, không chia sẻ file này.

Nếu dùng port khác: `php handoff/setup.php 8045 5198` trên bản giải nén mới. API URL và CORS sẽ theo hai port này. Không đặt API localhost của máy PO vào catalog: URL ảnh trong MySQL là `/api/product-images/...` tương đối.

## 4. Chạy website

Mở hai terminal:

```text
cd <thu-muc-giai-nen>/themmoik27be
php artisan serve --host=127.0.0.1 --port=8000 --no-reload
```

```text
cd <thu-muc-giai-nen>/Fe
npm run dev -- --host 127.0.0.1 --port 5173 --strictPort
```

Mở `http://127.0.0.1:5173`. Nếu setup chọn port khác, thay lệnh server và URL theo `install-result.json`. Login Customer ở `/dang-nhap`, Admin ở `/admin/login`; lấy credential tạo tại máy mình từ file private. Thanh toán là mô phỏng, dùng COD để kiểm tra, không chuyển tiền thật. Không cấu hình mail/bank/Google OAuth thật cho bài demo này.

Kiểm tra trang chủ, tìm kiếm, chi tiết, chọn màu/size, thêm giỏ, checkout và trang Admin. 167 ảnh chính tải local, ID 172 placeholder; gallery/ảnh biến thể fixture và fallback không được mất. Không có nhãn ảnh; trạng thái HOLD và phân loại nguồn nội bộ vẫn giữ.

## 5. Chứng minh bằng Navicat

Kết nối MySQL local của mình bằng Navicat, chọn đúng tên database trong `install-result.json`, không chọn database PO.

- Xem `san_phams`: 168 sản phẩm; đối chiếu ID/tên và `hinh_anh`.
- Xem `anh_san_phams`: 167 gallery, nối `id_san_pham`; URL là `/api/product-images/...`.
- Xem `chi_tiet_san_phams`: 501 dòng gồm 500 catalog và 1 fixture ảnh tổng hợp ID 510 → gallery 436 của sản phẩm 1. Đây là fixture để chứng minh ảnh biến thể; không phải 9 ảnh biến thể gốc của ID 172.
- Mở URL ảnh bằng backend đang chạy, ví dụ `http://127.0.0.1:8000` + URL tương đối ở MySQL. File nằm trong `themmoik27be/storage/app/public/products/<ID>/`; controller Laravel phục vụ file, không cần copy thư mục upload từ máy PO.
- ID 172 có `/product-placeholder.svg`, không có gallery phân phối. Không tự gán ảnh khác.

`demo/manifest.json` ghi checksum, provenance và nguồn nội bộ. `SOURCE_MANIFEST.json` ghi checksum source và các file đã ẩn thông tin liên hệ/tài khoản thanh toán trong bản đóng gói. Không xóa các thông tin kiểm kê này.

## 6. Kiểm tra chất lượng và lỗi thường gặp

```text
cd Fe
node --test tests/*.test.mjs
npm run build
cd ../themmoik27be
php artisan test
```

BE tests dùng SQLite memory, không thay database MySQL demo. Build không thay thế kiểm tra website. API lỗi: kiểm backend/port/CORS và MySQL service trước khi sửa dữ liệu. Ảnh 404: kiểm manifest, import và Storage; không đổi URL database để che lỗi. Sau đổi frontend API env cần khởi động lại Vite/build.

Chưa cấu hình các kênh email thật/Google OAuth ngoài; chúng không phải luồng demo offline. Ảnh sản phẩm được đóng local, nhưng toàn website có thể còn tài nguyên ngoài; không coi đây là chứng nhận offline nếu chưa test ngắt mạng thực tế. Không dùng tài khoản hoặc đơn thật để demo.
