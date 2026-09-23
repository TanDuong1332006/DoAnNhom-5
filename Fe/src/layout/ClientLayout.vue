<template>
  <div>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
      <div class="container">
        <router-link class="navbar-brand fw-bold fs-4" to="/">
          <i class="bi bi-bag-heart-fill text-warning me-2"></i>ShopVN
        </router-link>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <router-link class="nav-link" to="/">Trang Chủ</router-link>
            </li>
            <li class="nav-item">
              <router-link class="nav-link" to="/shop">Cửa Hàng</router-link>
            </li>
          </ul>

          <!-- Search -->
          <form class="d-flex me-3" @submit.prevent="goSearch">
            <div class="input-group">
              <input v-model="searchQuery" class="form-control form-control-sm" type="search" placeholder="Tìm sản phẩm...">
              <button class="btn btn-warning btn-sm" type="submit"><i class="bi bi-search"></i></button>
            </div>
          </form>

          <!-- Cart -->
          <router-link to="/cart" class="btn btn-outline-warning btn-sm me-2 position-relative">
            <i class="bi bi-cart3"></i>
            <span v-if="cart.count > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              {{ cart.count }}
            </span>
          </router-link>

          <!-- Auth -->
          <template v-if="auth.isLoggedIn && auth.isCustomer">
            <div class="dropdown">
              <button class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-1"></i>{{ auth.user?.name }}
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><router-link class="dropdown-item" to="/my-orders"><i class="bi bi-bag me-2"></i>Đơn hàng của tôi</router-link></li>
                <li><router-link class="dropdown-item" to="/profile"><i class="bi bi-person me-2"></i>Hồ sơ</router-link></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" @click.prevent="logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
              </ul>
            </div>
          </template>
          <template v-else-if="!auth.isLoggedIn">
            <router-link to="/login" class="btn btn-warning btn-sm me-1">Đăng nhập</router-link>
            <router-link to="/register" class="btn btn-outline-light btn-sm">Đăng ký</router-link>
          </template>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main>
      <router-view></router-view>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5 mt-5">
      <div class="container">
        <div class="row">
          <div class="col-md-4 mb-4">
            <h5 class="fw-bold text-warning"><i class="bi bi-bag-heart-fill me-2"></i>ShopVN</h5>
            <p class="text-muted">Cửa hàng thời trang online uy tín - Chất lượng tốt, giá cả phải chăng.</p>
          </div>
          <div class="col-md-4 mb-4">
            <h6 class="fw-bold">Danh mục</h6>
            <ul class="list-unstyled">
              <li><router-link to="/shop" class="text-muted text-decoration-none">Tất cả sản phẩm</router-link></li>
              <li><router-link to="/shop?featured=1" class="text-muted text-decoration-none">Sản phẩm nổi bật</router-link></li>
            </ul>
          </div>
          <div class="col-md-4 mb-4">
            <h6 class="fw-bold">Hỗ trợ</h6>
            <ul class="list-unstyled text-muted">
              <li><i class="bi bi-telephone me-2"></i>0909 123 456</li>
              <li><i class="bi bi-envelope me-2"></i>support@shopvn.com</li>
              <li><i class="bi bi-geo-alt me-2"></i>TP. Hồ Chí Minh</li>
            </ul>
          </div>
        </div>
        <hr class="border-secondary">
        <p class="text-center text-muted mb-0">&copy; 2026 ShopVN. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script>
import { authStore } from '@/stores/authStore'
import { cartStore } from '@/stores/cartStore'

export default {
  name: 'ClientLayout',
  data() {
    return {
      searchQuery: '',
      auth: authStore,
      cart: cartStore,
    }
  },
  methods: {
    goSearch() {
      if (this.searchQuery.trim()) {
        this.$router.push({ path: '/shop', query: { search: this.searchQuery.trim() } })
      }
    },
    logout() {
      authStore.logout()
      this.$router.push('/')
    },
  },
  mounted() {
    if (authStore.isLoggedIn && authStore.isCustomer) {
      cartStore.fetchCart()
    }
  },
}
</script>
