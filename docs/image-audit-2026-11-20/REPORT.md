# SOFEP — audit ảnh sản phẩm 20/11/2026

Thời điểm đọc catalog: 14:27:52 ngày 20/11/2026, Asia/Saigon. Nhánh `nhanh-cua-duong`; giữ nguyên các thay đổi có sẵn. Không commit/push/deploy, chỉnh Jira/Proposal, truy cập N.Cảnh Barber, sửa `.env`, xóa ảnh hoặc ghi vào `doan_nhom_5`.

## Kết quả và đơn vị đếm

168 sản phẩm đang bán, 514 bản ghi gallery, 500 biến thể; 9 biến thể có `id_anh_san_pham`. Có 355 cặp sản phẩm–URL sau loại trùng ảnh chính/gallery trong cùng sản phẩm, tương ứng 275 URL duy nhất. Không cộng hai đơn vị này với nhau.

| Nguồn URL đang tham chiếu trong catalog | URL duy nhất | Máy source hiện tại | Source sạch từ Git |
| --- | ---: | --- | --- |
| Laravel local upload | 187 | Có đủ 187 file trong storage | Thiếu cả 187 file |
| Unsplash | 87 | HTTP 200 trong lượt kiểm tra; bị frontend chặn vì chưa xác minh SKU | Cần Internet, vẫn bị frontend chặn |
| URL website bên ngoài khác | 1 | HTTP 200 trong lượt kiểm tra | Cần Internet và phụ thuộc website ngoài |
| Frontend asset làm ảnh catalog | 0 | Không có tham chiếu catalog | Không có tham chiếu catalog |
| Placeholder ghi trong catalog | 0 | Không thay dữ liệu thật | Có 1 file placeholder được Git theo dõi |

URL ngoài khác là `https://thoitrangbigsize.vn/wp-content/uploads/2025/03/den-3.jpg`; không phân loại nhầm thành Unsplash/local. Placeholder là trạng thái hiển thị frontend, không phải URL đã ghi lại vào database.

## Giai đoạn 1 — ảnh minh họa

167/168 sản phẩm có ảnh chính `illustration-UUID`. Trong 187 URL upload có 186 URL loại này. Manifest tại `docs/product-content/full-catalog/manifest.json` xác nhận nguồn AI, không phải ảnh chụp SKU. Trạng thái `review=approved` cũ không chứng minh ảnh đúng hàng thực tế. Không thể kết luận chính xác chất liệu, màu và chi tiết hàng thật chỉ từ tên sản phẩm hoặc prompt.

Bản vá `Fe/src/services/productImages.js` loại ảnh `illustration-UUID` khỏi các ảnh được chấp nhận. Card/Quick View nhận placeholder, gallery/ảnh theo màu không đưa các URL này vào trình xem. `Fe/src/components/shared/ProductImage.vue` hiển thị “Chờ ảnh đúng mẫu từ PO”. URL và file gốc giữ nguyên; không chỉ gỡ nhãn rồi tiếp tục hiển thị ảnh minh họa. Không thay API hoặc cơ chế phân quyền.

Danh sách cụ thể ID, tên, URL, vị trí sử dụng và phương án: `inventory.csv`; hàng đợi PO: `po-replacements.csv`. Cả Unsplash chưa xác minh tiếp tục bị chặn như trước. Không khẳng định mọi ảnh AI đều sai hình dáng; chúng chưa đủ bằng chứng để dùng làm ảnh đúng SKU.

Sản phẩm 172 “Áo polo” có một upload không mang tiền tố illustration, đã xem trực quan: ảnh áo polo trắng. Ảnh chính lại là URL website ngoài nêu trên. Giữ nguyên; cần PO xác nhận màu/mẫu và quyền sử dụng. Chưa có ảnh nào được xác nhận là ảnh chụp chính sản phẩm thực tế của SOFEP trong đợt audit này. HTTP 200 không đồng nghĩa đúng SKU.

## Giai đoạn 2 — UAT cô lập và giới hạn

