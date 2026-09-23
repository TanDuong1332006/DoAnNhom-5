<template>
  <div>
    <h4 class="fw-bold mb-4"><i class="bi bi-speedometer2 me-2"></i>Dashboard</h4>
    <div v-if="loading" class="text-center py-5"><div class="spinner-border text-warning"></div></div>
    <div v-else>
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card border-0 shadow-sm text-center p-3" style="border-left: 4px solid #ffc107 !important;">
            <i class="bi bi-box-seam fs-2 text-warning mb-2"></i>
            <h3 class="fw-bold">{{ stats.total_products }}</h3>
            <small class="text-muted">Tổng sản phẩm</small>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm text-center p-3" style="border-left: 4px solid #0dcaf0 !important;">
            <i class="bi bi-people fs-2 text-info mb-2"></i>
            <h3 class="fw-bold">{{ stats.total_customers }}</h3>
            <small class="text-muted">Khách hàng</small>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm text-center p-3" style="border-left: 4px solid #198754 !important;">
            <i class="bi bi-receipt fs-2 text-success mb-2"></i>
            <h3 class="fw-bold">{{ stats.total_orders }}</h3>
            <small class="text-muted">Tổng đơn hàng</small>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm text-center p-3" style="border-left: 4px solid #dc3545 !important;">
            <i class="bi bi-currency-dollar fs-2 text-danger mb-2"></i>
            <h3 class="fw-bold">{{ formatPrice(stats.revenue_month) }}đ</h3>
            <small class="text-muted">Doanh thu tháng</small>
          </div>
        </div>
      </div>

      <!-- Alert bars -->
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <div class="alert alert-warning d-flex align-items-center mb-0">
            <i class="bi bi-clock-history fs-5 me-3"></i>
            <div><strong>{{ stats.pending_orders }}</strong> đơn hàng chờ xác nhận</div>
            <router-link to="/admin/orders" class="btn btn-warning btn-sm ms-auto">Xem ngay</router-link>
          </div>
        </div>
        <div class="col-md-6">
          <div class="alert alert-danger d-flex align-items-center mb-0">
            <i class="bi bi-exclamation-triangle fs-5 me-3"></i>
            <div><strong>{{ stats.low_stock_products }}</strong> sản phẩm sắp hết hàng</div>
            <router-link to="/admin/products" class="btn btn-danger btn-sm ms-auto">Xem ngay</router-link>
          </div>
        </div>
      </div>

      <!-- Recent Orders -->
      <div class="card border-0 shadow-sm">
        <div class="card-header fw-bold">Đơn hàng gần đây</div>
        <div class="card-body p-0">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr><th>Mã ĐH</th><th>Khách hàng</th><th>Tổng tiền</th><th>Trạng thái</th><th>Ngày đặt</th></tr>
            </thead>
            <tbody>
              <tr v-for="order in recentOrders" :key="order.id">
                <td><strong>{{ order.code }}</strong></td>
                <td>{{ order.name }}</td>
                <td class="text-danger fw-bold">{{ formatPrice(order.total) }}đ</td>
                <td><span :class="statusClass(order.status)" class="badge">{{ statusLabel(order.status) }}</span></td>
                <td>{{ formatDate(order.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axiosClient from '@/services/axiosClient'

export default {
  name: 'Dashboard',
  data() { return { stats: {}, recentOrders: [], loading: true } },
  mounted() { this.fetchData() },
  methods: {
    async fetchData() {
      const res = await axiosClient.get('/admin/dashboard')
      if (res.data.status === 1) { this.stats = res.data.stats; this.recentOrders = res.data.recent_orders }
      this.loading = false
    },
    formatPrice(p) { return new Intl.NumberFormat('vi-VN').format(p || 0) },
    formatDate(d) { return new Date(d).toLocaleDateString('vi-VN') },
    statusLabel(s) { return ['Chờ XN','Xử lý','Đang giao','Đã giao','Huỷ'][s] || 'N/A' },
    statusClass(s) { return ['bg-secondary','bg-warning text-dark','bg-info text-dark','bg-success','bg-danger'][s] || 'bg-secondary' },
  },
}
</script>
