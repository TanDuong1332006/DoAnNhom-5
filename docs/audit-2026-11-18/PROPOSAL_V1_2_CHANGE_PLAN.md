# SOFEP Proposal v1.2 — Đề xuất tái lập phạm vi để PO review

Trạng thái: **đề xuất**, ngày 18/11/2026. Không thay thế Word v1.1, không giả định phê duyệt. N5-140 chỉ có thể Done sau khi bản Word hoàn chỉnh có traceability và được PO xem/duyệt theo acceptance criteria.

## Revision History dự kiến

| Version | Ngày | Nội dung | Người soạn/phê duyệt |
|---|---|---|---|
| 1.0 | 24/08/2026 | Initial Release theo bản gốc | Duong theo Revision History gốc |
| 1.1 | Chưa xác minh ngày phát hành; bìa 24/08, filesystem lưu 03/10 | Bản Word baseline được audit; thiếu entry lịch sử tương ứng | PM xác minh; không tự điền người duyệt |
| 1.2 Draft | 18/11/2026 | Đối chiếu source/Jira, đề xuất Single-Brand, sửa nội dung lệch và ghi gap | Báo cáo audit; tác giả tài liệu/PO phê duyệt do nhóm xác nhận |

Giữ bản baseline bất biến và hash. Không đổi ngày bìa thành ngày lưu file; không bổ sung chữ ký/approval giả.

## Change Log dự kiến

| Change | Baseline cũ | v1.2 đề xuất | Lý do/ảnh hưởng | Chấp thuận |
|---|---|---|---|---|
| CH-01 | Customer/Merchant/Admin | Customer/Admin vận hành Single-Brand; Merchant legacy giữ lịch sử | Routes Seller đã loại; ảnh hưởng FR04/06/08/10/12, use case, stakeholder, sprint và deliverables | Chờ PO |
| CH-02 | Chat Merchant và approval | Đề xuất defer/out of scope, tách moderation khỏi CRUD | Không có luồng hoạt động; private feedback là chức năng khác | Chờ PO |
| CH-03 | Hình AI/image search/trend và FR08 toàn events | Ghi chưa triển khai; nếu giữ vision, đặt Roadmap, không ở danh sách delivered | Không có endpoint/model inference/dataset/evaluation | Chờ PO |
| CH-04 | OAuth/JWT NFR, Google authentication | Sanctum Bearer hiện tại; Google pending/deferred | Mục stack phần lớn đúng, references/security còn lệch; không đổi công nghệ hệ thống | Chờ PO |
| CH-05 | Cart/checkout/payment chung | Nêu cart browser, Customer checkout, server price/stock/voucher, idempotency, COD/demo | Có source/test/UAT; không đồng nghĩa hoàn tiền hay Customer order history đã có | Chờ PO |
| CH-06 | Complaint và notifications tổng quát | Thêm private feedback, campaign/loyalty/inbox/wallet; tách gap dispute/order-account automation | Không thay đổi nghĩa FR09/11 âm thầm | Chờ PO |
| CH-07 | Profile/body/fashion preferences delivered | Ghi gap, chọn làm tiếp hoặc deferred có AC | Source chưa nối route cập nhật; UI thông tin chỉ đọc | Chờ PO |
| CH-08 | Deploy 17/11, project info end 24/08 | Giữ lịch baseline, thêm actual/forecast và mốc 20/11 đề xuất | Chưa chứng minh production; không giả lập tiến độ đã hoàn tất | Chờ PO |
| CH-09 | NFR ≤2s và scale chung | Định nghĩa workload/thao tác/p95/error-rate, environment/dataset và evidence | Số đo local nhỏ chưa nghiệm thu | Chờ PO |
| CH-10 | Ngân sách MongoDB/Docker, tài liệu cũ | Dự toán theo stack và phương án demo được chọn, bibliography khớp | Chưa có bằng chứng chi tiêu thực; không nâng dependency hay đổi DB | Chờ PO |

## Nội dung cần biên tập từng mục

