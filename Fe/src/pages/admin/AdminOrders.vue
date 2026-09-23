<template>
  <div>
    <h4 class="fw-bold mb-4"><i class="bi bi-receipt me-2"></i>Quản Lý Đơn Hàng</h4>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <div class="row g-2">
          <div class="col-md-4">
            <input v-model="search" type="text" class="form-control" placeholder="Tìm mã đơn, tên KH..." @input="fetchOrders">
          </div>
          <div class="col-md-3">
            <select v-model="filterStatus" class="form-select" @change="fetchOrders">
              <option value="">Tất cả trạng thái</option>
              <option value="0">Chờ xác nhận</option>
              <option value="1">Đang xử lý</option>
              <option value="2">Đang giao</option>
              <option value="3">Đã giao</option>
              <option value="4">Đã huỷ</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5"><div class="spinner-border text-warning"></div></div>
        <table v-else class="table table-hover align-middle mb-0">
          <thead class="table-dark">
            <tr><th>Mã ĐH</th><th>Khách hàng</th><th>SĐT</th><th>Tổng tiền</th><th>TT Thanh toán</th><th>Trạng thái</th><th>Ngày</th><th>Action</th></tr>
          </thead>
          <tbody>
            <tr v-for="order in orders" :key="order.id">
              <td><strong class="text-warning">{{ order.code }}</strong></td>
              <td>{{ order.name }}</td>
              <td>{{ order.phone }}</td>
              <td class="fw-bold text-danger">{{ formatPrice(order.total) }}đ</td>
              <td><span class="badge bg-info text-dark">{{ order.payment_method === 1 ? 'COD' : 'CK' }}</span></td>
              <td>
                <select :value="order.status" class="form-select form-select-sm" @change="updateStatus(order, +$event.target.value)" style="width: 140px;">
                  <option value="0">Chờ xác nhận</option>
                  <option value="1">Đang xử lý</option>
                  <option value="2">Đang giao</option>
                  <option value="3">Đã giao</option>
                  <option value="4">Đã huỷ</option>
                </select>
              </td>
              <td>{{ formatDate(order.created_at) }}</td>
              <td><button class="btn btn-sm btn-outline-primary" @click="viewDetail(order.id)"><i class="bi bi-eye"></i></button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white"><h5 class="modal-title">Chi Tiết Đơn Hàng</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
          <div class="modal-body" v-if="selectedOrder">
            <div class="row mb-3">
              <div class="col-md-6">
                <p><strong>Mã đơn:</strong> {{ selectedOrder.code }}</p>
                <p><strong>Khách hàng:</strong> {{ selectedOrder.name }}</p>
                <p><strong>Email:</strong> {{ selectedOrder.email }}</p>
                <p><strong>SĐT:</strong> {{ selectedOrder.phone }}</p>
              </div>
              <div class="col-md-6">
                <p><strong>Địa chỉ:</strong> {{ selectedOrder.address }}</p>
                <p><strong>TT Thanh toán:</strong> {{ selectedOrder.payment_method === 1 ? 'COD' : 'Chuyển khoản' }}</p>
                <p><strong>Ghi chú:</strong> {{ selectedOrder.note || 'Không có' }}</p>
              </div>
            </div>
            <table class="table table-sm">
              <thead class="table-light">
                <tr><th>Sản phẩm</th><th>Giá</th><th>SL</th><th>Thành tiền</th></tr>
              </thead>
              <tbody>
                <tr v-for="item in selectedOrder.items" :key="item.id">
                  <td><img :src="item.product_image" width="40" height="40" class="rounded me-2" style="object-fit:cover;">{{ item.product_name }}</td>
                  <td>{{ formatPrice(item.price) }}đ</td>
                  <td>{{ item.quantity }}</td>
                  <td>{{ formatPrice(item.subtotal) }}đ</td>
                </tr>
              </tbody>
            </table>
            <div class="text-end">
              <p>Tạm tính: {{ formatPrice(selectedOrder.subtotal) }}đ</p>
              <p v-if="selectedOrder.discount > 0" class="text-success">Giảm giá: -{{ formatPrice(selectedOrder.discount) }}đ</p>
              <p>Phí ship: {{ formatPrice(selectedOrder.shipping_fee) }}đ</p>
              <h5 class="text-danger">Tổng: {{ formatPrice(selectedOrder.total) }}đ</h5>
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
  name: 'AdminOrders',
  data() { return { orders: [], loading: false, search: '', filterStatus: '', selectedOrder: null, detailModal: null } },
  mounted() { this.fetchOrders() },
  methods: {
    async fetchOrders() {
      this.loading = true
      const res = await axiosClient.get('/admin/orders', { params: { search: this.search, status: this.filterStatus } })
      if (res.data.status === 1) this.orders = res.data.data.data
      this.loading = false
    },
    async updateStatus(order, status) {
      const res = await axiosClient.post('/admin/orders/update-status', { id: order.id, status })
      if (res.data.status === 1) { this.$toast.success(res.data.message); order.status = status }
      else this.$toast.error(res.data.message)
    },
    async viewDetail(id) {
      const res = await axiosClient.get(`/admin/orders/${id}`)
      if (res.data.status === 1) {
        this.selectedOrder = res.data.data
        if (!this.detailModal) this.detailModal = new bootstrap.Modal(document.getElementById('orderDetailModal'))
        this.detailModal.show()
      }
    },
    formatPrice(p) { return new Intl.NumberFormat('vi-VN').format(p) },
    formatDate(d) { return new Date(d).toLocaleDateString('vi-VN') },
  },
}
</script>
