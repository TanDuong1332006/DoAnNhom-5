<template>
  <div>
    <!-- Hero Banner -->
    <section class="bg-dark text-light py-5" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%) !important;">
      <div class="container py-5">
        <div class="row align-items-center">
          <div class="col-lg-6">
            <span class="badge bg-warning text-dark mb-3">🔥 Mùa Sale 2026</span>
            <h1 class="display-4 fw-bold mb-4">
              Thời Trang <span class="text-warning">Đỉnh Cao</span><br>Giá Tốt Nhất
            </h1>
            <p class="lead text-muted mb-4">Khám phá hàng nghìn sản phẩm thời trang chính hãng, cập nhật xu hướng mới nhất với mức giá cực kỳ hấp dẫn.</p>
            <div class="d-flex gap-3">
              <router-link to="/shop" class="btn btn-warning btn-lg px-5">Mua Ngay <i class="bi bi-arrow-right ms-2"></i></router-link>
              <router-link to="/shop?featured=1" class="btn btn-outline-light btn-lg">SP Nổi Bật</router-link>
            </div>
          </div>
          <div class="col-lg-6 text-center d-none d-lg-block">
            <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=600" class="img-fluid rounded-3 shadow-lg" alt="Fashion">
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Bar -->
    <section class="bg-warning py-3">
      <div class="container">
        <div class="row text-center text-dark">
          <div class="col-md-3 col-6 py-2">
            <i class="bi bi-truck fs-5 me-2"></i><strong>Miễn ship từ 500K</strong>
          </div>
          <div class="col-md-3 col-6 py-2">
            <i class="bi bi-arrow-return-left fs-5 me-2"></i><strong>Đổi trả 30 ngày</strong>
          </div>
          <div class="col-md-3 col-6 py-2">
            <i class="bi bi-shield-check fs-5 me-2"></i><strong>Hàng chính hãng</strong>
          </div>
          <div class="col-md-3 col-6 py-2">
            <i class="bi bi-headset fs-5 me-2"></i><strong>Hỗ trợ 24/7</strong>
          </div>
        </div>
      </div>
    </section>

    <!-- Categories -->
    <section class="py-5">
      <div class="container">
        <h2 class="text-center fw-bold mb-4">Danh Mục Sản Phẩm</h2>
        <div class="row g-3">
          <div v-for="cat in categories" :key="cat.id" class="col-6 col-md-3">
            <router-link :to="'/shop?category_id=' + cat.id" class="text-decoration-none">
              <div class="card border-0 shadow-sm text-center p-3 h-100 category-card">
                <img :src="cat.image || 'https://via.placeholder.com/100'" class="rounded-circle mx-auto mb-3" width="80" height="80" style="object-fit:cover;" :alt="cat.name">
                <h6 class="fw-semibold text-dark mb-1">{{ cat.name }}</h6>
                <small class="text-muted">{{ cat.products_count }} sản phẩm</small>
              </div>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="py-5 bg-light">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="fw-bold mb-0">🔥 Sản Phẩm Nổi Bật</h2>
          <router-link to="/shop" class="btn btn-outline-warning">Xem tất cả <i class="bi bi-arrow-right"></i></router-link>
        </div>
        <div class="row g-3">
          <div v-for="product in featuredProducts" :key="product.id" class="col-6 col-md-3">
            <ProductCard :product="product" @add-to-cart="addToCart" />
          </div>
        </div>
      </div>
    </section>

    <!-- New Arrivals -->
    <section class="py-5">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="fw-bold mb-0">✨ Hàng Mới Về</h2>
          <router-link to="/shop?sort_by=created_at" class="btn btn-outline-dark">Xem tất cả</router-link>
        </div>
        <div class="row g-3">
          <div v-for="product in newProducts" :key="product.id" class="col-6 col-md-3">
            <ProductCard :product="product" @add-to-cart="addToCart" />
          </div>
        </div>
      </div>
    </section>

    <!-- Banner CTA -->
    <section class="py-5 bg-dark text-white">
      <div class="container text-center">
        <h3 class="fw-bold mb-3">Đăng ký nhận ưu đãi độc quyền</h3>
        <p class="text-muted mb-4">Nhận mã giảm giá 10% ngay khi đăng ký tài khoản!</p>
        <router-link to="/register" class="btn btn-warning btn-lg px-5">Đăng Ký Ngay</router-link>
      </div>
    </section>
  </div>
</template>

<script>
import axiosClient from '@/services/axiosClient'
import { authStore } from '@/stores/authStore'
import { cartStore } from '@/stores/cartStore'
import ProductCard from '@/components/ProductCard.vue'

export default {
  name: 'Home',
  components: { ProductCard },
  data() {
    return {
      categories: [],
      featuredProducts: [],
      newProducts: [],
    }
  },
  mounted() {
    this.fetchData()
  },
  methods: {
    async fetchData() {
      const [catRes, featuredRes, newRes] = await Promise.all([
        axiosClient.get('/categories'),
        axiosClient.get('/products/featured'),
        axiosClient.get('/products/new-arrivals'),
      ])
      if (catRes.data.status === 1) this.categories = catRes.data.data
      if (featuredRes.data.status === 1) this.featuredProducts = featuredRes.data.data
      if (newRes.data.status === 1) this.newProducts = newRes.data.data
    },
    async addToCart(productId) {
      if (!authStore.isLoggedIn) {
        this.$toast.error('Vui lòng đăng nhập để thêm vào giỏ hàng!')
        this.$router.push('/login')
        return
      }
      const res = await cartStore.addItem(productId, 1)
      if (res.status === 1) this.$toast.success('Đã thêm vào giỏ hàng!')
      else this.$toast.error(res.message || 'Có lỗi xảy ra!')
    },
  },
}
</script>

<style scoped>
.category-card { transition: transform 0.2s; }
.category-card:hover { transform: translateY(-5px); }
</style>