1. Giữ nguyên tên học phần, thành viên, mã sinh viên và mentor; xác minh start/end date, version và URL. Contact trong tài liệu nội bộ không cần nhân bản vào bản public nếu nhóm không yêu cầu.
2. Problem Statement: nêu nhu cầu mua sắm và quản lý catalog/đơn/voucher của một thương hiệu; giải thích hướng marketplace/AI là baseline lịch sử thay vì xóa.
3. Survey: giữ so sánh có nguồn, phân biệt tính năng đối thủ và tính năng SOFEP; khi biên tập cần kiểm chứng nguồn và ngày truy cập, không suy ra SOFEP có AI.
4. Objectives: deliverable lõi e-commerce có kiểm thử; không hứa production hay toàn FR khi chưa ký. Scope có cột current/deferred/legacy và approver/date.
5. Requirements: giữ FR01–FR12 ổn định để trace, thêm disposition và version; requirement mới CR-01..05 chỉ là mã dự thảo, PO quyết định định danh cuối. Mỗi requirement có role, precondition, success/failure, API/source, test, Jira và acceptance.
6. NFR: bảo mật backend/ownership/OTP/session; hiệu năng với dataset/concurrency/p95; usability desktop/mobile/keyboard; scalability theo phép đo; không đổi sang JWT chỉ để khớp câu chữ cũ.
7. Constraints/assumptions: PHP ^8.3, MySQL local có schema lịch sử, runtime mail/scheduler/storage riêng; giới hạn thời gian/budget xác nhận lại. Main local khác UAT/production.
8. Stakeholders: Customer và Admin, PO/QA/nhóm triển khai; Merchant là actor legacy chờ xác nhận defer. Không tự thay thành viên phụ trách trên Jira theo vai trò trong Proposal.
9. Stack: Vue 3/Vite/Axios/Router; Laravel/PHP/Sanctum; MySQL; Bootstrap/Chart.js; Git/Jira/Navicat/Laragon theo môi trường thực. Version khóa hiện tại ở REPORT, không tự nâng cấp.
10. Scrum: nêu Sprint Goal, refinement, planning, review, retrospective và DoD có bằng chứng, không chỉ bảng ngày. Actual progress lấy từ deliverable/test; parent/subtask không đếm lặp. Thay hình đang quảng bá AI sau duyệt.
11. Schedule: giữ baseline 21/08–20/11; thêm actual, remaining, owner do PM chốt và dependency. Full UAT, schema reproducibility, security và rehearsal là đường găng còn lại.
12. Architecture: hai sơ đồ riêng: context Customer/Admin và container Browser Vue → REST Laravel → MySQL + public product storage/private feedback storage + scheduler. Sanctum token và quyền sở hữu thể hiện tại backend; deploy view chỉ khi có môi trường thực được xác minh.
13. Risks: F01–F06, thiếu history/profile/Google, refund chưa hỗ trợ, schema drift, uncommitted changes, scheduler restart, production payment gate, ảnh minh họa/external URL. Mỗi risk có owner, mức độ, mitigation, trigger và PO nhận residual risk.
14. Deliverables: versioned source snapshot, API inventory, schema baseline/mapping, module hiện tại và giới hạn, test/defect report, UAT sign-off, runbook/demo, deployment evidence nếu có. Không chứng nhận deployed prototype production chỉ vì Vite build đạt.
15. References: thay phần stack cũ có kiểm chứng bằng docs Vue/Vite/Laravel/Sanctum/MySQL và nguồn survey thực sự sử dụng; giữ version history của thay đổi nguồn.

## Definition of Done cho phiên bản Proposal mới

- Word v1.2 được tạo riêng sau biên tập; bản cũ giữ nguyên, metadata/revision/date nhất quán.
- Mỗi FR/NFR có disposition, source/test/Jira mapping, gap và approver cho phần deferred.
- Context/architecture/Scrum hình ảnh khớp nội dung; không còn hứa AI/Google/Merchant đã triển khai khi thiếu evidence.
- Jira 93 issue được reconciled theo parent/type, tránh đếm trùng; các số không trả về không bị tự tạo ticket.
- Mọi claim UAT ghi tester/ngày/môi trường/snapshot và Passed/Failed/Blocked; SQLite không thay MySQL; UAT kỹ thuật không thay PO sign-off.
- Render và kiểm tra toàn bộ trang Word trước bàn giao bản in; report audit Markdown hiện tại không thay render QA cho file Word tương lai.
- PO review và phê duyệt; không thay bản baseline trước khi có quyết định.
