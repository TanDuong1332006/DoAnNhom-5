<template>
    <div class="row">
        <!-- Nút mở Modal Thêm Mới & Thanh Tìm Kiếm -->
        <div class="col-12 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h5 class="mb-0 text-dark fw-bold text-uppercase fs-6">
                                <i class="bx bx-closet text-primary me-1"></i> Quản Lý Sản Phẩm ({{ list_san_pham_loc.length }})
                            </h5>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class='bx bx-search'></i></span>
                                <input v-model="key_search" type="text" class="form-control bg-light border-start-0" placeholder="Tìm theo tên sản phẩm, mã SP...">
                            </div>
                        </div>
                        <div class="col-md-3 text-end">
                            <button class="btn btn-primary btn-sm px-3 fw-semibold text-white" data-bs-toggle="modal" data-bs-target="#modalThemSP">
                                <i class='bx bx-plus-circle me-1'></i> Thêm Sản Phẩm Mới
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng Sản Phẩm -->
        <div class="col-12">
            <div class="card border-top border-3 border-primary shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0 align-middle text-nowrap">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th>STT</th>
                                    <th>Ảnh</th>
                                    <th>Mã SP</th>
                                    <th style="min-width: 220px;">Tên Sản Phẩm</th>
                                    <th>Danh Mục</th>
                                    <th>Thương Hiệu</th>
                                    <th>Giá Bán</th>
                                    <th>Giá KM</th>
                                    <th>Tồn Kho</th>
                                    <th>Nổi Bật</th>
                                    <th>Trạng Thái</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in list_san_pham_loc" :key="item.id">
                                    <td class="text-center">{{ index + 1 }}</td>
                                    <td class="text-center">
                                        <img :src="item.hinh_anh || 'https://via.placeholder.com/50'" class="rounded border" style="width: 45px; height: 45px; object-fit: cover;">
                                    </td>
                                    <td class="text-center font-monospace text-muted small">{{ item.ma_san_pham }}</td>
                                    <td class="fw-bold text-dark text-wrap" style="max-width: 260px;">
                                        {{ item.ten_san_pham }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill">
                                            {{ item.danh_muc ? item.danh_muc.ten_danh_muc : '---' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info-subtle text-info border rounded-pill">
                                            {{ item.thuong_hieu ? item.thuong_hieu.ten_thuong_hieu : '---' }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-semibold text-dark">{{ formatTien(item.gia_ban) }} đ</td>
                                    <td class="text-end fw-bold text-danger">
                                        {{ item.gia_khuyen_mai ? formatTien(item.gia_khuyen_mai) + ' đ' : '---' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success border rounded-pill">
                                            {{ item.so_luong_ton }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button v-on:click="changeNoiBat(item)" v-if="item.is_noi_bat == 1" class="btn btn-sm btn-warning py-0 px-2 text-dark fw-bold">
                                            <i class='bx bx-star'></i> Hot
                                        </button>
                                        <button v-on:click="changeNoiBat(item)" v-else class="btn btn-sm btn-outline-secondary py-0 px-2">
                                            Thường
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button v-on:click="changeStatus(item)" v-if="item.trang_thai == 1" class="btn btn-sm btn-success py-0 px-2">
                                            Mở bán
                                        </button>
                                        <button v-on:click="changeStatus(item)" v-else class="btn btn-sm btn-secondary py-0 px-2">
                                            Tạm ẩn
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button v-on:click="moModalCapNhat(item)" class="btn btn-primary btn-sm me-1" data-bs-toggle="modal" data-bs-target="#modalCapNhatSP">
                                            <i class='bx bx-edit-alt'></i> Sửa
                                        </button>
                                        <button v-on:click="Object.assign(delete_SanPham, item)" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalXoaSP">
                                            <i class='bx bx-trash'></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="list_san_pham_loc.length === 0">
                                    <td colspan="12" class="text-center py-4 text-muted">Không tìm thấy sản phẩm nào.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Thêm Mới Sản Phẩm -->
        <div class="modal fade" id="modalThemSP" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white">Thêm Mới Sản Phẩm</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Tên sản phẩm <span class="text-danger">*</span></label>
                                <input v-model="create_SanPham.ten_san_pham" type="text" class="form-control form-control-sm" placeholder="VD: Áo Thun Polo Nam Cotton...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Mã sản phẩm</label>
                                <input v-model="create_SanPham.ma_san_pham" type="text" class="form-control form-control-sm" placeholder="Để trống tự tạo mã">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Danh mục <span class="text-danger">*</span></label>
                                <select v-model="create_SanPham.id_danh_muc" class="form-select form-select-sm">
                                    <option value="">-- Chọn danh mục --</option>
                                    <option v-for="dm in list_danh_muc" :key="dm.id" :value="dm.id">{{ dm.ten_danh_muc }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Thương hiệu</label>
                                <select v-model="create_SanPham.id_thuong_hieu" class="form-select form-select-sm">
                                    <option value="">-- Không có thương hiệu --</option>
                                    <option v-for="th in list_thuong_hieu" :key="th.id" :value="th.id">{{ th.ten_thuong_hieu }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Giá bán (Gốc) <span class="text-danger">*</span></label>
                                <input v-model="create_SanPham.gia_ban" type="number" class="form-control form-control-sm" placeholder="VD: 350000">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Giá khuyến mãi</label>
                                <input v-model="create_SanPham.gia_khuyen_mai" type="number" class="form-control form-control-sm" placeholder="VD: 299000">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Số lượng tồn kho</label>
                                <input v-model="create_SanPham.so_luong_ton" type="number" class="form-control form-control-sm" placeholder="VD: 100">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Hình ảnh chính (URL)</label>
                                <input v-model="create_SanPham.hinh_anh" type="text" class="form-control form-control-sm" placeholder="https://images.unsplash.com/...">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Mô tả ngắn</label>
                                <input v-model="create_SanPham.mo_ta_ngan" type="text" class="form-control form-control-sm" placeholder="Tóm tắt chất liệu, kiểu dáng...">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Mô tả chi tiết</label>
                                <textarea v-model="create_SanPham.mo_ta_chi_tiet" class="form-control form-control-sm" rows="3" placeholder="Chi tiết sản phẩm, hướng dẫn giặt ủi..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-2">
                                    <input v-model="create_SanPham.is_noi_bat" class="form-check-input" type="checkbox" id="checkNoiBat" :true-value="1" :false-value="0">
                                    <label class="form-check-label fw-semibold" for="checkNoiBat">Đánh dấu Sản phẩm NỔI BẬT</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-2">
                                    <input v-model="create_SanPham.is_flash_sale" class="form-check-input" type="checkbox" id="checkFlashSale" :true-value="1" :false-value="0">
                                    <label class="form-check-label fw-semibold text-danger" for="checkFlashSale">Đánh dấu FLASH SALE</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                        <button v-on:click="themSanPham()" type="button" class="btn btn-primary btn-sm px-3" data-bs-dismiss="modal">Thêm Sản Phẩm</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Cập Nhật Sản Phẩm -->
        <div class="modal fade" id="modalCapNhatSP" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white">Cập Nhật Sản Phẩm</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Tên sản phẩm <span class="text-danger">*</span></label>
                                <input v-model="update_SanPham.ten_san_pham" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Mã sản phẩm</label>
                                <input v-model="update_SanPham.ma_san_pham" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Danh mục <span class="text-danger">*</span></label>
                                <select v-model="update_SanPham.id_danh_muc" class="form-select form-select-sm">
                                    <option v-for="dm in list_danh_muc" :key="dm.id" :value="dm.id">{{ dm.ten_danh_muc }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Thương hiệu</label>
                                <select v-model="update_SanPham.id_thuong_hieu" class="form-select form-select-sm">
                                    <option :value="null">-- Không có thương hiệu --</option>
                                    <option v-for="th in list_thuong_hieu" :key="th.id" :value="th.id">{{ th.ten_thuong_hieu }}</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Giá bán (Gốc) <span class="text-danger">*</span></label>
                                <input v-model="update_SanPham.gia_ban" type="number" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Giá khuyến mãi</label>
                                <input v-model="update_SanPham.gia_khuyen_mai" type="number" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Số lượng tồn kho</label>
                                <input v-model="update_SanPham.so_luong_ton" type="number" class="form-control form-control-sm">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Hình ảnh chính (URL)</label>
                                <input v-model="update_SanPham.hinh_anh" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Mô tả ngắn</label>
                                <input v-model="update_SanPham.mo_ta_ngan" type="text" class="form-control form-control-sm">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Mô tả chi tiết</label>
                                <textarea v-model="update_SanPham.mo_ta_chi_tiet" class="form-control form-control-sm" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                        <button v-on:click="capNhatSanPham()" type="button" class="btn btn-primary btn-sm px-3" data-bs-dismiss="modal">Cập nhật</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Xóa -->
        <div class="modal fade" id="modalXoaSP" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title text-white">Xác nhận xóa sản phẩm</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning border-0 mb-0">
                            Bạn có chắc chắn muốn xóa sản phẩm <strong>{{ delete_SanPham.ten_san_pham }}</strong> không?
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                        <button v-on:click="xoaSanPham()" type="button" class="btn btn-danger btn-sm px-3" data-bs-dismiss="modal">Xác nhận xóa</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'AdminSanPham',
    data() {
        return {
            key_search: '',
            list_san_pham: [],
            list_danh_muc: [],
            list_thuong_hieu: [],
            create_SanPham: {
                id_danh_muc: '',
                id_thuong_hieu: '',
                ma_san_pham: '',
                ten_san_pham: '',
                gia_ban: '',
                gia_khuyen_mai: '',
                hinh_anh: '',
                mo_ta_ngan: '',
                mo_ta_chi_tiet: '',
                so_luong_ton: 50,
                is_noi_bat: 0,
                is_flash_sale: 0,
                trang_thai: 1
            },
            update_SanPham: {
                id: '',
                id_danh_muc: '',
                id_thuong_hieu: '',
                ma_san_pham: '',
                ten_san_pham: '',
                gia_ban: '',
                gia_khuyen_mai: '',
                hinh_anh: '',
                mo_ta_ngan: '',
                mo_ta_chi_tiet: '',
                so_luong_ton: 0,
                is_noi_bat: 0,
                is_flash_sale: 0,
                trang_thai: 1
            },
            delete_SanPham: {
                id: '',
                ten_san_pham: ''
            }
        }
    },
    computed: {
        list_san_pham_loc() {
            if (!this.key_search) return this.list_san_pham;
            let kw = this.key_search.toLowerCase().trim();
            return this.list_san_pham.filter(i =>
                (i.ten_san_pham && i.ten_san_pham.toLowerCase().includes(kw)) ||
                (i.ma_san_pham && i.ma_san_pham.toLowerCase().includes(kw))
            );
        }
    },
    mounted() {
        this.loadData();
        this.loadDanhMucVaThuongHieu();
    },
    methods: {
        formatTien(val) {
            if (!val) return '0';
            return Number(val).toLocaleString('vi-VN');
        },
        loadData() {
            axios.get('http://127.0.0.1:8000/api/admin/san-pham/get-data')
                .then(res => {
                    this.list_san_pham = res.data.data;
                });
        },
        loadDanhMucVaThuongHieu() {
            axios.get('http://127.0.0.1:8000/api/admin/danh-muc/get-data')
                .then(res => { this.list_danh_muc = res.data.data; });
            axios.get('http://127.0.0.1:8000/api/admin/thuong-hieu/get-data')
                .then(res => { this.list_thuong_hieu = res.data.data; });
        },
        moModalCapNhat(item) {
            this.update_SanPham = Object.assign({}, item);
        },
        themSanPham() {
            if (!this.create_SanPham.ten_san_pham.trim() || !this.create_SanPham.id_danh_muc || !this.create_SanPham.gia_ban) {
                this.$toast.error('Vui lòng điền đủ Tên, Danh mục và Giá bán!');
                return;
            }
            axios.post('http://127.0.0.1:8000/api/admin/san-pham/create', this.create_SanPham)
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                        this.create_SanPham = {
                            id_danh_muc: '', id_thuong_hieu: '', ma_san_pham: '', ten_san_pham: '',
                            gia_ban: '', gia_khuyen_mai: '', hinh_anh: '', mo_ta_ngan: '', mo_ta_chi_tiet: '',
                            so_luong_ton: 50, is_noi_bat: 0, is_flash_sale: 0, trang_thai: 1
                        };
                    }
                })
                .catch(err => {
                    this.$toast.error('Lỗi khi thêm sản phẩm');
                });
        },
        capNhatSanPham() {
            axios.post('http://127.0.0.1:8000/api/admin/san-pham/update', this.update_SanPham)
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                    }
                });
        },
        xoaSanPham() {
            axios.post('http://127.0.0.1:8000/api/admin/san-pham/destroy', this.delete_SanPham)
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                    }
                });
        },
        changeStatus(item) {
            axios.post('http://127.0.0.1:8000/api/admin/san-pham/change-status', { id: item.id })
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                    }
                });
        },
        changeNoiBat(item) {
            axios.post('http://127.0.0.1:8000/api/admin/san-pham/change-noi-bat', { id: item.id })
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
