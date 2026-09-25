<template>
  <div>
    <h4 class="fw-bold mb-4"><i class="bi bi-people me-2"></i>Quản Lý Khách Hàng</h4>
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <input v-model="search" type="text" class="form-control" placeholder="Tìm tên, email..." style="max-width:350px;" @input="fetchCustomers">
      </div>
    </div>
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5"><div class="spinner-border text-warning"></div></div>
        <table v-else class="table table-hover align-middle mb-0">
          <thead class="table-dark">
            <tr><th>#</th><th>Tên</th><th>Email</th><th>SĐT</th><th>Đơn hàng</th><th>Trạng thái</th><th>Action</th></tr>
          </thead>
          <tbody>
            <tr v-for="(c, i) in customers" :key="c.id">
              <td>{{ i + 1 }}</td>
              <td class="fw-semibold">{{ c.name }}</td>
              <td>{{ c.email }}</td>
              <td>{{ c.phone }}</td>
              <td><span class="badge bg-info text-dark">{{ c.orders_count }} đơn</span></td>
              <td>
                <span v-if="c.is_block" class="badge bg-danger">Đã khoá</span>
                <span v-else-if="c.is_active" class="badge bg-success">Hoạt động</span>
                <span v-else class="badge bg-secondary">Chưa kích hoạt</span>
              </td>
              <td>
                <button @click="toggleBlock(c)" :class="c.is_block ? 'btn btn-sm btn-success' : 'btn btn-sm btn-danger'">
                  <i :class="c.is_block ? 'bi bi-unlock' : 'bi bi-lock'"></i>
                  {{ c.is_block ? 'Mở khoá' : 'Khoá' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import axiosClient from '@/services/axiosClient'

export default {
  name: 'AdminCustomers',
  data() { return { customers: [], loading: false, search: '' } },
  mounted() { this.fetchCustomers() },
  methods: {
    async fetchCustomers() {
      this.loading = true
      const res = await axiosClient.get('/admin/customers', { params: { search: this.search } })
      if (res.data.status === 1) this.customers = res.data.data.data
      this.loading = false
    },
    async toggleBlock(customer) {
      const action = customer.is_block ? 'mở khóa' : 'khóa'
      const confirmed = await this.$confirm(`Xác nhận <b>${action}</b> tài khoản <b>${customer.name}</b>?`, 'Xác Nhận Thao Tác', 'warning')
      if (!confirmed) return
      const res = await axiosClient.post('/admin/customers/toggle-block', { id: customer.id })
      if (res.data.status === 1) { this.$toast.success(res.data.message); customer.is_block = !customer.is_block }
      else this.$toast.error(res.data.message)
    },
  },
}
</script>
