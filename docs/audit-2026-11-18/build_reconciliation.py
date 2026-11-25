"""Tạo bảng review từ snapshot Jira đọc trực tiếp; không ghi Jira hoặc database."""
import csv
import hashlib
import json
from pathlib import Path
from collections import Counter

BASE = Path(__file__).resolve().parent
snapshot = json.loads((BASE / 'jira-snapshot.json').read_text(encoding='utf-8'))
hierarchy = {i['key']: i for i in json.loads((BASE / 'jira-hierarchy.json').read_text(encoding='utf-8'))}

def review(n):
    pending = 'Implemented/Pending UAT'
    if n in {38,39,40,41,42,45}:
        return ('To Do', 'Chưa đủ nội dung để đặt tên; PM xác định outcome',
                'jira-snapshot.json: description trống; jira-hierarchy.json',
                'Epic/Story tên chung, thiếu yêu cầu và AC; chưa chứng minh là duplicate.',
                'PM làm rõ title/description/AC/parent, giữ lịch sử; assignee do nhóm quyết định.')
    if n in {128,134,138}:
        return ('Out of Scope', 'Legacy Merchant — đề xuất hoãn theo Single-Brand',
                'themmoik27be/routes/api.php; AuthController; ApiAccessBoundaryTest',
                'Không có route Seller hoạt động; nhóm kiểm toán này chờ PO xác nhận scope.',
                'Đề xuất disposition Out of Scope/deferred, link N5-140; không xóa hoặc đóng Done.')
    if n in {83,84,87,88,95,96}:
        return ('To Do', 'Customer — '+('Fashion Preferences/Body info' if n in {84,88,96} else 'Profile')+' — '+('UI design' if n in {83,84} else 'Frontend' if n in {87,88} else 'API integration'),
                'Fe/src/layout/wrapper/ClientLayout.vue:485; themmoik27be/routes/api.php; ProfileController.php (chưa nối route)',
                'Modal chỉ đọc; chưa có cập nhật hồ sơ/sở thích/body theo FR02; không coi marketing preference là fashion preference.',
                'Review trạng thái Done với PO; tạo AC và làm tiếp hoặc defer có Change Log; chưa tự chuyển.')
    if n in {7,9,14,98,99,100,101,102,124,125}:
        return ('In Progress', 'Database — '+{7:'baseline và ERD',9:'Customer/auth/profile',14:'catalog/cart/order schema',98:'Product',99:'Category',100:'Inventory/Variant',101:'Cart',102:'Cart Item',124:'Order',125:'Order Item'}[n],
                'themmoik27be/database/migrations; tests/Support/IsolatedApiTestCase.php; tests/Support/MySqlUat.php; REPORT F03',
                'Có migrations/entities nhưng thiếu baseline role/cua_hangs; schema fixture tự tạo không chứng minh clean install.',
                'Giữ lịch sử thiết kế; bổ sung ERD/schema diff và kiểm chứng DB mới sau duyệt riêng; không chạy migrate DB thật.')
    if n in {1,2,3,4,5,6,8}:
        return ('In Progress', {1:'Requirements — baseline và scope change',2:'Scope — Single-Brand và deferred features',3:'Proposal — baseline v1.1 và bản sửa v1.2',4:'Scrum — Sprint Goal/DoD/evidence',5:'UI/UX — Customer/Admin và legacy Merchant',6:'Architecture — Vue/Laravel/MySQL/Sanctum',8:'API — inventory và contract hiện hành'}[n],
                'Proposal P240–337,412–650; hình P425/P653; ALIGNMENT.md; routes/api.php; jira-hierarchy.json',
                'Có baseline và triển khai nhưng tài liệu/scope/acceptance chưa nhất quán; chưa có sign-off artifact hiện hành.',
                'Bổ sung actual/revision/AC/evidence; PO xem trước; không tự đảo Done của baseline lịch sử.')
    if n in {10,11,12,13}:
        return ('In Progress', {10:'UI/UX — Auth/Profile/Preferences',11:'Frontend — Auth/Profile/Preferences',12:'Backend — Auth/User/Role',13:'API integration — Auth/Profile/Preferences'}[n],
                'AuthController; routes/api.php; ClientLayout; ApiAccessBoundaryTest; verification.json; REPORT F01/F02',
                'Auth có source/test nhưng các phần hồ sơ/sở thích của parent còn thiếu hoặc bảo mật phiên/OTP còn gap.',
                'Tách scope đã làm/chưa làm, link subtask; review Done của parent khi AC chưa đủ; không đếm lặp.')
    if n in {19,20,21,22,23}:
        return ('In Progress', {19:'UI/UX — Admin + disposition Merchant legacy',20:'Frontend — Admin catalog/inventory/promotions',21:'Backend — Admin catalog/inventory/promotions',22:'Scope review — Merchant Approval và Product Moderation',23:'API integration — Admin + Merchant legacy'}[n],
                'routes/api.php; Admin controllers; Fe/src/components/Admin; ApiAccessBoundaryTest; ALIGNMENT FR04/12',
                'Ticket trộn Admin đã triển khai với Merchant/approval chưa phù hợp. CRUD không chứng minh moderation workflow.',
                'Tách/đổi tên sau PO duyệt, giữ liên kết legacy; Admin pending UAT, Merchant đề xuất Out of Scope; không đóng parent toàn bộ.')
    if n == 32:
        return ('To Do', 'Deployment — môi trường được duyệt và smoke end-to-end',
                'Fe/vite.config.js; themmoik27be/config/cors.php; REPORT mục 3/10',
                'Có local runbook/build, chưa có production URL/TLS/pipeline/smoke; mô phỏng chỉ local/testing.',
                'Giữ To Do; PO chọn demo local hay hosting; mọi env/deploy phải duyệt riêng.')
    if n in {24,25,26,27,29,30,31,33,140,141,142,143,144}:
        names={24:'Integration — Single-Brand end-to-end',25:'Testing — API/MySQL data flow',26:'Authorization — Customer/Admin + reject legacy Seller',27:'Performance — workload và database optimization',29:'QA — functional/integration + browser UAT',30:'QA — security/performance residual gaps',31:'Defects — triage và regression F01–F06',33:'Documentation — consistent final handoff',140:'Proposal v1.2 — scope và traceability',141:'Reconciliation — 88 baseline + 5 tracking = 93 visible issues',142:'UAT — Guest/Auth/checkout/voucher/Admin',143:'UAT — private feedback/campaign/wallet/privacy',144:'Handoff — architecture/runbook/demo/rehearsal'}
        evidence={24:'CheckoutIntegrityTest + auth-catalog-mysql-uat JSON',25:'sofep-mysql-uat.json; sofep-auth-catalog-mysql-uat.json',26:'ApiAccessBoundaryTest; CustomerEngagementTest; verification.json',27:'sofep-api-benchmark.json; sofep-page-benchmark.json; REPORT F05',29:'verification.json; docs/sofep-uat-screenshots; N5-142 checklist',30:'REPORT F01/F02/F05; verification.json; benchmark JSON',31:'REPORT F01–F06; chưa có patch trong lượt audit',33:'docs/SOFEP_*_HANDOFF.md; REPORT; ALIGNMENT',140:'PROPOSAL_V1_2_CHANGE_PLAN.md; ALIGNMENT; Word gốc giữ nguyên',141:'jira-snapshot.json; jira-hierarchy.json; JIRA_RECONCILIATION.csv',142:'sofep-auth-catalog-mysql-uat.json; sofep-full-catalog-uat.json; REPORT UAT checklist',143:'sofep-customer-engagement-uat.json; sofep-engagement-activation-smoke.json; CustomerEngagementTest',144:'SOFEP_ENGAGEMENT_ACTIVATION.md; REPORT; ALIGNMENT'}
        return ('In Progress',names[n],evidence[n],
                'Đã có một phần code/test/tài liệu hoặc deliverable audit; còn AC/UAT/PO review, không tự coi hoàn thành toàn ticket.',
                'Gắn evidence và case còn thiếu/Blocked; cập nhật In Progress sau PM review, giữ owner/priority; chỉ Done khi đúng AC được chấp thuận.')
    if n == 117:
        return ('In Progress','Backend — Cart, xác nhận có cần đồng bộ server',
                'Fe/src/stores/cartStore.js; CartController cũ không được nối routes/api.php',
                'Giỏ đang hoạt động ở browser; không đủ chứng minh backend Cart hoàn thành theo mô tả parent.',
                'PO chọn cart browser là scope hay cần server sync; review Done, không tự thêm API.')
    if n == 118:
        return ('In Progress','Backend — Order lifecycle Admin và gap Customer tracking',
                'OrderLifecycle.php; Admin/DonHangController.php; OrderController cũ chưa nối route; routes/api.php',
                'Admin lifecycle/hủy bảo toàn lịch sử có test; Customer history và refund chưa có.',
                'Tách AC Admin/Customer/refund; link N5-142; giữ limitation, không nói toàn FR06 Done.')
    if n in {15,16,17,18}:
        return ('In Progress',{15:'UI/UX — Catalog/Cart/Checkout',16:'Frontend — Catalog/Cart/Checkout',17:'Backend — Catalog/Inventory/Cart/Order',18:'API integration — Catalog/Checkout/Order'}[n],
                'TimKiem/CheckoutModal/cartStore; ClientController/CheckoutService; verification.json; REPORT F04/F05',
                'Lõi có source/test; parent còn filter/bán chạy/cart server/Customer tracking và UAT đầy đủ tùy phạm vi.',
                'Không đóng parent theo một subtask đạt; định nghĩa AC hiện hành và nối test với subtask.')
    title = next(i['title'] for i in snapshot['issues'] if i['key'] == f'N5-{n}').strip()
    h=hierarchy[f'N5-{n}']; parent=h.get('parent')
    layer={'N5-10':'UI design','N5-11':'Frontend','N5-12':'Backend','N5-13':'API integration','N5-15':'UI design','N5-16':'Frontend','N5-17':'Backend','N5-19':'UI design Admin','N5-20':'Frontend Admin','N5-21':'Backend Admin','N5-23':'API integration Admin'}.get(parent,'Module')
    if n in {81,82,85,86,89,90,91,94}:
        ev='AuthController; AdminMiddleware/RequireCustomer; ApiAccessBoundaryTest; admin-login/catalog-session tests; verification.json'
        reason='Email/auth/role hoặc UI tương ứng có code và regression. Google/Profile ngoài phạm vi con này chưa chứng minh; thiếu artifact nghiệm thu theo tầng.'
    elif n in {129,130,131,132,133,135,136,137,139}:
        ev='Fe/src/components/Admin; Admin controllers; ProductCatalogPhaseTwoTest; CheckoutIntegrityTest; admin-api-audit JSON; verification.json'
        reason='Phần Admin có triển khai/test; Dashboard cần đặt rõ đối tượng; UAT browser CRUD/PO sign-off chưa đủ. Promotions mới có thêm campaign nhưng không mặc định mở rộng ticket cũ.'
    else:
        ev='ClientController/ProductDetailController/CheckoutService; Fe/src/components/Client + cartStore; Fe/tests; ProductCatalogPhaseTwoTest/CheckoutIntegrityTest; verification.json'
        reason='Phần catalog/cart/checkout tương ứng có source hoạt động và test; UI design và implementation là deliverable riêng, chưa có acceptance evidence đầy đủ cho từng issue.'
    return (pending,f'{layer} — {title}',ev,reason,
            'Gắn bằng chứng đúng tầng và AC; đề xuất Pending UAT/review Done hiện có; không tự chuyển. Giữ assignee/priority, link parent thay vì đóng Duplicate theo tên.')

