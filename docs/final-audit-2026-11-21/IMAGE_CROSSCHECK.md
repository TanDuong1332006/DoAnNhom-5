# Đối chiếu ảnh Website ↔ MySQL ↔ Laravel Storage

UAT 5198 → 8045, MySQL riêng. `home.json` là quan sát DOM browser; HTTP và checksum được kiểm độc lập. Dòng ảnh 515 là upload tổng hợp có sẵn từ kiểm thử bàn giao trước, không thuộc 167 ảnh gốc trong ZIP.

| ID | Tên | Gallery ID | MySQL main | MySQL gallery | HTTP/file | Browser |
|---|---|---|---|---|---|---|
| 1 | Áo Thun Nam Cổ Tròn Cotton Supima Cao Cấp | 436 | `/api/product-images/1/illustration-f8d55e60-803f-4354-a03c-93cc613b1b8c.jpg` | `/api/product-images/1/illustration-f8d55e60-803f-4354-a03c-93cc613b1b8c.jpg` | 200; checksum PASS | PASS |
| 1 | Áo Thun Nam Cổ Tròn Cotton Supima Cao Cấp | 515 | `/api/product-images/1/illustration-f8d55e60-803f-4354-a03c-93cc613b1b8c.jpg` | `http://127.0.0.1:8045/api/product-images/1/illustration-45e37174-e089-4c73-a65e-78c1501c7c73.jpg` | 200; checksum PASS | PASS |
| 22 | Áo Sơ Mi Nam Flannel Kẻ Caro Phong Cách Grunge | 358 | `/api/product-images/22/illustration-6abdb532-5aac-45b0-9c9d-c8c909ee3939.jpg` | `/api/product-images/22/illustration-6abdb532-5aac-45b0-9c9d-c8c909ee3939.jpg` | 200; checksum PASS | PASS |
| 43 | Quần Short Nam Vải Đũi Linen Đi Biển & Du Lịch | 377 | `/api/product-images/43/illustration-cf48d348-e596-4167-8661-905ec510a9d3.jpg` | `/api/product-images/43/illustration-cf48d348-e596-4167-8661-905ec510a9d3.jpg` | 200; checksum PASS | PASS |
| 85 | Áo Corset Nữ Định Hình Eo Gợi Cảm Phối Ren | 424 | `/api/product-images/85/illustration-6e65eebe-e6cf-4771-8e5f-3bf61b368dcd.jpg` | `/api/product-images/85/illustration-6e65eebe-e6cf-4771-8e5f-3bf61b368dcd.jpg` | 200; checksum PASS | PASS |
| 127 | Giày Slip-On Nữ Vải Canvas Thêu Họa Tiết Hoa Nhỏ | 466 | `/api/product-images/127/illustration-14b874cd-406d-49b0-8f1c-7a543a323a53.jpg` | `/api/product-images/127/illustration-14b874cd-406d-49b0-8f1c-7a543a323a53.jpg` | 200; checksum PASS | PASS |
| 172 | Áo polo | không có | `/product-placeholder.svg` | `NULL` | 200; checksum PASS | PASS |

File Storage tuyệt đối và URL backend tương ứng nằm trong `image-crosscheck.json`. ID 172 dùng SVG local; không coi placeholder là ảnh minh họa hợp lệ. Biến thể fixture 510 → gallery 436 được xác nhận trong `http-results.json`.
