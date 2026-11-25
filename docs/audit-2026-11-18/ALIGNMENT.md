# Đối chiếu Proposal ↔ source ↔ Jira

Ngày 18/11/2026. Locator P là số đoạn XML trong `proposal-extracted.txt`, không phải số trang Word. Đã đọc cả bảng và hình gốc; không đoán số trang. Các Jira trong bảng là issue đang tồn tại; mapping phân tích không đồng nghĩa chúng đã được liên kết chính thức trên Jira.

| Yêu cầu Proposal | Thực tế và mức bằng chứng | Bằng chứng chính | Jira | Đề xuất sửa/AC còn thiếu |
|---|---|---|---|---|
| FR01, P285–287: email/Google login | Email+OTP/login/reset có; Google chưa nối route. S/T/H, chưa A | AuthController; routes/api.php:28–39; ApiAccessBoundaryTest; auth-catalog UAT 06/11 | N5-11/12/13,81/82/85/86/89/94; N5-142 | Tách email đã test và Google hoãn/chưa làm; thêm AC OTP/rate limit/session; không ghi toàn FR01 Done |
| FR02, P288–290: sửa profile, preferences, body | Modal thông tin chỉ đọc; ProfileController cũ không nối route; SizeAdvisor là công thức local | ClientLayout.vue:485; routes/api.php toàn bộ; ProfileController; SizeAdvisorModal | N5-9/10/11/13,83/84/87/88/95/96 | Review Done; quyết định triển khai hay defer từng phần. Marketing preference không phải fashion preference |
| FR03, P291–293: search/filter/sort category/price/size/color | Category/keyword/price/sort có; chọn màu/size trong product detail không đồng nghĩa filter toàn catalog. “Bán chạy” dựa views | TimKiem.vue:396–436,489–533; ClientController::getSanPhams; productCatalog service/tests | N5-15/16,104/106/107/108/110/111 | Ghi bộ filter thực tế; PO chốt size/color list filter; sửa nghĩa bán chạy; UAT keyword/category/price/no-result/network |
| FR04, P294–296: Merchant CRUD sản phẩm/ảnh/giá/kho | Admin làm thay; Seller bị chặn. S/T/H, chưa A | Admin SanPhamController/ChiTietSanPhamController/ProductImageController; ProductCatalogPhaseTwoTest/ProductImagesTest | N5-17,114/116,19/20/21/23,130/131/135/137/139 | Change Log actor Merchant → Admin; giữ việc cũ, không báo Merchant hoàn thành; UAT CRUD/color-image/historical order |
| FR05, P297–299: cart add/remove/update | Local cartStore có variant ID, localStorage, quantity; không có server cart integration hiện tại | cartStore.js; product-detail-demo.test.mjs; checkout-flow.test.mjs | N5-16/17,101/102/112/117/126 | Ghi rõ cart phía browser, không hứa cross-device; 126 UI khác 112 implementation; test reload và cart cũ |
| FR06, P300–302: Customer đặt/theo dõi; Merchant xử lý | Checkout Customer và Admin lifecycle có; Customer chưa có lịch sử đơn đầy đủ | CheckoutService; DemoPayment; OrderLifecycle; routes/api.php; OrderController cũ không nối route | N5-18/24,113/118/124/125/127; N5-142 | Tách đặt hàng/Admin xử lý khỏi Customer tracking; role Merchant đổi Admin sau duyệt; không coi payment polling là history |
| FR07, P303–305: simulated payment | COD chờ thu và mô phỏng MB_BANK owner-only, local/testing, retry an toàn | giaoDichController.php:35; CheckoutIntegrityTest; sofep-auth-catalog-mysql-uat.json | N5-18/113/127; N5-142 | Ghi môi trường được phép; không tiền thật; bổ sung test hosting sandbox trước hứa deployed prototype |
| FR08, P306–308: searches/views/clicks/purchases analytics | Có legacy view counter và sales/dashboard/loyalty; chưa có pipeline searches/click events hoặc insight Merchant | ClientController::getChiTietSanPham; ThongKeController; LoyaltyAudience | N5-129/133/137; chưa có issue mô tả đầy đủ event analytics → N5-140/141 điều phối | Không thay FR08 thành “đã xong” từ dashboard; defer hoặc tạo backlog riêng sau PO chốt |
| FR09, P309–311: complaint/dispute | Private feedback topics/status/attachment/reply có; chưa phải dispute/refund resolution toàn diện | FeedbackController; AdminFeedback; CustomerEngagementTest; sofep-customer-engagement-uat.json | N5-140/143/144; issue chuyên module cần PM chốt | Ghi private feedback là deliverable mới; tách dispute/return/guest reply email chưa bật |
| FR10, P312–314: chat Customer–Merchant | Không có luồng hoạt động | routes FE/BE không có chat; icons/chat không phải chức năng | N5-140/141; không thấy issue chuyên chat trong snapshot | Đề xuất Out of Scope/defer có lý do và PO ký; không đổi thành feedback rồi xóa lịch sử |
| FR11, P315–317: order/promotion/account notifications | Có campaign nội bộ, feedback reply, unread, wallet; chưa có mọi hook order/account | CustomerCampaigns; CustomerInboxController; NotificationBell; CustomerCenter; routes/console.php | N5-132/136 (promotion một phần); N5-140/143 | Bổ sung module engagement; tách automatic order/account notification thành gap; scheduled UI UAT chưa đủ |
| FR12, P318–320: Admin users/Merchant/products/complaints/platform | Admin catalog/customer/order/voucher/stat/feedback có; không Merchant approval/system configuration hoàn chỉnh | AdminMiddleware + routes admin + Admin controllers; ApiAccessBoundaryTest; admin-api-audit JSON | N5-19..23,129/130/131/132/133/135/136/137/139 | Xác định chính xác quyền Admin; Merchant bỏ sau duyệt; không dùng CRUD thay moderation approval workflow |
| NFR01, P326–328: basic ≤2s | Mẫu benchmark nhỏ, có sample >2s; không có workload/p95 production | sofep-api-benchmark.json; sofep-page-benchmark.json | N5-27/30 | Định nghĩa thao tác, dataset, concurrency/network, p95; chưa nghiệm thu |
| NFR02, P329–331: OAuth2/JWT | Sanctum opaque Bearer hiện hành, không JWT; Google dependency chưa tích hợp | AuthController createToken; sanctum config; FE main/authSessions | N5-12/26/30/89/91/94 | Sửa requirement theo cơ chế thực; thêm token/reset/rate-limit/IDOR AC; không hứa OAuth đã chạy |
| NFR03, P332–334: usability/search nhanh | Có responsive/keyboard/error handling tests và ảnh lịch sử; chưa UAT toàn trang | customer-header.test; checkout-flow.test; docs/sofep-uat-screenshots và header-fix-screenshots | N5-5/15/29/142/144 | Rehearsal desktop/tablet/mobile + accessibility form lỗi; ký từng case |
| NFR04, P335–337: scalability | Tối ưu lazy layout/light search/aggregate; còn full catalog all=1 và get() | main.js; TimKiem.vue:501; Admin controllers; ThongKe test | N5-27/30 | Không tuyên bố scale đã đạt; bổ sung load plan, pagination contract sau duyệt |