rows=[]
for i in snapshot['issues']:
    n=int(i['key'].split('-')[1]); group,new_title,evidence,reason,action=review(n)
    rows.append({**i,**{k:v for k,v in hierarchy[i['key']].items() if k!='key'},
                 'audit_group':group,'proposed_title':new_title,'evidence':evidence,
                 'reason':reason,'recommended_action':action,
                 'proposed_priority':i['priority'],'assignee_change':'Không đổi',
                 'acceptance':'Chưa xác nhận PO sign-off; chỉ đề xuất Done khi đủ AC/evidence và được chấp thuận.'})
fields=list(rows[0])
with (BASE/'JIRA_RECONCILIATION.csv').open('w',encoding='utf-8-sig',newline='') as f:
    writer=csv.DictWriter(f,fields);writer.writeheader();writer.writerows(rows)
missing=[n for n in range(1,145) if f'N5-{n}' not in {i['key'] for i in rows}]
validation={'date':'2026-11-18','visible_issues':len(rows),'unique_keys':len({i['key'] for i in rows}),
            'current_status':dict(Counter(i['status'] for i in rows)),
            'audit_group':dict(Counter(i['audit_group'] for i in rows)),
            'verified_done_with_PO_signoff':0,'missing_numbers_not_proof_deleted':missing,
            'empty_descriptions':sum(not i['description'] for i in rows),
            'unassigned':[i['key'] for i in rows if not i['assignee']],
            'jira_mutations':0,'all_rows_have_evidence_and_action':all(i['evidence'] and i['recommended_action'] for i in rows)}
(BASE/'reconciliation-validation.json').write_text(json.dumps(validation,ensure_ascii=False,indent=2),encoding='utf-8')
print(json.dumps(validation,ensure_ascii=False))
