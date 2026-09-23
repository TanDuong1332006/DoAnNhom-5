<template>
  <div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><router-link to="/">Trang chủ</router-link></li>
        <li class="breadcrumb-item"><router-link to="/shop">Cửa hàng</router-link></li>
        <li class="breadcrumb-item active">{{ product.name }}</li>
      </ol>
    </nav>

    <div v-if="loading" class="text-center py-5"><div class="spinner-border text-warning"></div></div>
    <div v-else-if="product.id" class="row g-5">
      <!-- Image -->
      <div class="col-md-5">
        <img :src="selectedImage || product.image" class="img-fluid rounded-3 shadow" :alt="product.name" style="width:100%; height: 450px; object-fit: cover;">
        <div v-if="product.images?.length" class="d-flex gap-2 mt-3">
          <img v-for="img in product.images" :key="img.id" :src="img.image" class="rounded border" width="70" height="70" style="object-fit:cover; cursor:pointer;" @click="selectedImage = img.image">
        </div>
      </div>

      <!-- Info -->
      <div class="col-md-7">
        <span class="badge bg-light text-dark border mb-2">{{ product.category?.name }}</span>
        <h2 class="fw-bold">{{ product.name }}</h2>
        <div class="d-flex align-items-center gap-3 mb-3">
          <span class="fs-3 fw-bold text-danger">{{ formatPrice(product.sale_price || product.price) }}đ</span>
          <span v-if="product.sale_price" class="fs-5 text-muted text-decoration-line-through">{{ formatPrice(product.price) }}đ</span>
          <span v-if="product.sale_price" class="badge bg-danger">-{{ discountPercent }}%</span>
        </div>
        <p class="text-muted mb-4">{{ product.description }}</p>

        <div class="mb-4">
          <label class="form-label fw-semibold">Số lượng:</label>
          <div class="input-group" style="width: 150px;">
            <button class="btn btn-outline-secondary" @click="qty = Math.max(1, qty - 1)">−</button>
            <input v-model.number="qty" type="number" class="form-control text-center" min="1" :max="product.quantity">
            <button class="btn btn-outline-secondary" @click="qty = Math.min(product.quantity, qty + 1)">+</button>
          </div>
          <small class="text-muted mt-1 d-block">Còn {{ product.quantity }} sản phẩm</small>
        </div>

        <div class="d-flex gap-3">
          <button @click="addToCart" :disabled="product.quantity === 0 || addingToCart" class="btn btn-warning btn-lg flex-grow-1">
            <span v-if="addingToCart" class="spinner-border spinner-border-sm me-2"></span>
            <i v-else class="bi bi-cart-plus me-2"></i>Thêm vào giỏ hàng
          </button>
          <router-link to="/cart" class="btn btn-dark btn-lg">Mua ngay</router-link>
        </div>

        <div class="mt-4 p-3 bg-light rounded">
          <div class="d-flex gap-4 text-muted small">
            <span><i class="bi bi-truck me-1"></i>Miễn ship từ 500K</span>
            <span><i class="bi bi-arrow-return-left me-1"></i>Đổi trả 30 ngày</span>
            <span><i class="bi bi-shield-check me-1"></i>Hàng chính hãng</span>
          </div>
        </div>
      </div>

      <!-- Description -->
      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header fw-bold">Mô tả sản phẩm</div>
          <div class="card-body" v-html="product.content || product.description"></div>
        </div>
      </div>

      <!-- Related -->
      <div v-if="related.length" class="col-12">
        <h4 class="fw-bold mb-3">Sản phẩm liên quan</h4>
        <div class="row g-3">
          <div v-for="p in related" :key="p.id" class="col-6 col-md-3">
            <ProductCard :product="p" @add-to-cart="addToCartById" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axiosClient from '@/services/axiosClient'
import { authStore } from '@/stores/authStore'
import { cartStore } from '@/stores/cartStore'
import ProductCard from '@/components/ProductCard.vue'

export default {
  name: 'ProductDetail',
  components: { ProductCard },
  data() {
    return { product: {}, related: [], loading: true, qty: 1, addingToCart: false, selectedImage: null }
  },
  watch: { '$route.params.id': { immediate: true, handler() { this.fetchProduct() } } },
  computed: {
    discountPercent() {
      if (!this.product.sale_price) return 0
      return Math.round((1 - this.product.sale_price / this.product.price) * 100)
    },
  },
  methods: {
    async fetchProduct() {
      this.loading = true
      const res = await axiosClient.get(`/products/${this.$route.params.id}`)
      if (res.data.status === 1) { this.product = res.data.data; this.related = res.data.related }
      this.loading = false
    },
    async addToCart() {
      if (!authStore.isLoggedIn) { this.$toast.error('Vui lòng đăng nhập!'); this.$router.push('/login'); return }
      this.addingToCart = true
      const res = await cartStore.addItem(this.product.id, this.qty)
      if (res.status === 1) this.$toast.success('Đã thêm vào giỏ hàng!')
      else this.$toast.error(res.message)
      this.addingToCart = false
    },
    async addToCartById(productId) {
      if (!authStore.isLoggedIn) { this.$toast.error('Vui lòng đăng nhập!'); this.$router.push('/login'); return }
      const res = await cartStore.addItem(productId, 1)
      if (res.status === 1) this.$toast.success('Đã thêm vào giỏ hàng!')
      else this.$toast.error(res.message)
    },
    formatPrice(p) { return new Intl.NumberFormat('vi-VN').format(p) },
  },
}
</script>
