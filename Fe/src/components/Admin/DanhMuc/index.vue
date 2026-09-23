<template>
    <div class="row">
        <!-- Cột Trái: Thêm Mới Danh Mục -->
        <div class="col-lg-4">
            <div class="card border-top border-3 border-primary shadow-sm">
                <div class="card-header bg-transparent">
                    <b class="text-uppercase text-dark">Thêm Mới Danh Mục</b>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                        <input v-model="create_DanhMuc.ten_danh_muc" type="text" class="form-control form-control-sm" placeholder="Nhập tên danh mục...">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Hình ảnh (URL)</label>
                        <input v-model="create_DanhMuc.hinh_anh" type="text" class="form-control form-control-sm" placeholder="https://...">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Mô tả</label>
                        <textarea v-model="create_DanhMuc.mo_ta" class="form-control form-control-sm" rows="3" placeholder="Nhập mô tả danh mục..."></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <select v-model="create_DanhMuc.trang_thai" class="form-select form-select-sm">
                            <option :value="1">Hiển thị</option>
                            <option :value="0">Tạm tắt</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer bg-transparent text-end">
                    <button v-on:click="themDanhMuc()" type="button" class="btn btn-primary btn-sm px-3">
                        <i class='bx bx-plus-circle me-1'></i> Thêm Danh Mục
                    </button>
                </div>
            </div>
        </div>

        <!-- Cột Phải: Danh Sách Danh Mục -->
        <div class="col-lg-8">
            <div class="card border-top border-3 border-primary shadow-sm">
                <div class="card-header bg-transparent py-2">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <b class="text-uppercase text-dark">Danh Sách Danh Mục ({{ list_danh_muc_loc.length }})</b>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="input-group input-group-sm ms-auto" style="max-width: 260px;">
                                <span class="input-group-text bg-light border-end-0 pe-0 rounded-start-pill text-muted">
                                    <i class='bx bx-search fs-6'></i>
                                </span>
                                <input type="text" v-model="key_search" class="form-control bg-light border-start-0 rounded-end-pill ps-2 shadow-none" placeholder="Tìm kiếm nhanh...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0 text-nowrap">
                            <thead class="table-primary text-center align-middle">
                                <tr>
                                    <th>STT</th>
                                    <th>Ảnh</th>
                                    <th>Tên Danh Mục</th>
                                    <th>Slug</th>
                                    <th>Số Sản Phẩm</th>
                                    <th>Trạng Thái</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in list_danh_muc_loc" :key="item.id">
                                    <td class="text-center align-middle">{{ index + 1 }}</td>
                                    <td class="text-center align-middle">
                                        <img :src="item.hinh_anh || 'https://via.placeholder.com/40'" class="rounded border" style="width: 40px; height: 40px; object-fit: cover;">
                                    </td>
                                    <td class="align-middle fw-bold text-dark">{{ item.ten_danh_muc }}</td>
                                    <td class="align-middle text-muted small">{{ item.slug_danh_muc }}</td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">
                                            {{ item.san_phams_count || 0 }} SP
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button v-on:click="changeStatus(item)" v-if="item.trang_thai == 1" class="btn btn-sm btn-success">
                                            Hiển thị
                                        </button>
                                        <button v-on:click="changeStatus(item)" v-else class="btn btn-sm btn-secondary">
                                            Tạm tắt
                                        </button>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button v-on:click="Object.assign(update_DanhMuc, item)" class="btn btn-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#modalCapNhat">
                                            <i class='bx bx-edit-alt'></i> Sửa
                                        </button>
                                        <button v-on:click="Object.assign(delete_DanhMuc, item)" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalXoa">
                                            <i class='bx bx-trash'></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="list_danh_muc_loc.length === 0">
                                    <td colspan="7" class="text-center py-4 text-muted">Không tìm thấy danh mục nào.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Cập Nhật Danh Mục -->
        <div class="modal fade" id="modalCapNhat" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white">Cập nhật danh mục</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Tên danh mục <span class="text-danger">*</span></label>
                            <input v-model="update_DanhMuc.ten_danh_muc" type="text" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Hình ảnh (URL)</label>
                            <input v-model="update_DanhMuc.hinh_anh" type="text" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Mô tả</label>
                            <textarea v-model="update_DanhMuc.mo_ta" class="form-control form-control-sm" rows="3"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select v-model="update_DanhMuc.trang_thai" class="form-select form-select-sm">
                                <option :value="1">Hiển thị</option>
                                <option :value="0">Tạm tắt</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                        <button v-on:click="capNhatDanhMuc()" type="button" class="btn btn-primary btn-sm px-3" data-bs-dismiss="modal">Cập nhật</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Xóa -->
        <div class="modal fade" id="modalXoa" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white">Xác nhận xóa danh mục</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning border-0 mb-0" role="alert">
                            <i class='bx bx-error-circle me-1 fs-5 align-middle'></i>
                            Bạn có chắc chắn muốn xóa danh mục <strong>{{ delete_DanhMuc.ten_danh_muc }}</strong> không? Hành động này sẽ xóa toàn bộ sản phẩm thuộc danh mục!
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                        <button v-on:click="xoaDanhMuc()" type="button" class="btn btn-danger btn-sm px-3" data-bs-dismiss="modal">Xác nhận xóa</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'AdminDanhMuc',
    data() {
        return {
            key_search: '',
            list_danh_muc: [],
            create_DanhMuc: {
                ten_danh_muc: '',
                hinh_anh: '',
                mo_ta: '',
                trang_thai: 1
            },
            update_DanhMuc: {
                id: '',
                ten_danh_muc: '',
                hinh_anh: '',
                mo_ta: '',
                trang_thai: 1
            },
            delete_DanhMuc: {
                id: '',
                ten_danh_muc: '',
            },
        }
    },
    computed: {
        list_danh_muc_loc() {
            if (!this.key_search) {
                return this.list_danh_muc;
            }
            let kw = this.key_search.toLowerCase().trim();
            return this.list_danh_muc.filter((item) =>
                (item.ten_danh_muc && String(item.ten_danh_muc).toLowerCase().includes(kw)) ||
                (item.slug_danh_muc && String(item.slug_danh_muc).toLowerCase().includes(kw))
            );
        }
    },
    mounted() {
        this.loadData();
    },
    methods: {
        loadData() {
            axios.get('http://127.0.0.1:8000/api/admin/danh-muc/get-data')
                .then(response => {
                    this.list_danh_muc = response.data.data;
                })
                .catch(error => {
                    console.error(error);
                });
        },
        themDanhMuc() {
            if (!this.create_DanhMuc.ten_danh_muc.trim()) {
                this.$toast.error('Vui lòng nhập tên danh mục');
                return;
            }
            axios.post('http://127.0.0.1:8000/api/admin/danh-muc/create', this.create_DanhMuc)
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                        this.create_DanhMuc = { ten_danh_muc: '', hinh_anh: '', mo_ta: '', trang_thai: 1 };
                    } else {
                        this.$toast.error(res.data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    this.$toast.error('Đã xảy ra lỗi khi thêm danh mục.');
                });
        },
        capNhatDanhMuc() {
            axios.post('http://127.0.0.1:8000/api/admin/danh-muc/update', this.update_DanhMuc)
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                    } else {
                        this.$toast.error(res.data.message || "Cập nhật thất bại");
                    }
                })
                .catch(error => {
                    console.error(error);
                    this.$toast.error("Có lỗi xảy ra khi cập nhật!");
                });
        },
        xoaDanhMuc() {
            axios.post('http://127.0.0.1:8000/api/admin/danh-muc/destroy', this.delete_DanhMuc)
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                    } else {
                        this.$toast.error(res.data.message || "Xóa thất bại");
                    }
                })
                .catch(error => {
                    console.error(error);
                    this.$toast.error("Có lỗi xảy ra khi xóa!");
                });
        },
        changeStatus(item) {
            axios.post('http://127.0.0.1:8000/api/admin/danh-muc/change-status', { id: item.id })
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                    } else {
                        this.$toast.error(res.data.message || "Đổi trạng thái thất bại");
                    }
                })
                .catch(error => {
                    console.error(error);
                    this.$toast.error("Có lỗi xảy ra!");
                });
        }
    }
}
</script>

<style></style>