## Phạm vi, công nghệ, quy trình và deliverables ngoài FR

| Mục Proposal | Vấn đề/bằng chứng | Jira | Đề xuất |
|---|---|---|---|
| 1–3 / Project Information | Bìa Version 1.1, revision chỉ 1.0; end date 24/08 khác 20/11; nhân sự baseline khác phân công Jira hiện tại | 1/2/3/4/140/144 | Giữ tên/ID/học phần/mentor; thêm revision 1.2 chờ duyệt; PM xác nhận phân công, không tự đổi assignee |
| 4–5 Problem/survey | Lý do cá nhân hóa/merchant khác sản phẩm hiện tại; survey nhiều AI chưa có source | 1/2/140 | Giải thích chuyển sang lõi e-commerce Single-Brand, trích nguồn survey có ngày kiểm chứng khi biên tập |
| 6 Objectives/scope | Merchant/chat/behavior analytics vẫn in scope; no real-money đúng | 2/19..26/140 | Chốt scope bằng Change Log; giữ exclusion payment thật; bổ sung refund chưa hỗ trợ |
| 7 Key features | Chưa ghi Product Detail 2.1, private feedback, loyalty AND rules, preview/retry, wallet, authenticated checkout | 140/141/142/143 | Thêm requirement ID mới, AC và mapping, không ghi đè FR cũ vô dấu vết |
| 8 Constraints/budget | 3 vs 4 tháng; budget $59/tháng, cap $4.000 là dự toán; MongoDB/Docker còn trong bảng | 140/144 | Cập nhật effort/cost theo phương án demo được duyệt; không coi dự toán là chi phí đã chi |
| 9 Stakeholders | Merchant là bên bán độc lập; hiện chỉ Customer và Admin vận hành một thương hiệu | 19/22/128/134/138/140 | Ghi actor hiện tại và actor legacy, date/approver của quyết định |
| 10 Stack | Vue/Laravel/MySQL/Sanctum đã đúng; References chưa đúng; Google chưa có integration | 6/140 | Thêm Vite/PHP/lock versions và auth mode; phân biệt dependency với feature |
| 11 Scrum | Có 4 sprint; hình ở P425 vẫn ghi AI/image search/trend; chưa có artifact nghiệm thu sprint được audit | 4/140/141 | Thay hình theo phạm vi sau duyệt; gắn Sprint Goal/DoD/evidence/retrospective; không chứng nhận thực hành Scrum chỉ từ kế hoạch |
| 11 Schedule | Development đến 08/11; testing 09–14/11; fix 15–16; deploy 17; final 18–20 | 24..33/140..144 | Baseline giữ nguyên, thêm actual/forecast/dependency/blocker; ngày 18/11 chưa có deploy evidence |
| 12 Architecture | Hình P653 là context Customer/Merchant/Admin có Search by Image và AI trend, không mô tả stack | 6/140/144 | Vẽ context Single-Brand và container Vue/API/Laravel/MySQL/storage/scheduler riêng; đổi không làm mất hình baseline |
| 13 Risks | Merchant approval không phù hợp; thiếu schema drift, OTP abuse, stale token, dev-only payment, uncommitted source, scheduler restart | 27/30/31/140/144 | Risk register có likelihood/impact/owner/trigger/mitigation/residual acceptance; F01–F06 trong REPORT |
| 14 Deliverables | Merchant/Google/profile/tracking rộng hơn thực tế; deployed prototype chưa chứng minh production | 32/33/140/144 | Bảng accepted/pending/deferred theo evidence; package source + storage/data guide + test report + demo runbook |
| 15 References | Next.js/FastAPI/MongoDB/JWT không khớp cơ chế thực; không được sửa access date giả | 140 | Thay/thêm tài liệu stack thực khi biên tập có kiểm chứng; giữ lịch sử thay đổi bibliography |

