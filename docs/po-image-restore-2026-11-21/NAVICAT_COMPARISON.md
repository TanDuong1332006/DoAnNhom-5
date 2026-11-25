# Website PO ↔ MySQL ↔ Laravel Storage

Ngày 21/11/2026. Đọc database `doan_nhom_5` trực tiếp bằng PDO READ ONLY; không thao tác UI Navicat hoặc sửa dữ liệu. Các trường dưới là giá trị Navicat sẽ đọc từ cùng database. Website thực tế là localhost:5173, backend 127.0.0.1:8000. Ảnh là minh họa catalog giả lập.

| ID | Tên | ID gallery khớp ảnh chính | URL MySQL = API | HTTP/checksum | Website thực tế |
|---|---|---|---|---|---|
|1|Áo Thun Nam Cổ Tròn Cotton Supima Cao Cấp|436|http://127.0.0.1:8000/api/product-images/1/illustration-f8d55e60-803f-4354-a03c-93cc613b1b8c.jpg|200; SHA-256 khớp file|[Chi tiết](http://localhost:5173/san-pham/ao-thun-nam-co-tron-cotton-supima) — đã tải|
|3|Quần Jean Nam Slimfit Denim Co Giãn Nhẹ|364|http://127.0.0.1:8000/api/product-images/3/illustration-cc8516e1-45f2-4d7e-aa31-7ed16d81e21e.jpg|200; SHA-256 khớp file|[Chi tiết](http://localhost:5173/san-pham/quan-jean-nam-slimfit-denim) — đã tải|
|4|Đầm Xòe Nữ Cổ Vuông Thanh Lịch Dự Tiệc|409|http://127.0.0.1:8000/api/product-images/4/illustration-4a74edd6-e428-46a3-87ed-afb7eeefaba4.jpg|200; SHA-256 khớp file|[Chi tiết](http://localhost:5173/san-pham/dam-xoe-nu-co-vuong-thanh-lich) — đã tải|
|7|Giày Sneaker Nam Nữ Thể Thao Air Cushion|382|http://127.0.0.1:8000/api/product-images/7/illustration-0e8041d0-658e-490d-8959-ee68fd5b49e9.jpg|200; SHA-256 khớp file|[Chi tiết](http://localhost:5173/san-pham/giay-sneaker-the-thao-air-cushion) — đã tải|
|8|Túi Xách Da Nữ Đeo Chéo Khóa Mạ Vàng|383|http://127.0.0.1:8000/api/product-images/8/illustration-e86eafad-9735-4f6f-affb-49ffcbac129f.jpg|200; SHA-256 khớp file|[Chi tiết](http://localhost:5173/san-pham/tui-xach-da-nu-deo-cheo) — đã tải|

## ID 1 — Áo Thun Nam Cổ Tròn Cotton Supima Cao Cấp

- `san_phams.hinh_anh`: http://127.0.0.1:8000/api/product-images/1/illustration-f8d55e60-803f-4354-a03c-93cc613b1b8c.jpg
- `anh_san_phams`: ID 436; URL tương ứng giữ nguyên bằng URL trên.
- File vật lý: `D:\do_an_nhom\code_chinh\DoAnNhom-5-GITHUB\themmoik27be\storage\app\public\products\1\illustration-f8d55e60-803f-4354-a03c-93cc613b1b8c.jpg`.
- HTTP Laravel: http://127.0.0.1:8000/api/product-images/1/illustration-f8d55e60-803f-4354-a03c-93cc613b1b8c.jpg — 200, SHA-256 `18cac68cf57ce6b2bd2794834575343b5dfde1e2bee16bf77084385d01c38afc`.
- Browser: http://localhost:5173/san-pham/ao-thun-nam-co-tron-cotton-supima; ảnh chính và thumbnail tải được. [Ảnh chụp](product-1.png).

## ID 3 — Quần Jean Nam Slimfit Denim Co Giãn Nhẹ

