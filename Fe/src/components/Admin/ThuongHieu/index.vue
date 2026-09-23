<template>
    <div class="row">
        <!-- Cột Trái: Thêm Mới Thương Hiệu -->
        <div class="col-lg-4">
            <div class="card border-top border-3 border-primary shadow-sm">
                <div class="card-header bg-transparent">
                    <b class="text-uppercase text-dark">Thêm Mới Thương Hiệu</b>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Tên thương hiệu <span class="text-danger">*</span></label>
                        <input v-model="create_ThuongHieu.ten_thuong_hieu" type="text" class="form-control form-control-sm" placeholder="Nhập tên thương hiệu...">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Logo (URL)</label>
                        <input v-model="create_ThuongHieu.hinh_anh" type="text" class="form-control form-control-sm" placeholder="https://...">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Mô tả</label>
                        <textarea v-model="create_ThuongHieu.mo_ta" class="form-control form-control-sm" rows="3" placeholder="Nhập mô tả thương hiệu..."></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <select v-model="create_ThuongHieu.trang_thai" class="form-select form-select-sm">
                            <option :value="1">Hoạt động</option>
                            <option :value="0">Tạm tắt</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer bg-transparent text-end">
                    <button v-on:click="themThuongHieu()" type="button" class="btn btn-primary btn-sm px-3">
                        <i class='bx bx-plus-circle me-1'></i> Thêm Thương Hiệu
                    </button>
                </div>
            </div>
        </div>

        <!-- Cột Phải: Danh Sách Thương Hiệu -->
        <div class="col-lg-8">
            <div class="card border-top border-3 border-primary shadow-sm">
                <div class="card-header bg-transparent py-2">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <b class="text-uppercase text-dark">Danh Sách Thương Hiệu ({{ list_thuong_hieu_loc.length }})</b>
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
                                    <th>Logo</th>
                                    <th>Tên Thương Hiệu</th>
                                    <th>Số Sản Phẩm</th>
                                    <th>Trạng Thái</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in list_thuong_hieu_loc" :key="item.id">
                                    <td class="text-center align-middle">{{ index + 1 }}</td>
                                    <td class="text-center align-middle">
                                        <img :src="item.hinh_anh || 'https://via.placeholder.com/40'" class="rounded border p-1 bg-white" style="width: 45px; height: 35px; object-fit: contain;">
                                    </td>
                                    <td class="align-middle fw-bold text-dark">{{ item.ten_thuong_hieu }}</td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                                            {{ item.san_phams_count || 0 }} SP
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button v-on:click="changeStatus(item)" v-if="item.trang_thai == 1" class="btn btn-sm btn-success">
                                            Hoạt động
                                        </button>
                                        <button v-on:click="changeStatus(item)" v-else class="btn btn-sm btn-secondary">
                                            Tạm tắt
                                        </button>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button v-on:click="Object.assign(update_ThuongHieu, item)" class="btn btn-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#modalCapNhatTH">
                                            <i class='bx bx-edit-alt'></i> Sửa
                                        </button>
                                        <button v-on:click="Object.assign(delete_ThuongHieu, item)" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalXoaTH">
                                            <i class='bx bx-trash'></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="list_thuong_hieu_loc.length === 0">
                                    <td colspan="6" class="text-center py-4 text-muted">Không tìm thấy thương hiệu nào.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Cập Nhật -->
        <div class="modal fade" id="modalCapNhatTH" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white">Cập nhật thương hiệu</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Tên thương hiệu <span class="text-danger">*</span></label>
                            <input v-model="update_ThuongHieu.ten_thuong_hieu" type="text" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Logo (URL)</label>
                            <input v-model="update_ThuongHieu.hinh_anh" type="text" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Mô tả</label>
                            <textarea v-model="update_ThuongHieu.mo_ta" class="form-control form-control-sm" rows="3"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select v-model="update_ThuongHieu.trang_thai" class="form-select form-select-sm">
                                <option :value="1">Hoạt động</option>
                                <option :value="0">Tạm tắt</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                        <button v-on:click="capNhatThuongHieu()" type="button" class="btn btn-primary btn-sm px-3" data-bs-dismiss="modal">Cập nhật</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Xóa -->
        <div class="modal fade" id="modalXoaTH" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white">Xác nhận xóa thương hiệu</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning border-0 mb-0" role="alert">
                            <i class='bx bx-error-circle me-1 fs-5 align-middle'></i>
                            Bạn có chắc chắn muốn xóa thương hiệu <strong>{{ delete_ThuongHieu.ten_thuong_hieu }}</strong> không?
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                        <button v-on:click="xoaThuongHieu()" type="button" class="btn btn-danger btn-sm px-3" data-bs-dismiss="modal">Xác nhận xóa</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'AdminThuongHieu',
    data() {
        return {
            key_search: '',
            list_thuong_hieu: [],
            create_ThuongHieu: {
                ten_thuong_hieu: '',
                hinh_anh: '',
                mo_ta: '',
                trang_thai: 1
            },
            update_ThuongHieu: {
                id: '',
                ten_thuong_hieu: '',
                hinh_anh: '',
                mo_ta: '',
                trang_thai: 1
            },
            delete_ThuongHieu: {
                id: '',
                ten_thuong_hieu: '',
            },
        }
    },
    computed: {
        list_thuong_hieu_loc() {
            if (!this.key_search) {
                return this.list_thuong_hieu;
            }
            let kw = this.key_search.toLowerCase().trim();
            return this.list_thuong_hieu.filter((item) =>
                (item.ten_thuong_hieu && String(item.ten_thuong_hieu).toLowerCase().includes(kw)) ||
                (item.slug_thuong_hieu && String(item.slug_thuong_hieu).toLowerCase().includes(kw))
            );
        }
    },
    mounted() {
        this.loadData();
    },
    methods: {
        loadData() {
            axios.get('http://127.0.0.1:8000/api/admin/thuong-hieu/get-data')
                .then(response => {
                    this.list_thuong_hieu = response.data.data;
                })
                .catch(error => {
                    console.error(error);
                });
        },
        themThuongHieu() {
            if (!this.create_ThuongHieu.ten_thuong_hieu.trim()) {
                this.$toast.error('Vui lòng nhập tên thương hiệu');
                return;
            }
            axios.post('http://127.0.0.1:8000/api/admin/thuong-hieu/create', this.create_ThuongHieu)
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                        this.create_ThuongHieu = { ten_thuong_hieu: '', hinh_anh: '', mo_ta: '', trang_thai: 1 };
                    } else {
                        this.$toast.error(res.data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    this.$toast.error('Đã xảy ra lỗi khi thêm thương hiệu.');
                });
        },
        capNhatThuongHieu() {
            axios.post('http://127.0.0.1:8000/api/admin/thuong-hieu/update', this.update_ThuongHieu)
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
        xoaThuongHieu() {
            axios.post('http://127.0.0.1:8000/api/admin/thuong-hieu/destroy', this.delete_ThuongHieu)
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
            axios.post('http://127.0.0.1:8000/api/admin/thuong-hieu/change-status', { id: item.id })
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
