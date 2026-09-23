<template>
    <div class="row">
        <!-- Cột Trái: Thêm Mới Màu Sắc -->
        <div class="col-lg-4">
            <div class="card border-top border-3 border-primary shadow-sm">
                <div class="card-header bg-transparent">
                    <b class="text-uppercase text-dark">Thêm Mới Màu Sắc</b>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Tên màu <span class="text-danger">*</span></label>
                        <input v-model="create_MauSac.ten_mau" type="text" class="form-control form-control-sm" placeholder="VD: Đen, Trắng, Đỏ đô...">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Mã màu (Hex Code)</label>
                        <div class="input-group input-group-sm">
                            <input v-model="create_MauSac.ma_mau" type="color" class="form-control form-control-color" style="max-width: 50px;">
                            <input v-model="create_MauSac.ma_mau" type="text" class="form-control" placeholder="#000000">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <select v-model="create_MauSac.trang_thai" class="form-select form-select-sm">
                            <option :value="1">Hoạt động</option>
                            <option :value="0">Tạm tắt</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer bg-transparent text-end">
                    <button v-on:click="themMauSac()" type="button" class="btn btn-primary btn-sm px-3">
                        <i class='bx bx-plus-circle me-1'></i> Thêm Màu
                    </button>
                </div>
            </div>
        </div>

        <!-- Cột Phải: Danh Sách Màu Sắc -->
        <div class="col-lg-8">
            <div class="card border-top border-3 border-primary shadow-sm">
                <div class="card-header bg-transparent py-2">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <b class="text-uppercase text-dark">Danh Sách Màu Sắc ({{ list_mau_sac_loc.length }})</b>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="input-group input-group-sm ms-auto" style="max-width: 260px;">
                                <span class="input-group-text bg-light border-end-0 pe-0 rounded-start-pill text-muted">
                                    <i class='bx bx-search fs-6'></i>
                                </span>
                                <input type="text" v-model="key_search" class="form-control bg-light border-start-0 rounded-end-pill ps-2 shadow-none" placeholder="Tìm tên màu...">
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
                                    <th>Mẫu Màu</th>
                                    <th>Tên Màu</th>
                                    <th>Mã Hex</th>
                                    <th>Trạng Thái</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in list_mau_sac_loc" :key="item.id">
                                    <td class="text-center align-middle">{{ index + 1 }}</td>
                                    <td class="text-center align-middle">
                                        <span class="d-inline-block rounded-circle border shadow-sm"
                                            :style="{ backgroundColor: item.ma_mau || '#ccc', width: '26px', height: '26px' }">
                                        </span>
                                    </td>
                                    <td class="align-middle fw-bold text-dark">{{ item.ten_mau }}</td>
                                    <td class="text-center align-middle font-monospace">{{ item.ma_mau }}</td>
                                    <td class="text-center align-middle">
                                        <button v-on:click="changeStatus(item)" v-if="item.trang_thai == 1" class="btn btn-sm btn-success">
                                            Hoạt động
                                        </button>
                                        <button v-on:click="changeStatus(item)" v-else class="btn btn-sm btn-secondary">
                                            Tạm tắt
                                        </button>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button v-on:click="Object.assign(update_MauSac, item)" class="btn btn-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#modalCapNhatMS">
                                            <i class='bx bx-edit-alt'></i> Sửa
                                        </button>
                                        <button v-on:click="Object.assign(delete_MauSac, item)" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalXoaMS">
                                            <i class='bx bx-trash'></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Cập Nhật -->
        <div class="modal fade" id="modalCapNhatMS" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white">Cập nhật màu sắc</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Tên màu <span class="text-danger">*</span></label>
                            <input v-model="update_MauSac.ten_mau" type="text" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Mã màu</label>
                            <div class="input-group input-group-sm">
                                <input v-model="update_MauSac.ma_mau" type="color" class="form-control form-control-color" style="max-width: 50px;">
                                <input v-model="update_MauSac.ma_mau" type="text" class="form-control">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select v-model="update_MauSac.trang_thai" class="form-select form-select-sm">
                                <option :value="1">Hoạt động</option>
                                <option :value="0">Tạm tắt</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                        <button v-on:click="capNhatMauSac()" type="button" class="btn btn-primary btn-sm px-3" data-bs-dismiss="modal">Cập nhật</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Xóa -->
        <div class="modal fade" id="modalXoaMS" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white">Xác nhận xóa màu</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning border-0 mb-0">
                            Bạn có chắc chắn muốn xóa màu <strong>{{ delete_MauSac.ten_mau }}</strong> không?
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                        <button v-on:click="xoaMauSac()" type="button" class="btn btn-danger btn-sm px-3" data-bs-dismiss="modal">Xóa</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'AdminMauSac',
    data() {
        return {
            key_search: '',
            list_mau_sac: [],
            create_MauSac: { ten_mau: '', ma_mau: '#000000', trang_thai: 1 },
            update_MauSac: { id: '', ten_mau: '', ma_mau: '', trang_thai: 1 },
            delete_MauSac: { id: '', ten_mau: '' },
        }
    },
    computed: {
        list_mau_sac_loc() {
            if (!this.key_search) return this.list_mau_sac;
            let kw = this.key_search.toLowerCase().trim();
            return this.list_mau_sac.filter(i => i.ten_mau && i.ten_mau.toLowerCase().includes(kw));
        }
    },
    mounted() {
        this.loadData();
    },
    methods: {
        loadData() {
            axios.get('http://127.0.0.1:8000/api/admin/mau-sac/get-data')
                .then(res => { this.list_mau_sac = res.data.data; });
        },
        themMauSac() {
            if (!this.create_MauSac.ten_mau.trim()) {
                this.$toast.error('Vui lòng nhập tên màu');
                return;
            }
            axios.post('http://127.0.0.1:8000/api/admin/mau-sac/create', this.create_MauSac)
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                        this.create_MauSac = { ten_mau: '', ma_mau: '#000000', trang_thai: 1 };
                    }
                });
        },
        capNhatMauSac() {
            axios.post('http://127.0.0.1:8000/api/admin/mau-sac/update', this.update_MauSac)
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                    }
                });
        },
        xoaMauSac() {
            axios.post('http://127.0.0.1:8000/api/admin/mau-sac/destroy', this.delete_MauSac)
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                    }
                });
        },
        changeStatus(item) {
            axios.post('http://127.0.0.1:8000/api/admin/mau-sac/change-status', { id: item.id })
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                    }
                });
        }
    }
}
</script>
