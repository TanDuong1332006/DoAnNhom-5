<template>
  <div class="card h-100 border-0 shadow-sm product-card">
    <div class="position-relative">
      <router-link :to="'/product/' + product.id">
        <img :src="product.image" class="card-img-top" :alt="product.name" style="height: 220px; object-fit: cover;">
      </router-link>
      <span v-if="product.sale_price" class="badge bg-danger position-absolute top-0 end-0 m-2">
        -{{ discountPercent }}%
      </span>
      <span v-if="product.is_featured" class="badge bg-warning text-dark position-absolute top-0 start-0 m-2">
        <i class="bi bi-star-fill"></i> Nổi bật
      </span>
    </div>
    <div class="card-body d-flex flex-column">
      <small class="text-muted mb-1">{{ product.category?.name }}</small>
      <h6 class="card-title fw-semibold text-truncate">{{ product.name }}</h6>
      <div class="mt-auto">
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="fw-bold text-danger fs-6">{{ formatPrice(product.sale_price || product.price) }}đ</span>
          <span v-if="product.sale_price" class="text-muted text-decoration-line-through small">{{ formatPrice(product.price) }}đ</span>
        </div>
        <button @click="$emit('add-to-cart', product.id)" class="btn btn-warning btn-sm w-100">
          <i class="bi bi-cart-plus me-1"></i>Thêm vào giỏ
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProductCard',
  props: { product: { type: Object, required: true } },
  emits: ['add-to-cart'],
  computed: {
    discountPercent() {
      if (!this.product.sale_price) return 0
      return Math.round((1 - this.product.sale_price / this.product.price) * 100)
    },
  },
  methods: {
    formatPrice(price) {
      return new Intl.NumberFormat('vi-VN').format(price)
    },
  },
}
</script>

<style scoped>
.product-card { transition: transform 0.2s; }
.product-card:hover { transform: translateY(-3px); }
</style>
