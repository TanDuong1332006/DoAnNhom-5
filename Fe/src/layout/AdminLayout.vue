<template>
  <div class="d-flex" style="min-height: 100vh;">
    <!-- Sidebar -->
    <div class="bg-dark text-light" style="width: 250px; min-height: 100vh;">
      <div class="p-3 border-bottom border-secondary">
        <h5 class="fw-bold text-warning mb-0">
          <i class="bi bi-shield-check me-2"></i>Admin Panel
        </h5>
        <small class="text-muted">{{ auth.user?.name }}</small>
      </div>
      <nav class="nav flex-column py-3">
        <router-link class="nav-link text-light px-3 py-2" to="/admin" exact-active-class="bg-warning text-dark fw-bold">
          <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </router-link>
        <router-link class="nav-link text-light px-3 py-2" to="/admin/products" active-class="bg-warning text-dark fw-bold">
          <i class="bi bi-box-seam me-2"></i>Sản Phẩm
        </router-link>
        <router-link class="nav-link text-light px-3 py-2" to="/admin/categories" active-class="bg-warning text-dark fw-bold">
          <i class="bi bi-tags me-2"></i>Danh Mục
        </router-link>
        <router-link class="nav-link text-light px-3 py-2" to="/admin/orders" active-class="bg-warning text-dark fw-bold">
          <i class="bi bi-receipt me-2"></i>Đơn Hàng
        </router-link>
        <router-link class="nav-link text-light px-3 py-2" to="/admin/customers" active-class="bg-warning text-dark fw-bold">
          <i class="bi bi-people me-2"></i>Khách Hàng
        </router-link>
        <router-link class="nav-link text-light px-3 py-2" to="/admin/coupons" active-class="bg-warning text-dark fw-bold">
          <i class="bi bi-ticket-perforated me-2"></i>Mã Giảm Giá
        </router-link>
        <hr class="border-secondary mx-3">
        <a href="#" class="nav-link text-danger px-3 py-2" @click.prevent="logout">
          <i class="bi bi-box-arrow-right me-2"></i>Đăng Xuất
        </a>
      </nav>
    </div>

    <!-- Main content -->
    <div class="flex-grow-1 bg-light">
      <div class="bg-white border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold text-muted">ShopVN Admin Dashboard</h6>
        <span class="text-muted small"><i class="bi bi-person-circle me-1"></i>{{ auth.user?.email }}</span>
      </div>
      <div class="p-4">
        <router-view></router-view>
      </div>
    </div>
  </div>
</template>

<script>
import { authStore } from '@/stores/authStore'

export default {
  name: 'AdminLayout',
  data() {
    return { auth: authStore }
  },
  methods: {
    logout() {
      authStore.logout()
      this.$router.push('/admin/login')
    },
  },
}
</script>
