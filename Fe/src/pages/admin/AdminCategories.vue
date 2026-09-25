<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="fw-bold mb-0"><i class="bi bi-tags me-2"></i>Quản Lý Danh Mục</h4>
      <button class="btn btn-warning" @click="openCreate"><i class="bi bi-plus-lg me-1"></i>Thêm Danh Mục</button>
    </div>
    <div class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-dark">
            <tr><th>#</th><th>Ảnh</th><th>Tên danh mục</th><th>Số SP</th><th>Trạng thái</th><th>Action</th></tr>
          </thead>
          <tbody>
            <tr v-for="(cat, i) in categories" :key="cat.id">
              <td>{{ i + 1 }}</td>
              <td><img :src="cat.image || 'https://via.placeholder.com/50'" width="50" height="50" class="rounded" style="object-fit:cover;"></td>
              <td class="fw-semibold">{{ cat.name }}</td>
              <td><span class="badge bg-light text-dark border">{{ cat.products_count }}</span></td>
              <td><span :class="cat.is_active ? 'badge bg-success' : 'badge bg-secondary'">{{ cat.is_active ? 'Hiển thị' : 'Ẩn' }}</span></td>
              <td>
                <button class="btn btn-sm btn-primary me-1" @click="openEdit(cat)"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-danger" @click="deleteCategory(cat)"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="catModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title">{{ editMode ? 'Cập nhật' : 'Thêm mới' }} danh mục</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3"><label class="form-label fw-semibold">Tên danh mục *</label>
              <input v-model="form.name" type="text" class="form-control">
            </div>
            <div class="mb-3"><label class="form-label fw-semibold">URL Ảnh</label>
              <input v-model="form.image" type="text" class="form-control" placeholder="https://...">
              <img v-if="form.image" :src="form.image" height="80" class="mt-2 rounded">
            </div>
            <div class="form-check form-switch">
              <input v-model="form.is_active" class="form-check-input" type="checkbox">
              <label class="form-check-label">Hiển thị</label>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
            <button @click="save" :disabled="saving" class="btn btn-warning">
              <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>Lưu
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
  name: 'AdminCategories',
  data() {
    return {
      categories: [], editMode: false, saving: false,
      form: { id: null, name: '', image: '', is_active: true },
      modal: null,
    }
  },
  mounted() { this.fetchCategories() },
  methods: {
    async fetchCategories() {
      const res = await axiosClient.get('/admin/categories')
      if (res.data.status === 1) this.categories = res.data.data
    },
    openCreate() { this.editMode = false; this.form = { id: null, name: '', image: '', is_active: true }; this.showModal() },
    openEdit(cat) { this.editMode = true; this.form = { id: cat.id, name: cat.name, image: cat.image||'', is_active: !!cat.is_active }; this.showModal() },
    showModal() { if (!this.modal) this.modal = new bootstrap.Modal(document.getElementById('catModal')); this.modal.show() },
    async save() {
      this.saving = true
      const url = this.editMode ? '/admin/categories/update' : '/admin/categories/create'
      const data = { ...this.form, is_active: this.form.is_active ? 1 : 0 }
      if (!this.editMode) delete data.id
      try {
        const res = await axiosClient.post(url, data)
        if (res.data.status === 1) { this.$toast.success(res.data.message); this.modal?.hide(); this.fetchCategories() }
        else this.$toast.error(res.data.message)
      } catch (e) {
        if (e.response?.data?.errors) { const k = Object.keys(e.response.data.errors)[0]; this.$toast.error(e.response.data.errors[k][0]) }
      }
      this.saving = false
    },
    async deleteCategory(cat) {
      const confirmed = await this.$confirmDelete(`Bạn có chắc muốn xóa danh mục <b>"${cat.name}"</b>?`);
      if (!confirmed) return;
      const res = await axiosClient.post('/admin/categories/delete', { id: cat.id })
      if (res.data.status === 1) { this.$toast.success('Đã xoá!'); this.fetchCategories() }
      else this.$toast.error(res.data.message)
    },
  },
}
</script>