- `san_phams.hinh_anh`: http://127.0.0.1:8000/api/product-images/3/illustration-cc8516e1-45f2-4d7e-aa31-7ed16d81e21e.jpg
- `anh_san_phams`: ID 364; URL tương ứng giữ nguyên bằng URL trên.
- File vật lý: `D:\do_an_nhom\code_chinh\DoAnNhom-5-GITHUB\themmoik27be\storage\app\public\products\3\illustration-cc8516e1-45f2-4d7e-aa31-7ed16d81e21e.jpg`.
- HTTP Laravel: http://127.0.0.1:8000/api/product-images/3/illustration-cc8516e1-45f2-4d7e-aa31-7ed16d81e21e.jpg — 200, SHA-256 `82a5c4b2a065f4ea032109baa2098d3a311775ef2558cc62d2c19fd18fb7387e`.
- Browser: http://localhost:5173/san-pham/quan-jean-nam-slimfit-denim; ảnh chính và thumbnail tải được. [Ảnh chụp](product-3.png).

## ID 4 — Đầm Xòe Nữ Cổ Vuông Thanh Lịch Dự Tiệc

- `san_phams.hinh_anh`: http://127.0.0.1:8000/api/product-images/4/illustration-4a74edd6-e428-46a3-87ed-afb7eeefaba4.jpg
- `anh_san_phams`: ID 409; URL tương ứng giữ nguyên bằng URL trên.
- File vật lý: `D:\do_an_nhom\code_chinh\DoAnNhom-5-GITHUB\themmoik27be\storage\app\public\products\4\illustration-4a74edd6-e428-46a3-87ed-afb7eeefaba4.jpg`.
- HTTP Laravel: http://127.0.0.1:8000/api/product-images/4/illustration-4a74edd6-e428-46a3-87ed-afb7eeefaba4.jpg — 200, SHA-256 `f069a29af1c3e8bca5b4d743ba9ad2eff6d8dc18bf09ffc5aff8d8e5a2ea857f`.
- Browser: http://localhost:5173/san-pham/dam-xoe-nu-co-vuong-thanh-lich; ảnh chính và thumbnail tải được. [Ảnh chụp](product-4.png).

## ID 7 — Giày Sneaker Nam Nữ Thể Thao Air Cushion

- `san_phams.hinh_anh`: http://127.0.0.1:8000/api/product-images/7/illustration-0e8041d0-658e-490d-8959-ee68fd5b49e9.jpg
- `anh_san_phams`: ID 382; URL tương ứng giữ nguyên bằng URL trên.
- File vật lý: `D:\do_an_nhom\code_chinh\DoAnNhom-5-GITHUB\themmoik27be\storage\app\public\products\7\illustration-0e8041d0-658e-490d-8959-ee68fd5b49e9.jpg`.
- HTTP Laravel: http://127.0.0.1:8000/api/product-images/7/illustration-0e8041d0-658e-490d-8959-ee68fd5b49e9.jpg — 200, SHA-256 `436d47c65c245774a8aae2dc945a8f743309af581caeced9acebb77a65fb574f`.
- Browser: http://localhost:5173/san-pham/giay-sneaker-the-thao-air-cushion; ảnh chính và thumbnail tải được. [Ảnh chụp](product-7.png).

## ID 8 — Túi Xách Da Nữ Đeo Chéo Khóa Mạ Vàng

- `san_phams.hinh_anh`: http://127.0.0.1:8000/api/product-images/8/illustration-e86eafad-9735-4f6f-affb-49ffcbac129f.jpg
- `anh_san_phams`: ID 383; URL tương ứng giữ nguyên bằng URL trên.
- File vật lý: `D:\do_an_nhom\code_chinh\DoAnNhom-5-GITHUB\themmoik27be\storage\app\public\products\8\illustration-e86eafad-9735-4f6f-affb-49ffcbac129f.jpg`.
- HTTP Laravel: http://127.0.0.1:8000/api/product-images/8/illustration-e86eafad-9735-4f6f-affb-49ffcbac129f.jpg — 200, SHA-256 `ecd5d0627488b0dc7bdbe3979583218124d374f15dd4b8ce4d57eed723e72dad`.
- Browser: http://localhost:5173/san-pham/tui-xach-da-nu-deo-cheo; ảnh chính và thumbnail tải được. [Ảnh chụp](product-8.png).

## ID 172

Nguồn hiện tại vẫn giữ trong database. Resolver trả `/product-placeholder.svg`, browser localhost:5173 tải 200. Không gọi nguồn HOLD để thay bằng ảnh khác.
