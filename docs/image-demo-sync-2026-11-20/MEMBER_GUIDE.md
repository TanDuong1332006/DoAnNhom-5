# Chạy demo ảnh minh họa SOFEP

Quyết định PO mới: catalog 168 sản phẩm là giả lập; không cần 8 đợt duyệt ảnh thật. Ảnh trong gói là minh họa, không phải cam kết hàng thật/SKU/màu.

## Source và dependency

Clone source và dùng phiên bản có các bản vá API/config/ảnh đang được nghiệm thu. **Các bản vá hiện chưa commit/push; clone HEAD một mình chưa có chúng.** Không báo đây là bộ source đã phát hành. Giữ Composer/npm đúng lockfile; không chạy `composer setup` vì script có migration tự động.

Chuẩn bị PHP/MySQL và dependency theo README dự án. Thử nghiệm này dùng archive source sạch cùng máy cho backend, dependency có sẵn; chưa kiểm tra cài dependency mới trên máy vật lý khác. Frontend chạy bản sao độc lập của source working tree đã vá, không có `.env` PO, chỉ dùng chung dependency qua junction.

## Import catalog và ảnh

1. Lấy `docs/image-clone-2026-11-20/sofep-image-demo.zip` và checksum đi kèm. SHA-256 gói đã kiểm tra: `972561984c18aed8a24928ab9fad6364def20198b457d845033ab38559b6a7b4`.
2. Giải nén vào thư mục riêng. Đọc README trong gói. Dùng source backend sạch không `.env`/config cache cho importer của gói; cấu hình credential MySQL **của thành viên**, bằng biến môi trường `SOFEP_DEMO_DB_HOST`, `SOFEP_DEMO_DB_PORT`, `SOFEP_DEMO_DB_USERNAME`, `SOFEP_DEMO_DB_PASSWORD` trong tiến trình local. Không gửi credential vào báo cáo/Git.
3. Chạy `php <bundle>/import.php <clean-source>/themmoik27be <new-runtime>` với runtime chưa tồn tại. Importer tạo database prefix `sofep_image_restore_...`, migration và catalog demo riêng, kiểm checksum và copy 167 ảnh vào Storage riêng. Không trỏ nó vào database chính hoặc import SQL dump chính. Gói không chứa tài khoản/khách/đơn/token; fixture ảnh biến thể ID 510 là tổng hợp.
4. Đặt `SOFEP_DEMO_SESSION=<new-runtime>/session.json`, chạy Laravel harness trong gói bằng `php -S 127.0.0.1:8043 <bundle>/server.php`. Harness boot Laravel thật, guard database và dùng Storage/cache/session riêng. Nó là bộ demo đã kiểm thử, không phải hướng dẫn thay `.env` production.
5. CORS cần cho origin frontend thực tế. UAT nhiệm vụ này dùng `router.php` đặt allowlist đúng `http://127.0.0.1:5195` sau khi boot Laravel, không wildcard và không sửa CORS chính. `serve.php` là launcher nội bộ đọc credential local hiện tại trong bộ nhớ; **thành viên không phụ thuộc launcher/credential máy PO**, dùng cấu hình MySQL riêng theo README gói.

## Chạy frontend

Trên frontend đã có bản vá, cấu hình tiến trình Vite:

```text
VITE_API_URL=http://127.0.0.1:8043/api
VITE_DEMO_ILLUSTRATIONS=true
```

Chạy Vite trên `127.0.0.1:5195`. Có thể dùng port khác nếu sửa API base và allowlist CORS UAT tương ứng. Hai biến trên là biến build/dev: cần khởi động lại Vite hoặc build lại khi đổi. Mặc định không bật `VITE_DEMO_ILLUSTRATIONS` vẫn giữ chính sách placeholder của môi trường hiện tại; không sửa `.env` PO.

`vite-uat.mjs` trong thư mục này là cấu hình thử local, đọc `fe_root` từ `uat.json`. Các đường TEMP trong `uat.json` là bằng chứng một lượt thử, không phải cấu hình portable để sao chép nguyên máy khác.

## Kiểm tra sau import

- Navicat phải mở đúng database UAT prefix trên: 168 sản phẩm, 167 gallery, 501 biến thể (500 catalog + 1 fixture). `san_phams.hinh_anh` và `anh_san_phams.duong_dan_anh` là URL tương đối `/api/product-images/...`, không origin 8000 của PO.
- Trang chủ/chi tiết hiển thị “Ảnh minh họa”. 167 ảnh chính demo có file, API và HTTP đúng checksum; ID 172 dùng placeholder. Không tự nối 9 biến thể ID 172 vào ảnh khác; chúng null trong demo vì ảnh nguồn chưa được phân phối.
- Gallery/biến thể fixture phải tải qua backend mới. URL thiếu trả 404 và component fallback placeholder; không đổi đường dẫn nguồn để che lỗi.
- Ảnh sản phẩm demo là local, không cần Unsplash/Pexels khi chạy. CSP UAT chặn tài nguyên ngoài và port 8000. Chưa test ngắt Internet thật, nên không chứng nhận toàn website offline.

Không xóa file hoặc bản ghi cũ khi nhập. Danh sách đề xuất dọn chỉ dùng để PO duyệt, không có lệnh DELETE trong bộ công cụ này.
