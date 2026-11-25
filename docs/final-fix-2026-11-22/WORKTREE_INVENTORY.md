# Kiểm kê working tree

Không xóa hoặc hoàn tác file nào. Source được lấy theo allowlist, bao gồm source chưa tracked. Không đóng Proposal/Jira, báo cáo chứa đường máy PO, runtime, vendor/node_modules, .env, log, ảnh nguồn ngoài demo và seeder cũ.

|Trạng thái|Đường dẫn|Phân loại|
|---|---|---|
| M|Fe/src/App.vue|Source/test cần giữ|
| M|Fe/src/components/Admin/ChiTietSanPham/index.vue|Source/test cần giữ|
| D|Fe/src/components/Admin/DanhGia/index.vue|Ngoài allowlist; giữ nguyên trên máy PO|
| M|Fe/src/components/Admin/DanhMuc/index.vue|Source/test cần giữ|
| M|Fe/src/components/Admin/Dashboard/index.vue|Source/test cần giữ|
| M|Fe/src/components/Admin/DonHang/index.vue|Source/test cần giữ|
| M|Fe/src/components/Admin/KhachHang/index.vue|Source/test cần giữ|
| M|Fe/src/components/Admin/KichThuoc/index.vue|Source/test cần giữ|
| M|Fe/src/components/Admin/MaGiamGia/index.vue|Source/test cần giữ|
| M|Fe/src/components/Admin/MauSac/index.vue|Source/test cần giữ|
| M|Fe/src/components/Admin/SanPham/ProductImages.vue|Source/test cần giữ|
| M|Fe/src/components/Admin/SanPham/ProductVariants.vue|Source/test cần giữ|
| M|Fe/src/components/Admin/SanPham/index.vue|Source/test cần giữ|
| M|Fe/src/components/Admin/ThongKe/DoanhThuTheoNam.vue|Source/test cần giữ|
| M|Fe/src/components/Admin/ThongKe/DoanhThuTheoThang.vue|Source/test cần giữ|
| M|Fe/src/components/Admin/ThongKe/SanPhamTheoDanhMuc.vue|Source/test cần giữ|
| M|Fe/src/components/Admin/ThuongHieu/index.vue|Source/test cần giữ|
| M|Fe/src/components/Client/CheckoutModal.vue|Source/test cần giữ|
| M|Fe/src/components/Client/ChiTietSanPham.vue|Source/test cần giữ|
| M|Fe/src/components/Client/TimKiem.vue|Source/test cần giữ|
| M|Fe/src/components/Client/TrangChu.vue|Source/test cần giữ|
| M|Fe/src/components/DangKy/index.vue|Source/test cần giữ|
| M|Fe/src/components/DangNhap/index.vue|Source/test cần giữ|
| M|Fe/src/components/ProductCard.vue|Source/test cần giữ|
| M|Fe/src/components/QuenMatKhau/index.vue|Source/test cần giữ|
| M|Fe/src/components/shared/ProductImage.vue|Source/test cần giữ|
| M|Fe/src/layout/components/AdminSearch.vue|Source/test cần giữ|
| M|Fe/src/layout/components/HeaderRocker.vue|Source/test cần giữ|
| M|Fe/src/layout/components/MenuRocker.vue|Source/test cần giữ|
| M|Fe/src/layout/wrapper/ClientLayout.vue|Source/test cần giữ|
| M|Fe/src/main.js|Source/test cần giữ|
| M|Fe/src/pages/admin/AdminProducts.vue|Source/test cần giữ|
| M|Fe/src/pages/client/Cart.vue|Source/test cần giữ|
| M|Fe/src/pages/client/Checkout.vue|Source/test cần giữ|
| M|Fe/src/pages/client/MyOrders.vue|Source/test cần giữ|
| M|Fe/src/pages/client/ProductDetail.vue|Source/test cần giữ|
| M|Fe/src/router/checkLogin.js|Source/test cần giữ|
| M|Fe/src/router/index.js|Source/test cần giữ|
| M|Fe/src/services/adminSearch.js|Source/test cần giữ|
| M|Fe/src/services/authSessions.js|Source/test cần giữ|
| M|Fe/src/services/axiosClient.js|Source/test cần giữ|
| M|Fe/src/services/productImages.js|Source/test cần giữ|
| M|Fe/src/services/resolveApiUrl.js|Source/test cần giữ|
| M|Fe/src/stores/cartStore.js|Source/test cần giữ|
| M|Fe/tests/admin-login.test.mjs|Source/test cần giữ|
| M|Fe/tests/catalog-session.test.mjs|Source/test cần giữ|
| M|Fe/tests/checkout-flow.test.mjs|Source/test cần giữ|
| M|Fe/tests/customer-header.test.mjs|Source/test cần giữ|
| M|Fe/tests/performance-build.config.mjs|Ngoài allowlist; giữ nguyên trên máy PO|
| M|Fe/tests/product-images.test.mjs|Source/test cần giữ|
| D|themmoik27be/app/Http/Controllers/Admin/DanhGiaController.php|Ngoài allowlist; giữ nguyên trên máy PO|
| M|themmoik27be/app/Http/Controllers/Admin/DanhMucController.php|Source/test cần giữ|
| M|themmoik27be/app/Http/Controllers/Admin/MaGiamGiaController.php|Source/test cần giữ|
| M|themmoik27be/app/Http/Controllers/Api/ForgotPasswordController.php|Source/test cần giữ|
| M|themmoik27be/app/Http/Controllers/AuthController.php|Source/test cần giữ|
| M|themmoik27be/app/Http/Controllers/giaoDichController.php|Source/test cần giữ|
| M|themmoik27be/app/Http/Requests/MaGiamGiaRequest.php|Source/test cần giữ|
| M|themmoik27be/app/Models/MaGiamGia.php|Source/test cần giữ|
| M|themmoik27be/app/Providers/AppServiceProvider.php|Source/test cần giữ|
| M|themmoik27be/app/Services/CheckoutService.php|Source/test cần giữ|
| M|themmoik27be/app/Services/DemoPayment.php|Source/test cần giữ|
| M|themmoik27be/config/cors.php|Source/test cần giữ|
| M|themmoik27be/routes/api.php|Source/test cần giữ|
| M|themmoik27be/routes/console.php|Source/test cần giữ|
| M|themmoik27be/tests/Feature/ApiAccessBoundaryTest.php|Source/test cần giữ|
| M|themmoik27be/tests/Feature/CheckoutIntegrityTest.php|Source/test cần giữ|
| M|themmoik27be/tests/Feature/ProductDetailDemoTest.php|Source/test cần giữ|
|??|Fe/src/assets/css/engagement.css|Source/test cần giữ|
|??|Fe/src/components/Engagement/|Source/test cần giữ|
|??|Fe/src/services/apiConfig.js|Source/test cần giữ|
|??|Fe/src/services/engagement.js|Source/test cần giữ|
|??|Fe/src/services/legacyProductImageHolds.js|Source/test cần giữ|
|??|Fe/tests/api-config.test.mjs|Source/test cần giữ|
|??|Fe/tests/customer-engagement.test.mjs|Source/test cần giữ|
|??|Fe/tests/password-reset-security.test.mjs|Source/test cần giữ|
|??|Fe/tests/sprint34.test.mjs|Source/test cần giữ|
|??|Fe/tests/submission-safety.test.mjs|Source/test cần giữ|
|??|IS432CIS-Proposal_SOFEP .docx|Không đóng gói: dữ liệu/công cụ ngoài phạm vi|
|??|docs/N5-142/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/N5-143/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/N5-147/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/PRODUCT_IMAGES_UAT.md|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/SOFEP_AUTH_CHECKOUT_HANDOFF.md|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/SOFEP_CATALOG_RESTORE_HANDOFF.md|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/SOFEP_CATALOG_VARIANT_REVIEW.csv|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/SOFEP_COMPLETION_HANDOFF.md|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/SOFEP_CUSTOMER_ENGAGEMENT_HANDOFF.md|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/SOFEP_ENGAGEMENT_ACTIVATION.md|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/SOFEP_HEADER_FIX.md|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/SOFEP_PRE_MIGRATION_SAFETY.md|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/api-image-config-2026-11-20/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/audit-2026-11-18/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/final-audit-2026-11-21/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/final-fix-2026-11-22/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/final-submission-2026-11-22/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/header-fix-screenshots/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/image-audit-2026-11-20/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/image-cleanup-uat-2026-11-20/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/image-clone-2026-11-20/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/image-demo-sync-2026-11-20/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/image-handoff-2026-11-20/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/po-image-restore-2026-11-21/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/product-content/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/product-photo-approval-2026-11-20/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/remove-image-labels-2026-11-21/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-admin-api-audit.json|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-api-benchmark.json|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-api-benchmark.mjs|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-auth-catalog-mysql-uat.json|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-auth-catalog-mysql-uat.mjs|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-catalog-audit.mjs|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-catalog-current.json|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-catalog-demo.json|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-customer-engagement-uat.json|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-customer-engagement-uat.mjs|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-engagement-activation-catalog.json|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-engagement-activation-smoke.json|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-engagement-activation.json|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-engagement-main-check.json|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-full-catalog-uat.json|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-full-catalog-uat.mjs|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-mysql-uat.json|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-page-benchmark.json|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-pre-migration-safety.json|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-restored-catalog-audit.json|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-restored-catalog-audit.mjs|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sofep-uat-screenshots/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/sprint34-2026-11-19/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/submission-handoff-2026-11-21/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|docs/uat-final-2026-11-19/|Tài liệu/bằng chứng nội bộ; chỉ đóng hướng dẫn mới|
|??|outputs/|Không đóng gói: dữ liệu/công cụ ngoài phạm vi|
|??|themmoik27be/app/Http/Controllers/Admin/CustomerCampaignController.php|Source/test cần giữ|
|??|themmoik27be/app/Http/Controllers/CustomerInboxController.php|Source/test cần giữ|
|??|themmoik27be/app/Http/Controllers/CustomerOrderController.php|Source/test cần giữ|
|??|themmoik27be/app/Http/Controllers/FeedbackController.php|Source/test cần giữ|
|??|themmoik27be/app/Http/Middleware/EngagementReady.php|Source/test cần giữ|
|??|themmoik27be/app/Http/Middleware/RequireCustomer.php|Source/test cần giữ|
|??|themmoik27be/app/Http/Requests/CampaignRequest.php|Source/test cần giữ|
|??|themmoik27be/app/Services/CustomerCampaigns.php|Source/test cần giữ|
|??|themmoik27be/app/Services/LoyaltyAudience.php|Source/test cần giữ|
|??|themmoik27be/app/Services/PasswordResetOtp.php|Source/test cần giữ|
|??|themmoik27be/app/Services/VoucherWallet.php|Source/test cần giữ|
|??|themmoik27be/config/engagement.php|Source/test cần giữ|
|??|themmoik27be/database/migrations/2026_11_06_000001_create_customer_engagement_tables.php|Source/test cần giữ|
|??|themmoik27be/database/migrations/2026_11_18_000001_add_customer_role_baseline.php|Source/test cần giữ|
|??|themmoik27be/tests/Feature/AuthSecurityTest.php|Source/test cần giữ|
|??|themmoik27be/tests/Feature/CategoryDeletionSafetyTest.php|Source/test cần giữ|
|??|themmoik27be/tests/Feature/CorsConfigurationTest.php|Source/test cần giữ|
|??|themmoik27be/tests/Feature/CustomerEngagementTest.php|Source/test cần giữ|
|??|themmoik27be/tests/Feature/CustomerOrderHistoryTest.php|Source/test cần giữ|
|??|themmoik27be/tests/Feature/LegacyReviewRetirementTest.php|Source/test cần giữ|
|??|themmoik27be/tests/Feature/MigrationBaselineTest.php|Source/test cần giữ|
|??|themmoik27be/tests/Support/CheckoutUat.php|Không đóng gói: dữ liệu/công cụ ngoài phạm vi|
|??|themmoik27be/tests/Support/EngagementUatRouter.php|Không đóng gói: dữ liệu/công cụ ngoài phạm vi|
|??|themmoik27be/tests/Support/EngagementUatWorker.php|Không đóng gói: dữ liệu/công cụ ngoài phạm vi|
|??|themmoik27be/tests/Support/FinalPurchaseEngagementUat.php|Không đóng gói: dữ liệu/công cụ ngoài phạm vi|
|??|themmoik27be/tests/Support/MySqlSecurityUat.php|Không đóng gói: dữ liệu/công cụ ngoài phạm vi|
|??|themmoik27be/tests/Support/PrepareEngagementUat.php|Không đóng gói: dữ liệu/công cụ ngoài phạm vi|
|??|themmoik27be/tests/Support/SecurityBrowserBoot.php|Không đóng gói: dữ liệu/công cụ ngoài phạm vi|
|??|themmoik27be/tests/Support/SecurityBrowserControl.php|Không đóng gói: dữ liệu/công cụ ngoài phạm vi|
|??|themmoik27be/tests/Support/SecurityBrowserEvidence.php|Không đóng gói: dữ liệu/công cụ ngoài phạm vi|
|??|themmoik27be/tests/Support/SecurityBrowserUat.php|Không đóng gói: dữ liệu/công cụ ngoài phạm vi|
|??|themmoik27be/tests/Support/SecurityCacheInspection.php|Không đóng gói: dữ liệu/công cụ ngoài phạm vi|
|??|themmoik27be/tests/Support/Sprint34Uat.php|Không đóng gói: dữ liệu/công cụ ngoài phạm vi|
|??|themmoik27be/tests/Support/checkout-browser-router.php|Không đóng gói: dữ liệu/công cụ ngoài phạm vi|
