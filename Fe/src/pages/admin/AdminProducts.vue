<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="fw-bold mb-0"><i class="bi bi-box-seam me-2"></i>Quản Lý Sản Phẩm</h4>
      <button class="btn btn-warning" @click="openCreateModal"><i class="bi bi-plus-lg me-1"></i>Thêm Sản Phẩm</button>
    </div>

    <!-- Search/Filter -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <div class="row g-2">
          <div class="col-md-5">
            <input v-model="search" type="text" class="form-control" placeholder="Tìm sản phẩm..." @input="fetchProducts">
          </div>
          <div class="col-md-4">
            <select v-model="filterCat" class="form-select" @change="fetchProducts">
              <option value="">Tất cả danh mục</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
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
            <tr><th>Ảnh</th><th>Tên</th><th>Danh mục</th><th>Giá</th><th>Tồn kho</th><th>Trạng thái</th><th>Action</th></tr>
          </thead>
          <tbody>
            <tr v-for="p in products" :key="p.id">
              <td><img :src="p.image" width="50" height="50" class="rounded" style="object-fit:cover;"></td>
              <td class="fw-semibold" style="max-width:200px;">{{ p.name }}</td>
              <td><span class="badge bg-light text-dark border">{{ p.category?.name }}</span></td>
              <td>
                <div class="text-danger fw-bold">{{ formatPrice(p.sale_price || p.price) }}đ</div>
                <small v-if="p.sale_price" class="text-muted text-decoration-line-through">{{ formatPrice(p.price) }}đ</small>
              </td>
              <td><span :class="p.quantity <= 5 ? 'text-danger fw-bold' : ''">{{ p.quantity }}</span></td>
              <td><span :class="p.is_active ? 'badge bg-success' : 'badge bg-secondary'">{{ p.is_active ? 'Đang bán' : 'Ẩn' }}</span></td>
              <td>
                <button class="btn btn-sm btn-primary me-1" @click="openEditModal(p)"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-danger" @click="deleteProduct(p)"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <nav v-if="totalPages > 1" class="mt-3">
      <ul class="pagination">
        <li v-for="p in totalPages" :key="p" class="page-item" :class="{active: p === currentPage}">
          <a class="page-link" href="#" @click.prevent="fetchProducts(p)">{{ p }}</a>
        </li>
      </ul>
    </nav>

    <!-- Create/Edit Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title">{{ editMode ? 'Cập Nhật' : 'Thêm Mới' }} Sản Phẩm</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-12">
                <label class="form-label fw-semibold">Danh mục *</label>
                <select v-model="form.category_id" class="form-select">
                  <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
              <div class="col-md-12"><label class="form-label fw-semibold">Tên sản phẩm *</label>
                <input v-model="form.name" type="text" class="form-control" placeholder="Tên sản phẩm...">
              </div>
              <div class="col-md-12"><label class="form-label fw-semibold">Mô tả *</label>
                <textarea v-model="form.description" class="form-control" rows="2"></textarea>
              </div>
              <div class="col-md-6"><label class="form-label fw-semibold">Giá gốc *</label>
                <input v-model.number="form.price" type="number" class="form-control" min="0">
              </div>
              <div class="col-md-6"><label class="form-label fw-semibold">Giá khuyến mãi</label>
                <input v-model.number="form.sale_price" type="number" class="form-control" min="0" placeholder="Để trống nếu không có">
              </div>
              <div class="col-md-6"><label class="form-label fw-semibold">Số lượng tồn kho *</label>
                <input v-model.number="form.quantity" type="number" class="form-control" min="0">
              </div>
              <div class="col-md-6"><label class="form-label fw-semibold">URL Hình ảnh *</label>
                <input v-model="form.image" type="text" class="form-control" placeholder="https://...">
              </div>
              <div class="col-md-6">
                <div class="form-check form-switch mt-4">
                  <input v-model="form.is_featured" class="form-check-input" type="checkbox" id="isFeatured">
                  <label class="form-check-label" for="isFeatured">Sản phẩm nổi bật</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-check form-switch mt-4">
                  <input v-model="form.is_active" class="form-check-input" type="checkbox" id="isActive">
                  <label class="form-check-label" for="isActive">Đang bán</label>
                </div>
              </div>
              <div v-if="form.image" class="col-md-12">
                <img :src="form.image" height="100" class="rounded border">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
            <button @click="saveProduct" :disabled="saving" class="btn btn-warning">
              <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>{{ editMode ? 'Cập Nhật' : 'Thêm Mới' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axiosClient from '@/services/axiosClient'

export default {
  name: 'AdminProducts',
  data() {
    return {
      products: [], categories: [],
      loading: false, saving: false,
      search: '', filterCat: '',
      currentPage: 1, totalPages: 1,
      editMode: false,
      form: { id: null, category_id: '', name: '', description: '', price: 0, sale_price: null, quantity: 0, image: '', is_featured: false, is_active: true },
      modal: null,
    }
  },
  mounted() { this.fetchCategories(); this.fetchProducts() },
  methods: {
    async fetchCategories() {
      const res = await axiosClient.get('/admin/categories')
      if (res.data.status === 1) this.categories = res.data.data
    },
    async fetchProducts(page = 1) {
      this.loading = true; this.currentPage = page
      const res = await axiosClient.get('/admin/products', { params: { page, search: this.search, category_id: this.filterCat } })
      if (res.data.status === 1) { this.products = res.data.data.data; this.totalPages = res.data.data.last_page }
      this.loading = false
    },
    openCreateModal() {
      this.editMode = false
      this.form = { id: null, category_id: this.categories[0]?.id || '', name: '', description: '', price: 0, sale_price: null, quantity: 0, image: '', is_featured: false, is_active: true }
      this.showModal()
    },
    openEditModal(p) {
      this.editMode = true
      this.form = { id: p.id, category_id: p.category_id, name: p.name, description: p.description, price: p.price, sale_price: p.sale_price, quantity: p.quantity, image: p.image, is_featured: p.is_featured, is_active: p.is_active }
      this.showModal()
    },
    showModal() {
      if (!this.modal) this.modal = new bootstrap.Modal(document.getElementById('productModal'))
      this.modal.show()
    },
    async saveProduct() {
      this.saving = true
      const url = this.editMode ? '/admin/products/update' : '/admin/products/create'
      const data = { ...this.form, is_featured: this.form.is_featured ? 1 : 0, is_active: this.form.is_active ? 1 : 0 }
      if (!this.editMode) delete data.id
      try {
        const res = await axiosClient.post(url, data)
        if (res.data.status === 1) { this.$toast.success(res.data.message); this.modal?.hide(); this.fetchProducts() }
        else this.$toast.error(res.data.message)
      } catch (e) {
        if (e.response?.data?.errors) { const k = Object.keys(e.response.data.errors)[0]; this.$toast.error(e.response.data.errors[k][0]) }
        else this.$toast.error('Có lỗi!')
      }
      this.saving = false
    },
    async deleteProduct(p) {
      if (!confirm(`Xoá sản phẩm "${p.name}"?`)) return
      const res = await axiosClient.post('/admin/products/delete', { id: p.id })
      if (res.data.status === 1) { this.$toast.success('Đã xoá!'); this.fetchProducts() }
      else this.$toast.error(res.data.message)
    },
    formatPrice(p) { return new Intl.NumberFormat('vi-VN').format(p) },
  },
}
</script>