- Source sạch: `git archive HEAD` tại commit `ece155286b4dbbd59903c1484d71fe26bc7616d5`, giải nén vào thư mục TEMP riêng; đường dẫn cụ thể tại `clone-location.json`. Đây là mô phỏng nội dung clone từ commit local, chưa đối chiếu HEAD remote GitHub. Không sao chép working tree, `.env`, dependency hoặc storage máy PO vào source sạch.
- Database riêng: `sofep_image_uat_20261120_072900_b82465`; chỉ có ba bảng fixture catalog ảnh với 168 sản phẩm, 514 gallery, 500 biến thể. Không có bảng khách hàng/đơn hàng, không sao chép token, tài khoản hoặc thông tin khách hàng. `demo-database.json` là bằng chứng. Đây là schema tối thiểu kiểm thử ảnh, KHÔNG phải môi trường Laravel hoàn chỉnh.
- Server file tạm chỉ đọc source sạch: kiểm tra từng đường dẫn vật lý của 187 upload, tất cả 404 vì file không được Git đóng gói. Đây không phải kết quả gọi route Laravel trên máy mới. Chỉ `.gitignore` của storage được theo dõi; 187 file tồn tại trên máy hiện tại nhưng không có trong source sạch. Chưa chứng minh các file này chỉ có duy nhất trên máy PO, vì không có quyền kiểm kê máy thành viên khác.
- Placeholder có trong source sạch, HTTP 200. Mô phỏng resource loader chặn mạng ngoài: placeholder vẫn HTTP 200, Unsplash bị từ chối; resolver bản vá chuyển đủ 167 ảnh chính minh họa về placeholder. Chưa ngắt mạng hệ điều hành hoặc xác minh render browser desktop/mobile.
- Lượt HTTP đọc trên môi trường hiện tại: 275 URL; 106 ảnh raster HTTP 200, 169 timeout, 0 phản hồi 404. Các timeout đều thuộc upload local; file vẫn có trên đĩa. Chưa xác định nguyên nhân timeout; không kết luận mất ảnh hoặc lỗi 404. Chi tiết từng URL nằm trong `inventory.json` → `checks` và cột `online_check` trong CSV. Không sửa server/cấu hình thật để khắc phục timeout trong đợt này.

Giai đoạn 2 hoàn thành kiểm tra nguồn file, fixture database và chính sách fallback; CHƯA hoàn thành nghiệm thu full-stack trên máy mới hoặc browser offline. Chưa cài dependency, chạy Laravel UAT đầy đủ hoặc kiểm tra upload/đổi ảnh qua giao diện Admin. Không dùng build hoặc mô phỏng loader làm bằng chứng cho các phần chưa kiểm tra này.

## Giai đoạn 3 — bảo toàn ảnh và lịch sử

`catalog.json` đọc bằng PDO trong transaction `START TRANSACTION READ ONLY`, không boot Laravel. Chỉ SELECT cột catalog cần thiết; dữ liệu đơn chỉ xuất tổng số tham chiếu theo URL, không xuất dòng đơn, mã đơn hay khách hàng.

187 file ảnh trong `storage/app/public` đều đang có tham chiếu catalog; không có file ảnh không tham chiếu trong phạm vi này. `storage-preservation.json` ghi file, SHA-256, dung lượng, URL và lý do giữ. Có 23 URL ảnh được tham chiếu tổng cộng 41 lần từ snapshot ảnh dòng đơn; tất cả hiện cũng có trong catalog. Không đổi hoặc xóa bất kỳ snapshot nào.

Ảnh biến thể phải bảo toàn bằng `chi_tiet_san_phams.id_anh_san_pham` → `anh_san_phams.id`; không chỉ kiểm tra URL ảnh chính. Bảng CSV ghi `variant_ids`, `gallery_ids`, `order_references` và `using`. `CheckoutService.php` lưu `hinh_anh` vào dòng đơn khi đặt hàng, nên đổi ảnh catalog về sau không cho phép xóa file cũ. Migration biến thể có `nullOnDelete`; đó không phải quyền xóa dữ liệu.

Chưa đề xuất xóa file/bản ghi nào. Kiểm kê này không chứng minh không còn tham chiếu từ mọi hệ thống/bản backup khác. Mọi phương án xóa thật vẫn cần PM và PO duyệt riêng.

## Phương án đóng gói để thành viên chạy độc lập

