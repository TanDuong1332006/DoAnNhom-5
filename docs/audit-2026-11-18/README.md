# Bàn giao kiểm toán SOFEP — 18/11/2026

Đọc theo thứ tự:

1. [Báo cáo chính](REPORT.md): kết luận, kiến trúc, chức năng, phát hiện, Jira, kế hoạch và quyết định PO.
2. [Bảng Proposal ↔ source ↔ Jira](ALIGNMENT.md): FR01–12, NFR01–04 và đủ các mục Proposal.
3. [Đề xuất Proposal v1.2](PROPOSAL_V1_2_CHANGE_PLAN.md): Revision History, Change Log, hướng dẫn biên tập và điều kiện nghiệm thu; không thay Word gốc.
4. [Đối chiếu 93 Jira issue](JIRA_RECONCILIATION.csv): UTF-8 BOM để mở Excel; một dòng/issue, giữ dữ liệu người phụ trách và priority; không phải file import tự động chuyển trạng thái.
5. [Kết quả kiểm tra mới](verification.json), [PHP lint](php-lint.json), [validation đối chiếu](reconciliation-validation.json), [trích đoạn source](SOURCE_EVIDENCE.md).

## File mới và mục đích

| File/thư mục | Nội dung |
|---|---|
| REPORT.md, ALIGNMENT.md | Báo cáo và traceability |
| PROPOSAL_V1_2_CHANGE_PLAN.md | Đề xuất thay đổi, chưa phải Word v1.2 đã được duyệt |
| JIRA_RECONCILIATION.csv | 93 dòng review từng ticket, đề xuất tên/nhóm/evidence/hành động |
| jira-snapshot.json | Snapshot title/description/assignee/priority/status từ JQL trực tiếp; không lưu token |
| jira-hierarchy.json | Issue type và parent để phân biệt trùng tên với trùng công việc |
| build_reconciliation.py | Script tái tạo CSV/validation từ snapshot, không gọi Jira/DB |
| reconciliation-validation.json | Đếm issue/nhóm/số không được trả về/description trống |
| verification.json, php-lint.json | Lệnh và kết quả kiểm tra ngày 18/11 |
| source-manifest.json | SHA-256 source snapshot đã kiểm thử |
| SOURCE_EVIDENCE.md | Trích đoạn có số dòng cho phát hiện chính |
| git-status-baseline.txt | Snapshot trong lúc audit, đã có artifact mới; không phải baseline sạch ban đầu |
| proposal-extracted.txt | Toàn bộ body/bảng Word theo số đoạn XML; có thông tin nhóm từ tài liệu gốc, dùng nội bộ |
| image1.png, image2.png, image3.png | Ảnh nhúng tách từ Word, không chỉnh sửa; đã xem hình Scrum và context |
| build-check/ | Build kiểm chứng tạo riêng; giữ lại do bộ kiểm tra phê duyệt tự động từ chối lệnh xóa đệ quy; không cần commit cùng báo cáo |
| README.md | Chỉ mục bàn giao này |

Không sửa/xóa source ứng dụng, không ghi database thật, không đổi môi trường/dependency/Git history/Jira. Hash 206 file source kiểm toán được đối chiếu lại và không đổi; xem verification.json. Bằng chứng UAT đầu tháng là lịch sử, không được diễn đạt thành đã chạy lại ngày 18/11. Browser/MySQL UAT mới và PO sign-off còn thiếu.