## Đề xuất requirement mới cho v1.2, chưa tự mở Jira

| ID dự thảo | Phạm vi và tiêu chí chính | Bằng chứng đã có | Jira điều phối |
|---|---|---|---|
| CR-01 | Checkout bắt buộc Customer, giá/stock/voucher server, idempotency, retry | CheckoutIntegrityTest + auth UAT 06/11 | N5-142 |
| CR-02 | Product Detail 2.1, gallery độc lập, color/size tồn riêng và ảnh theo màu | ProductDetailDemoTest/ProductImagesTest + catalog audit | N5-140/142/144 |
| CR-03 | Private feedback, attachment Admin-only, trạng thái/ghi chú/reply riêng | CustomerEngagementTest + UAT 07/11 | N5-143 |
| CR-04 | Campaign preview có snapshot, AND audience số đơn/chi tiêu, lịch/retry/opt-out | CustomerCampaigns/LoyaltyAudience + tests | N5-143 |
| CR-05 | Inbox/unread, voucher grant riêng, wallet ownership/save-once, checkout recheck | CustomerInbox/VoucherWallet/MaGiamGia + tests/UAT | N5-143/142 |

PM quyết định issue implementation/defect riêng và liên kết vào các task 140..144; không nhồi toàn bộ module vào ticket documentation, không tự tạo backlog trùng.
