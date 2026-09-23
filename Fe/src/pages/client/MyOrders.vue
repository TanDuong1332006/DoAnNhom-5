<template>
  <div class="container py-5">
    <h3 class="fw-bold mb-4"><i class="bi bi-bag me-2"></i>Đơn Hàng Của Tôi</h3>
    <div v-if="loading" class="text-center py-5"><div class="spinner-border text-warning"></div></div>
    <div v-else-if="orders.length === 0" class="text-center py-5 text-muted">
      <i class="bi bi-bag-x fs-1"></i><p class="mt-3">Bạn chưa có đơn hàng nào</p>
      <router-link to="/shop" class="btn btn-warning">Mua hàng ngay</router-link>
    </div>
    <div v-else>
      <div v-for="order in orders" :key="order.id" class="card border-0 shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span class="fw-bold">{{ order.code }}</span>
          <span :class="statusClass(order.status)" class="badge">{{ statusLabel(order.status) }}</span>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <small class="text-muted">Ngày đặt: {{ formatDate(order.created_at) }}</small><br>
              <small class="text-muted">Địa chỉ: {{ order.address }}</small>
            </div>
            <div class="col-md-6 text-md-end">
              <div class="fw-bold text-danger fs-5">{{ formatPrice(order.total) }}đ</div>
              <small class="text-muted">{{ order.items?.length }} sản phẩm</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axiosClient from '@/services/axiosClient'

export default {
  name: 'MyOrders',
  data() { return { orders: [], loading: true } },
  mounted() { this.fetchOrders() },
  methods: {
    async fetchOrders() {
      const res = await axiosClient.get('/orders')
      if (res.data.status === 1) this.orders = res.data.data
      this.loading = false
    },
    formatPrice(p) { return new Intl.NumberFormat('vi-VN').format(p) },
    formatDate(d) { return new Date(d).toLocaleDateString('vi-VN') },
    statusLabel(s) { return ['Chờ xác nhận','Đang xử lý','Đang giao','Đã giao','Đã huỷ'][s] || 'N/A' },
    statusClass(s) { return ['bg-secondary','bg-warning text-dark','bg-info','bg-success','bg-danger'][s] || 'bg-secondary' },
  },
}
</script>
