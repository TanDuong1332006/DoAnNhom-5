# Tiếp nhận ảnh thật và phương án nhập sau duyệt

Đây là quy trình đề xuất, chưa thực hiện tải ảnh, upload hoặc nhập dữ liệu.

## PO cung cấp theo từng đợt

- Mỗi thư mục `<đợt>/<SKU sản phẩm>/` gồm ảnh chính diện, mặt sau, chi tiết và nhãn nhận diện; giày/túi/phụ kiện bổ sung góc thấy cấu tạo cần đối chiếu. Ưu tiên ảnh PO tự chụp sản phẩm thực tế.
- Điền ID sản phẩm và SKU từ `Catalog`; ID màu, tên màu, ID biến thể và kích thước từ `Bien_the`. Nếu một ảnh áp dụng nhiều kích thước cùng màu, liệt kê rõ tất cả ID biến thể được duyệt. Không tự gán cho màu/biến thể khác.
- Gợi ý tên file: `<SKU>__color-<ID>__variant-<ID>__front__01.jpg`. Tên file chỉ hỗ trợ quản lý, không phải bằng chứng. Trường hợp màu chưa xác định phải chờ xác nhận, không đoán từ ảnh.
- Cung cấp bằng chứng liên hệ ảnh với hàng thực tế: ảnh nhãn/SKU hoặc hồ sơ nhà cung cấp có mã đối chiếu, chi tiết chất liệu/phom/hoa văn và màu. PO xác nhận ảnh là đúng mẫu đang bán, quyền sử dụng/phân phối, người duyệt và ngày duyệt.
- Ảnh AI, Unsplash, Pexels và ảnh tải được từ website không tự được coi là ảnh đúng sản phẩm. Thiếu bằng chứng hoặc quyền: giữ placeholder và yêu cầu bổ sung.

## Hồ sơ một ảnh

Mỗi file mới cần một dòng manifest: tên file, SHA-256, ID sản phẩm, SKU, ID màu, danh sách ID biến thể, góc chụp, vai trò ảnh chính/gallery, thứ tự gallery đề xuất, nguồn và tài liệu quyền sử dụng, bằng chứng đúng SKU, người/ngày duyệt, kết quả QA UAT. Bảng `Anh` có các cột PO điền tương ứng; một URL dùng chung nhiều sản phẩm vẫn phải duyệt riêng phạm vi từng SKU.

Giữ nguyên file nguồn và URL hiện hành. Nếu cần xử lý kích thước hoặc metadata riêng tư, chỉ tạo bản sao UAT sau khi thống nhất; lưu checksum cả nguồn và bản dùng. Không đưa ảnh nhãn có thông tin cá nhân vào gói chia sẻ.

## Thử nghiệm trước khi nhập thật

1. PO duyệt file và phạm vi. Kiểm tra manifest ở chế độ dry-run: ID/SKU tồn tại, biến thể thuộc đúng sản phẩm, đúng màu, không trùng/xung đột ảnh chính, file đủ và checksum đúng. Không suy luận ánh xạ theo thứ tự 167 ảnh AI.
2. Dùng database/storage UAT riêng và backend base URL của UAT, không ghi cứng localhost máy PO. Chỉ upload bản sao những ảnh có quyền sử dụng đã được xác nhận.
3. Luồng Admin hiện có nhận tối đa 3 file/lần, JPG/JPEG/PNG/WebP, tối đa 2 MB/file, kích thước tối đa 6000×6000; gallery tối đa 20 ảnh/sản phẩm (`Admin/ProductImageController.php`). Khi triển khai phải kiểm tra lại hợp đồng endpoint đang chạy; không đổi hợp đồng API trong nhiệm vụ này.
4. Ghi lại ID ảnh do môi trường đích trả về; không mặc định ID gallery của database nguồn dùng được ở nơi khác. Đối chiếu lại liên kết ảnh–sản phẩm–biến thể trước khi cập nhật.
5. QA URL qua HTTP, ảnh chính, thứ tự gallery, từng màu/biến thể, placeholder khi thiếu/lỗi, cấu hình API/CORS và hiển thị desktop/mobile. Kiểm tra quyền Admin được phép và Customer/Seller bị từ chối ở endpoint Admin; nếu sử dụng luồng Seller phải kiểm tra sở hữu cửa hàng ở backend. PO đối chiếu hình trực quan với hàng thực tế.
6. Chỉ ghi “Đạt” trong bảng khi đã có bằng chứng UAT. Xác nhận file/quyền/SKU một mình không có nghĩa đã qua kiểm thử hoặc được nhập database chính.

## Phê duyệt nhập thật riêng

Sau khi PO xác nhận ảnh và QA UAT đạt, trình PO/PM danh sách thay đổi cụ thể: ảnh chính/gallery/biến thể nào đổi, URL cũ/mới, phạm vi SKU/màu, bằng chứng và phương án khôi phục. Chờ phê duyệt riêng trước mọi thao tác ghi database/storage thật.

Giữ nguyên 23 URL liên quan lịch sử đơn và các snapshot đơn; không cập nhật lịch sử để theo ảnh mới. Không xóa 167 ảnh AI, 20 file HOLD hoặc bất cứ ảnh cũ nào trong bước nhập. Lưu ánh xạ metadata trước/sau; nếu cần khôi phục chỉ áp dụng bản khôi phục đã được duyệt, giữ cả file cũ và mới. Đề xuất dọn file/bản ghi phải có bảng tham chiếu và phê duyệt xóa riêng.

Nhiệm vụ này chưa xây/chạy importer, chưa upload, chưa thay file hoặc URL hiện hành. Mọi ô duyệt khởi tạo ở trạng thái chờ; không có phê duyệt giả điền sẵn.
