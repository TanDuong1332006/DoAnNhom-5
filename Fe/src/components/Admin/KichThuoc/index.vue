<template>
    <div class="row">
        <!-- Cột Trái: Thêm Mới Kích Thước -->
        <div class="col-lg-4">
            <div class="card border-top border-3 border-primary shadow-sm">
                <div class="card-header bg-transparent">
                    <b class="text-uppercase text-dark">Thêm Mới Kích Thước</b>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Tên size <span class="text-danger">*</span></label>
                        <input v-model="create_KichThuoc.ten_kich_thuoc" type="text" class="form-control form-control-sm" placeholder="VD: S, M, L, XL, 38, 39...">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Gợi ý / Mô tả</label>
                        <textarea v-model="create_KichThuoc.mo_ta" class="form-control form-control-sm" rows="3" placeholder="VD: Phù hợp chiều cao 1m60-1m68, 50-60kg"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <select v-model="create_KichThuoc.trang_thai" class="form-select form-select-sm">
                            <option :value="1">Hoạt động</option>
                            <option :value="0">Tạm tắt</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer bg-transparent text-end">
                    <button v-on:click="themKichThuoc()" type="button" class="btn btn-primary btn-sm px-3">
                        <i class='bx bx-plus-circle me-1'></i> Thêm Size
                    </button>
                </div>
            </div>
        </div>

        <!-- Cột Phải: Danh Sách Kích Thước -->
        <div class="col-lg-8">
            <div class="card border-top border-3 border-primary shadow-sm">
                <div class="card-header bg-transparent py-2">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <b class="text-uppercase text-dark">Danh Sách Kích Thước ({{ list_kich_thuoc_loc.length }})</b>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="input-group input-group-sm ms-auto" style="max-width: 260px;">
                                <span class="input-group-text bg-light border-end-0 pe-0 rounded-start-pill text-muted">
                                    <i class='bx bx-search fs-6'></i>
                                </span>
                                <input type="text" v-model="key_search" class="form-control bg-light border-start-0 rounded-end-pill ps-2 shadow-none" placeholder="Tìm size...">
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
                                    <th>Tên Size</th>
                                    <th>Gợi Ý Thông Số</th>
                                    <th>Trạng Thái</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in list_kich_thuoc_loc" :key="item.id">
                                    <td class="text-center align-middle">{{ index + 1 }}</td>
                                    <td class="text-center align-middle fw-bold text-primary fs-6">{{ item.ten_kich_thuoc }}</td>
                                    <td class="align-middle text-muted">{{ item.mo_ta || '---' }}</td>
                                    <td class="text-center align-middle">
                                        <button v-on:click="changeStatus(item)" v-if="item.trang_thai == 1" class="btn btn-sm btn-success">
                                            Hoạt động
                                        </button>
                                        <button v-on:click="changeStatus(item)" v-else class="btn btn-sm btn-secondary">
                                            Tạm tắt
                                        </button>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button v-on:click="Object.assign(update_KichThuoc, item)" class="btn btn-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#modalCapNhatKT">
                                            <i class='bx bx-edit-alt'></i> Sửa
                                        </button>
                                        <button v-on:click="Object.assign(delete_KichThuoc, item)" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalXoaKT">
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
        <div class="modal fade" id="modalCapNhatKT" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white">Cập nhật kích thước</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Tên size <span class="text-danger">*</span></label>
                            <input v-model="update_KichThuoc.ten_kich_thuoc" type="text" class="form-control form-control-sm">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Gợi ý thông số</label>
                            <textarea v-model="update_KichThuoc.mo_ta" class="form-control form-control-sm" rows="3"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select v-model="update_KichThuoc.trang_thai" class="form-select form-select-sm">
                                <option :value="1">Hoạt động</option>
                                <option :value="0">Tạm tắt</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                        <button v-on:click="capNhatKichThuoc()" type="button" class="btn btn-primary btn-sm px-3" data-bs-dismiss="modal">Cập nhật</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Xóa -->
        <div class="modal fade" id="modalXoaKT" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white">Xác nhận xóa size</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning border-0 mb-0">
                            Bạn có chắc chắn muốn xóa size <strong>{{ delete_KichThuoc.ten_kich_thuoc }}</strong> không?
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                        <button v-on:click="xoaKichThuoc()" type="button" class="btn btn-danger btn-sm px-3" data-bs-dismiss="modal">Xóa</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'AdminKichThuoc',
    data() {
        return {
            key_search: '',
            list_kich_thuoc: [],
            create_KichThuoc: { ten_kich_thuoc: '', mo_ta: '', trang_thai: 1 },
            update_KichThuoc: { id: '', ten_kich_thuoc: '', mo_ta: '', trang_thai: 1 },
            delete_KichThuoc: { id: '', ten_kich_thuoc: '' },
        }
    },
    computed: {
        list_kich_thuoc_loc() {
            if (!this.key_search) return this.list_kich_thuoc;
            let kw = this.key_search.toLowerCase().trim();
            return this.list_kich_thuoc.filter(i => i.ten_kich_thuoc && i.ten_kich_thuoc.toLowerCase().includes(kw));
        }
    },
    mounted() {
        this.loadData();
    },
    methods: {
        loadData() {
            axios.get('http://127.0.0.1:8000/api/admin/kich-thuoc/get-data')
                .then(res => { this.list_kich_thuoc = res.data.data; });
        },
        themKichThuoc() {
            if (!this.create_KichThuoc.ten_kich_thuoc.trim()) {
                this.$toast.error('Vui lòng nhập tên size');
                return;
            }
            axios.post('http://127.0.0.1:8000/api/admin/kich-thuoc/create', this.create_KichThuoc)
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                        this.create_KichThuoc = { ten_kich_thuoc: '', mo_ta: '', trang_thai: 1 };
                    }
                });
        },
        capNhatKichThuoc() {
            axios.post('http://127.0.0.1:8000/api/admin/kich-thuoc/update', this.update_KichThuoc)
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                    }
                });
        },
        xoaKichThuoc() {
            axios.post('http://127.0.0.1:8000/api/admin/kich-thuoc/destroy', this.delete_KichThuoc)
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                    }
                });
        },
        changeStatus(item) {
            axios.post('http://127.0.0.1:8000/api/admin/kich-thuoc/change-status', { id: item.id })
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