1. Giữ `Fe/public/product-placeholder.svg` trong Git; dùng `demo-catalog-portable.json` làm dữ liệu demo ảnh an toàn. Fixture này dùng placeholder và không có gallery sai mẫu, không phải chỉ thị cập nhật database thật. Không gán ảnh AI thành ảnh sản phẩm đã xác nhận.
2. PO cung cấp ảnh đúng SKU và xác nhận quyền sử dụng; lập manifest gồm product ID/SKU, variant nếu có, tên file tương đối, SHA-256, nguồn/quyền sử dụng và trạng thái duyệt. Chỉ những ảnh đã duyệt mới vào gói hiển thị demo.
3. Tạo gói media demo version hóa riêng, giữ cấu trúc `products/{productId}/{filename}` để giải nén vào storage UAT. Không kèm `.env`, log, backup database, tài khoản, token hoặc ảnh riêng tư. Gói bảo tồn ảnh cũ phải tách khỏi gói ảnh đã duyệt, giữ nguyên filename/hash và hạn chế phát hành khi chưa kiểm tra quyền sử dụng.
4. Database demo đầy đủ cần schema Laravel/MySQL đồng bộ và tài khoản tổng hợp, seed catalog được duyệt, không lấy khách hàng/đơn thật. Khi áp dụng phải chặn tên database thật, kiểm tra database đích trước mọi thao tác ghi. Fixture ba bảng hiện tại chỉ dành cho audit ảnh; không thể chạy toàn bộ app.
5. Trong database demo, chuyển URL loopback cố định sang đường dẫn ảnh tương đối hoặc sinh URL theo API origin của UAT. Cấu hình source API cũ còn hardcode localhost ở nhiều nơi; cần phạm vi riêng để chuẩn hóa triển khai đầy đủ, không âm thầm đổi hợp đồng API trong audit này. `/api/product-images/...` hiện không cần symlink; `/storage/...` truyền thống cần cấu hình phù hợp trên máy đích.
6. Nghiệm thu bổ sung: build/app trên máy sạch, ảnh chính/gallery/biến thể/Quick View/zoom, ảnh upload Laravel thật, 404 có fallback, Internet bật/tắt với loopback còn hoạt động. Kiểm tra Admin được upload; Customer/Seller/khách bị từ chối thao tác quản lý ảnh; không ghi upload vào storage hiện tại. Chưa phát hành gói lên GitHub hoặc deploy trong tác vụ này.

## File và kiểm tra đã thực hiện

Source sửa: `Fe/src/services/productImages.js`, `Fe/src/components/shared/ProductImage.vue`; test bổ sung: `Fe/tests/product-images.test.mjs`. Không sửa component chi tiết đang có thay đổi sẵn; nó dùng chung bộ lọc gallery.

File mới chỉ nằm trong thư mục báo cáo này: các script `read-catalog.php`, `prepare-demo.php`, `audit.mjs`, `clone-test.mjs`, `finalize.mjs`; CSV kiểm kê/hàng đợi; JSON catalog, inventory, summary, clone-location, clone-test, demo-database, demo-catalog-portable, storage-preservation, preservation-summary; tài liệu `REPORT.md`.

| Lệnh | Kết quả |
| --- | --- |
| `php docs/image-audit-2026-11-20/read-catalog.php` | Đạt, transaction chỉ đọc catalog nguồn |
| `php docs/image-audit-2026-11-20/prepare-demo.php` | Đạt, tạo fixture MySQL riêng; không có bảng khách/đơn |
| `node docs/image-audit-2026-11-20/audit.mjs` | Đã xuất đầy đủ bảng; có 169 timeout, không đánh dấu tất cả ảnh đạt |
| `node docs/image-audit-2026-11-20/clone-test.mjs` | Đạt các assertion placeholder/offline mô phỏng; 187 ảnh upload thiếu trong source sạch |
| `node docs/image-audit-2026-11-20/finalize.mjs` | Đạt, hash và kiểm kê 187 file |
| `node --test Fe/tests/product-images.test.mjs Fe/tests/product-detail-demo.test.mjs Fe/tests/catalog-session.test.mjs` | 25/25 đạt |
| `npm run build` tại `Fe/` | Đạt, Vite 4.4.8 |
| `php -l` cho hai script PHP mới | Đạt |

Chưa chạy `php artisan test`, test ghi upload thật, test phân quyền qua HTTP UAT hoặc browser offline: chưa có môi trường Laravel UAT đầy đủ đã được xác minh. Đọc route cho thấy quản lý ảnh nằm trong nhóm Admin; không thay backend hoặc kiểm soát quyền. Không coi kiểm tra tĩnh là bằng chứng runtime phân quyền. Database fixture và source TEMP được giữ để kiểm tra lại; không tự xóa.
