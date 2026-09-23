<template>
    <div class="row">
        <!-- Cột Trái: Thêm Mã Giảm Giá -->
        <div class="col-lg-4">
            <div class="card border-top border-3 border-primary shadow-sm">
                <div class="card-header bg-transparent">
                    <b class="text-uppercase text-dark">Thêm Mã Giảm Giá</b>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Mã code <span class="text-danger">*</span></label>
                        <input v-model="create_MaGiamGia.ma_code" type="text" class="form-control form-control-sm text-uppercase font-monospace" placeholder="VD: SALE20, FREESHIP...">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Tên chương trình <span class="text-danger">*</span></label>
                        <input v-model="create_MaGiamGia.ten_giam_gia" type="text" class="form-control form-control-sm" placeholder="VD: Giảm 20% đơn từ 500k">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Loại giảm</label>
                            <select v-model="create_MaGiamGia.loai_giam" class="form-select form-select-sm">
                                <option value="phan_tram">Phần trăm (%)</option>
                                <option value="co_dinh">Số tiền cố định (VNĐ)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Giá trị giảm <span class="text-danger">*</span></label>
                            <input v-model="create_MaGiamGia.gia_tri" type="number" class="form-control form-control-sm" placeholder="VD: 10 hoặc 50000">
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Đơn tối thiểu</label>
                            <input v-model="create_MaGiamGia.gia_tri_toi_thieu" type="number" class="form-control form-control-sm" placeholder="VD: 200000">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Giảm tối đa</label>
                            <input v-model="create_MaGiamGia.giam_toi_da" type="number" class="form-control form-control-sm" placeholder="VD: 100000">
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Số lượng mã</label>
                            <input v-model="create_MaGiamGia.so_luong" type="number" class="form-control form-control-sm" placeholder="100">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select v-model="create_MaGiamGia.trang_thai" class="form-select form-select-sm">
                                <option :value="1">Kích hoạt</option>
                                <option :value="0">Tạm tắt</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent text-end">
                    <button v-on:click="themMGG()" type="button" class="btn btn-primary btn-sm px-3">
                        <i class='bx bx-plus-circle me-1'></i> Thêm Coupon
                    </button>
                </div>
            </div>
        </div>

        <!-- Cột Phải: Danh Sách Mã Giảm Giá -->
        <div class="col-lg-8">
            <div class="card border-top border-3 border-primary shadow-sm">
                <div class="card-header bg-transparent py-2">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <b class="text-uppercase text-dark">Danh Sách Mã Giảm Giá ({{ list_mgg_loc.length }})</b>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="input-group input-group-sm ms-auto" style="max-width: 260px;">
                                <span class="input-group-text bg-light border-end-0 pe-0 rounded-start-pill text-muted">
                                    <i class='bx bx-search fs-6'></i>
                                </span>
                                <input type="text" v-model="key_search" class="form-control bg-light border-start-0 rounded-end-pill ps-2 shadow-none" placeholder="Tìm mã code...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0 text-nowrap align-middle">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th>STT</th>
                                    <th>Mã Code</th>
                                    <th>Tên Giảm Giá</th>
                                    <th>Mức Giảm</th>
                                    <th>Đã Dùng / Tổng</th>
                                    <th>Trạng Thái</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in list_mgg_loc" :key="item.id">
                                    <td class="text-center">{{ index + 1 }}</td>
                                    <td class="text-center fw-bold text-danger font-monospace fs-6">{{ item.ma_code }}</td>
                                    <td class="text-wrap" style="max-width: 200px;">{{ item.ten_giam_gia }}</td>
                                    <td class="text-center fw-bold text-success">
                                        {{ item.loai_giam === 'phan_tram' ? item.gia_tri + '%' : Number(item.gia_tri).toLocaleString('vi-VN') + ' đ' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill">
                                            {{ item.so_luong_da_dung }} / {{ item.so_luong }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button v-on:click="changeStatus(item)" v-if="item.trang_thai == 1" class="btn btn-sm btn-success py-0 px-2">
                                            Kích hoạt
                                        </button>
                                        <button v-on:click="changeStatus(item)" v-else class="btn btn-sm btn-secondary py-0 px-2">
                                            Tạm tắt
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button v-on:click="xoaMGG(item)" class="btn btn-danger btn-sm">
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
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'AdminMaGiamGia',
    data() {
        return {
            key_search: '',
            list_mgg: [],
            create_MaGiamGia: {
                ma_code: '',
                ten_giam_gia: '',
                loai_giam: 'phan_tram',
                gia_tri: '',
                gia_tri_toi_thieu: 0,
                giam_toi_da: null,
                so_luong: 100,
                trang_thai: 1
            }
        }
    },
    computed: {
        list_mgg_loc() {
            if (!this.key_search) return this.list_mgg;
            let kw = this.key_search.toLowerCase().trim();
            return this.list_mgg.filter(i =>
                (i.ma_code && i.ma_code.toLowerCase().includes(kw)) ||
                (i.ten_giam_gia && i.ten_giam_gia.toLowerCase().includes(kw))
            );
        }
    },
    mounted() {
        this.loadData();
    },
    methods: {
        loadData() {
            axios.get('http://127.0.0.1:8000/api/admin/ma-giam-gia/get-data')
                .then(res => { this.list_mgg = res.data.data; });
        },
        themMGG() {
            if (!this.create_MaGiamGia.ma_code.trim() || !this.create_MaGiamGia.gia_tri) {
                this.$toast.error('Vui lòng nhập Mã code và Giá trị giảm');
                return;
            }
            axios.post('http://127.0.0.1:8000/api/admin/ma-giam-gia/create', this.create_MaGiamGia)
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                        this.create_MaGiamGia = {
                            ma_code: '', ten_giam_gia: '', loai_giam: 'phan_tram',
                            gia_tri: '', gia_tri_toi_thieu: 0, giam_toi_da: null,
                            so_luong: 100, trang_thai: 1
                        };
                    }
                });
        },
        xoaMGG(item) {
            if (confirm('Bạn có chắc muốn xóa mã giảm giá này?')) {
                axios.post('http://127.0.0.1:8000/api/admin/ma-giam-gia/destroy', { id: item.id })
                    .then(res => {
                        if (res.data.status) {
                            this.$toast.success(res.data.message);
                            this.loadData();
                        }
                    });
            }
        },
        changeStatus(item) {
            axios.post('http://127.0.0.1:8000/api/admin/ma-giam-gia/change-status', { id: item.id })
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
