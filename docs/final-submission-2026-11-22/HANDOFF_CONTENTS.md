# Thành phần bản nộp dự kiến — chưa đóng gói chính thức

## Giữ

- `Fe/src`, `Fe/public`, assets đang dùng, cấu hình Vite, package.json/lockfile, bộ tests FE cần thiết. Không thay thiết kế, không tích hợp Figma.
- Laravel app/config/routes/resources/public, migrations/factories, Composer manifest/lockfile, PHPUnit, Feature/Unit tests và support cô lập cần chạy suite. Giữ phần góp ý/phản hồi/thông báo, API config, CORS, ảnh và phân quyền đã kiểm thử.
- `demo/catalog.json`: đúng 7 bảng catalog, 168 sản phẩm, 167 gallery, 501 biến thể gồm fixture ảnh 510 → 436. Không có dữ liệu tài khoản/khách/đơn/token thật.
- `demo/media`: đúng 167 ảnh đã kiểm checksum; placeholder ID 172; manifest/checksum/provenance nguồn nội bộ. Không thêm 20 file HOLD/ảnh chưa duyệt quyền vào bộ này.
- `handoff/setup.php`, hướng dẫn cài, README và SOURCE_MANIFEST. Giữ thông tin license của assets/dependency.
- Các sửa High đã được duyệt và kiểm thử **sau này**, cập nhật checksum/bản ZIP mới; v2 hiện chưa có bản vá đó.

## Không đưa vào archive nộp, nhưng không xóa trên máy PO

- `.env*`, APP_KEY, credential MySQL, file `demo-access.json`, token/session/cache/log, SQL dump chính, khách/đơn thật, ảnh upload ngoài bộ demo.
- `.git`, vendor/node_modules/dist hiện có, `.vscode`, output UAT/database/temp, screenshots/log/báo cáo audit cũ, các ZIP cũ và file installer thử nghiệm cục bộ.
- Seeder dữ liệu/tài khoản cũ và các browser UAT launcher cũ trong tests/Support không cần cho suite. Không tự loại IsolatedApiTestCase hoặc các test phụ thuộc chỉ vì nằm trong Support.
- Proposal, Jira exports, Figma mới và tài liệu riêng không cần cho việc chạy/nộp.
- Không copy toàn bộ working tree bằng thao tác chọn tất cả; dùng allowlist/manifest và xét file mới trước khi phát hành.

[WORKTREE.md](WORKTREE.md) liệt kê từng đường dẫn trạng thái Git hiện tại, có trong ZIP hay không và đề xuất giữ/không phân phối/rà riêng. `working-tree.json` là dữ liệu tương ứng. Đây là kiểm kê file chưa commit, không phải danh sách file đã xóa. `source-comparison.json` đối chiếu đủ 999 file source với snapshot nguồn v2; ba file phân phối đã ẩn thông tin liên hệ/thanh toán được đánh dấu riêng.

## Tài khoản và hướng dẫn giảng viên

Hướng dẫn đầy đủ hiện có tại [INSTALL.md](../submission-handoff-2026-11-21/INSTALL.md), cũng nằm trong ZIP ở `handoff/INSTALL.md`:

1. Cài PHP/extension, Composer, Node/npm và MySQL theo yêu cầu lockfile. Có Internet/cache hợp lệ để cài dependency.
2. Giải nén vào thư mục mới; `composer install --prefer-dist --no-interaction --no-scripts`, `npm ci --ignore-scripts --no-audit --fund=false`.
3. Khai báo credential MySQL local trong biến tiến trình; `php handoff/setup.php 8000 5173` (hoặc port tự chọn). Installer tạo database demo riêng, env/key, catalog và ảnh; không import `doan_nhom_5` hoặc chạy seed cũ.
4. Tài khoản Admin là `admin@sofep.example.test`, Customer là `customer@sofep.example.test`. **Mật khẩu ngẫu nhiên tạo tại máy cài**, xem `themmoik27be/storage/app/private/demo-access.json`; không gửi file này vào Git/archive. Không dùng tài khoản Admin của PO để hướng dẫn nộp.
5. Chạy Laravel/Vite theo port chọn, đăng nhập bằng credential vừa sinh. Kiểm tìm kiếm, biến thể/giỏ/COD/ảnh/Admin. Thanh toán mô phỏng không chuyển tiền thật.
6. Navicat mở đúng database trong `install-result.json`, đối chiếu `san_phams.hinh_anh` và `anh_san_phams.duong_dan_anh` với URL Laravel/File Storage của bản cài. URL catalog tương đối, không cần upload trên máy PO.

Trước bản chính thức cần ghi rõ chức năng đánh giá cũ nếu đã gỡ, phạm vi OTP dùng tài khoản demo có sẵn, trạng thái High đã sửa, và cập nhật checksum/version. Chưa có cam kết offline toàn website hay cài trên máy vật lý khác.
