# Danh sách PO duyệt ảnh thật — 20/11/2026

## Kết quả

Snapshot catalog lúc `2026-11-20T09:20:57+00:00`: **168 sản phẩm, 500 biến thể, 514 bản ghi gallery**. Chưa có hồ sơ chứng minh ảnh thật đúng SKU cho **cả 168 sản phẩm**. Giữ placeholder cho 168 ảnh chính theo bộ xử lý frontend hiện tại; đây là đối chiếu logic, không phải kiểm thử trình duyệt mới.

Bảng duyệt: [SOFEP_PO_DUYET_ANH.xlsx](../../outputs/sofep-photo-approval-2026-11-20/SOFEP_PO_DUYET_ANH.xlsx). Các sheet `Catalog`, `Bien_the`, `Anh` có bộ lọc theo đợt. `Anh` giữ URL gốc, nguồn, checksum upload, chứng cứ, tham chiếu đơn và ô PO điền. `Bien_the` liệt kê đầy đủ ID, màu, kích thước và liên kết ảnh; database không có trường SKU biến thể riêng. Không tự tạo SKU hoặc suy luận ảnh theo màu. Chỉ 9/500 biến thể có liên kết ảnh riêng, đều thuộc ID 172.

## Các đợt duyệt

| Đợt | Danh mục | Sản phẩm | Biến thể | Có bằng chứng ảnh thật đúng SKU |
|---|---|---:|---:|---:|
| [D01](batches/D01.md) | Áo Nam | 23 | 73 | 0 |
| [D02](batches/D02.md) | Quần Nam | 21 | 62 | 0 |
| [D03](batches/D03.md) | Váy & Đầm Nữ | 21 | 62 | 0 |
| [D04](batches/D04.md) | Áo Nữ | 21 | 62 | 0 |
| [D05](batches/D05.md) | Áo Khoác & Blazer | 21 | 62 | 0 |
| [D06](batches/D06.md) | Giày & Sneaker | 21 | 62 | 0 |
| [D07](batches/D07.md) | Túi Xách & Ví | 21 | 62 | 0 |
| [D08](batches/D08.md) | Phụ Kiện Thời Trang | 19 | 55 | 0 |

Mỗi sản phẩm thuộc đúng một đợt. Các bảng đợt là danh sách toàn bộ sản phẩm chưa có nguồn ảnh thật đáng tin cậy; không chỉ các sản phẩm thiếu file.

## Phân loại và giới hạn bằng chứng

| Nguồn | URL duy nhất | Kết luận |
|---|---:|---|
| AI tạo cho demo | 167 | Minh họa, chưa chứng minh đúng SKU |
| Pexels theo hồ sơ lịch sử | 19 | Ảnh stock, chưa chứng minh đúng SKU |
| Unsplash | 87 | Ảnh stock, chưa chứng minh đúng SKU |
| Website ngoài, chưa rõ quyền | 1 | Chưa xác minh ảnh thật/SKU, ID 172 |
| Upload, chưa rõ nguồn | 1 | Chưa xác minh ảnh thật/SKU, ID 172 |
| Tổng | 275 | Không nguồn nào mặc nhiên được duyệt |

355 dòng ảnh là cặp **sản phẩm–URL** sau khi gộp URL trùng trong cùng sản phẩm, vẫn giữ toàn bộ ID gallery và ID biến thể liên quan. Vì nhiều sản phẩm dùng chung URL và nhiều bản ghi gallery trùng URL, số này khác 514 bản ghi và 275 URL duy nhất.

187 file upload được tham chiếu hiện tồn tại và đã đọc checksum; không phát hiện thiếu file local trong tập đó. Có file không chứng minh đúng sản phẩm hoặc có quyền sử dụng. Không gọi HTTP mới hoặc tải ảnh Internet trong nhiệm vụ này. Kết quả HTTP cũ được ghi nhãn lịch sử trong bảng; **chưa xác nhận tình trạng tải HTTP hiện tại**, không kết luận mọi URL đang hoạt động. Không có sản phẩm trống trường ảnh chính; tất cả vẫn thiếu bằng chứng phù hợp.

ID 172 cần ưu tiên kiểm tra ảnh website `thoitrangbigsize.vn` và upload `dd7b32f3-7ee8-4f6e-b7f0-e256ee801dc9.jpg`: chưa đủ bằng chứng nguồn/quyền và đối chiếu SKU/màu, tiếp tục HOLD. Không dùng việc file tải được để bỏ placeholder.

## Cách dùng bảng

1. Lọc một đợt, đối chiếu sản phẩm và toàn bộ màu/biến thể.
2. PO điền ô vàng ở `Anh` và thông tin phạm vi ở `Bien_the`, theo [quy trình tiếp nhận và nhập ảnh](PHOTO_INTAKE_AND_IMPORT_PLAN.md).
3. Chỉ xác nhận SKU chưa đủ: còn cần file mới, phạm vi màu/biến thể, bằng chứng, quyền sử dụng, người/ngày duyệt và QA UAT. Cột V chỉ báo “Chờ phê duyệt nhập” khi đầy đủ; không cấp quyền ghi database.
4. Không có bằng chứng thì giữ “Chờ ảnh thật”/placeholder. Việc điền bảng không thay URL hoặc trạng thái hiển thị trong hệ thống.

## Truy vết và an toàn

Đối chiếu [image-audit](../image-audit-2026-11-20/REPORT.md), [image-clone](../image-clone-2026-11-20/REPORT.md), [image-handoff](../image-handoff-2026-11-20/REPORT.md), [API/image config](../api-image-config-2026-11-20/REPORT.md), manifest AI và hồ sơ 20 file HOLD. Nguồn cụ thể được giữ trong bảng ảnh.

`read-catalog.php` chỉ SELECT trong transaction READ ONLY, rollback; chỉ xuất catalog và số tham chiếu URL ảnh đơn, không xuất khách hàng hoặc chi tiết đơn. `catalog-snapshot.json` và `review-data.json` là dữ liệu đối chiếu; `prepare-review.mjs` tạo các bảng đợt. Bảo toàn 23 URL ảnh lịch sử đơn với 41 tham chiếu; không sửa URL, bản ghi hoặc file ảnh.

Đã kiểm tra số dòng, đối chiếu tổng, công thức không báo lỗi, thử điều kiện duyệt rồi trả toàn bộ ô về chờ duyệt; xem bản render các sheet. Không sửa source ứng dụng nên không chạy lại FE/BE tests/build. Không thực hiện import/upload hoặc kiểm thử mạng mới; không thay database/storage chính, không tải ảnh mới, không commit/push/deploy, không chỉnh Jira/Proposal hoặc N.Cảnh Barber.
