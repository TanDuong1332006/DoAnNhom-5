<template>
  <div class="container py-5">
    <div class="row">
      <!-- Sidebar Filter -->
      <div class="col-lg-3 mb-4">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-dark text-white fw-bold">Bộ Lọc</div>
          <div class="card-body">
            <label class="form-label fw-semibold">Danh mục</label>
            <select v-model="filters.category_id" class="form-select form-select-sm mb-3" @change="fetchProducts">
              <option value="">Tất cả danh mục</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>

            <label class="form-label fw-semibold">Sắp xếp</label>
            <select v-model="filters.sort_by" class="form-select form-select-sm mb-3" @change="fetchProducts">
              <option value="created_at">Mới nhất</option>
              <option value="price_asc">Giá tăng dần</option>
              <option value="price_desc">Giá giảm dần</option>
              <option value="views">Phổ biến nhất</option>
            </select>

            <label class="form-label fw-semibold">Tìm kiếm</label>
            <div class="input-group input-group-sm">
              <input v-model="filters.search" type="text" class="form-control" placeholder="Tên sản phẩm..." @keyup.enter="fetchProducts">
              <button class="btn btn-warning" @click="fetchProducts"><i class="bi bi-search"></i></button>
            </div>
          </div>
        </div>
      </div>

      <!-- Products Grid -->
      <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="mb-0">Tìm thấy <span class="text-warning fw-bold">{{ total }}</span> sản phẩm</h5>
          <span class="text-muted small">Trang {{ currentPage }}/{{ totalPages }}</span>
        </div>

        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-warning"></div>
        </div>
        <div v-else-if="products.length === 0" class="text-center py-5 text-muted">
          <i class="bi bi-search fs-1"></i>
          <p class="mt-3">Không tìm thấy sản phẩm phù hợp.</p>
        </div>
        <div v-else class="row g-3">
          <div v-for="product in products" :key="product.id" class="col-6 col-md-4">
            <ProductCard :product="product" @add-to-cart="addToCart" />
          </div>
        </div>

        <!-- Pagination -->
        <nav v-if="totalPages > 1" class="mt-4">
          <ul class="pagination justify-content-center">
            <li class="page-item" :class="{disabled: currentPage === 1}">
              <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)"><i class="bi bi-chevron-left"></i></a>
            </li>
            <li v-for="p in totalPages" :key="p" class="page-item" :class="{active: p === currentPage}">
              <a class="page-link" href="#" @click.prevent="changePage(p)">{{ p }}</a>
            </li>
            <li class="page-item" :class="{disabled: currentPage === totalPages}">
              <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)"><i class="bi bi-chevron-right"></i></a>
            </li>
          </ul>
        </nav>
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
  name: 'Shop',
  components: { ProductCard },
  data() {
    return {
      products: [], categories: [],
      loading: false,
      total: 0, currentPage: 1, totalPages: 1,
      filters: { category_id: '', search: '', sort_by: 'created_at' },
    }
  },
  watch: {
    '$route.query': { immediate: true, handler(q) {
      this.filters.category_id = q.category_id || ''
      this.filters.search = q.search || ''
      this.fetchProducts()
    }}
  },
  mounted() {
    this.fetchCategories()
  },
  methods: {
    async fetchCategories() {
      const res = await axiosClient.get('/categories')
      if (res.data.status === 1) this.categories = res.data.data
    },
    async fetchProducts(page = 1) {
      this.loading = true
      this.currentPage = page
      const params = { page, per_page: 12, category_id: this.filters.category_id, search: this.filters.search }
      if (this.filters.sort_by === 'price_asc') { params.sort_by = 'price'; params.sort_dir = 'asc' }
      else if (this.filters.sort_by === 'price_desc') { params.sort_by = 'price'; params.sort_dir = 'desc' }
      else { params.sort_by = this.filters.sort_by; params.sort_dir = 'desc' }

      const res = await axiosClient.get('/products', { params })
      if (res.data.status === 1) {
        this.products = res.data.data.data
        this.total = res.data.data.total
        this.totalPages = res.data.data.last_page
      }
      this.loading = false
    },
    changePage(p) {
      if (p < 1 || p > this.totalPages) return
      this.fetchProducts(p)
    },
    async addToCart(productId) {
      if (!authStore.isLoggedIn) { this.$toast.error('Vui lòng đăng nhập!'); this.$router.push('/login'); return }
      const res = await cartStore.addItem(productId, 1)
      if (res.status === 1) this.$toast.success('Đã thêm vào giỏ hàng!')
      else this.$toast.error(res.message)
    },
  },
}
</script>
