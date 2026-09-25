<template>
    <div class="row">
        <!-- Cột Trái: Thêm Biến Thể -->
        <div class="col-lg-4">
            <div class="card border-top border-3 border-primary shadow-sm">
                <div class="card-header bg-transparent">
                    <b class="text-uppercase text-dark">Thêm Biến Thể Tồn Kho</b>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Sản phẩm <span class="text-danger">*</span></label>
                        <select v-model="create_BienThe.id_san_pham" class="form-select form-select-sm">
                            <option value="">-- Chọn sản phẩm --</option>
                            <option v-for="sp in list_san_pham" :key="sp.id" :value="sp.id">{{ sp.ten_san_pham }}</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Màu sắc</label>
                        <select v-model="create_BienThe.id_mau_sac" class="form-select form-select-sm">
                            <option value="">-- Không chọn màu --</option>
                            <option v-for="ms in list_mau_sac" :key="ms.id" :value="ms.id">{{ ms.ten_mau }}</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Kích thước (Size)</label>
                        <select v-model="create_BienThe.id_kich_thuoc" class="form-select form-select-sm">
                            <option value="">-- Không chọn size --</option>
                            <option v-for="kt in list_kich_thuoc" :key="kt.id" :value="kt.id">{{ kt.ten_kich_thuoc }}</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Số lượng tồn kho <span class="text-danger">*</span></label>
                        <input v-model="create_BienThe.so_luong_ton" type="number" min="0" class="form-control form-control-sm" placeholder="VD: 50">
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold">Giá chênh lệch (+- VND)</label>
                        <input v-model="create_BienThe.gia_chenh_lech" type="number" class="form-control form-control-sm" placeholder="0">
                    </div>
                </div>
                <div class="card-footer bg-transparent text-end">
                    <button v-on:click="themBienThe()" type="button" class="btn btn-primary btn-sm px-3">
                        <i class='bx bx-plus-circle me-1'></i> Thêm Biến Thể
                    </button>
                </div>
            </div>
        </div>

        <!-- Cột Phải: Danh Sách Biến Thể -->
        <div class="col-lg-8">
            <div class="card border-top border-3 border-primary shadow-sm">
                <div class="card-header bg-transparent py-2">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <b class="text-uppercase text-dark">Danh Sách Biến Thể Tồn Kho ({{ list_bien_the_loc.length }})</b>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="input-group input-group-sm ms-auto" style="max-width: 260px;">
                                <span class="input-group-text bg-light border-end-0 pe-0 rounded-start-pill text-muted">
                                    <i class='bx bx-search fs-6'></i>
                                </span>
                                <input type="text" v-model="key_search" class="form-control bg-light border-start-0 rounded-end-pill ps-2 shadow-none" placeholder="Tìm sản phẩm, màu, size...">
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
                                    <th>Sản Phẩm</th>
                                    <th>Màu Sắc</th>
                                    <th>Kích Thước</th>
                                    <th>Tồn Kho</th>
                                    <th>Giá Chênh</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in list_bien_the_loc" :key="item.id">
                                    <td class="text-center">{{ index + 1 }}</td>
                                    <td class="fw-bold text-dark text-wrap" style="max-width: 250px;">
                                        {{ item.san_pham ? item.san_pham.ten_san_pham : '---' }}
                                    </td>
                                    <td class="text-center">
                                        <span v-if="item.mau_sac" class="badge rounded-pill text-white px-2 py-1"
                                            :style="{ backgroundColor: item.mau_sac.ma_mau || '#333' }">
                                            {{ item.mau_sac.ten_mau }}
                                        </span>
                                        <span v-else class="text-muted">---</span>
                                    </td>
                                    <td class="text-center fw-bold text-primary">
                                        {{ item.kich_thuoc ? item.kich_thuoc.ten_kich_thuoc : '---' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                            {{ item.so_luong_ton }} cái
                                        </span>
                                    </td>
                                    <td class="text-end text-muted small">
                                        {{ Number(item.gia_chenh_lech).toLocaleString('vi-VN') }} đ
                                    </td>
                                    <td class="text-center">
                                        <button v-on:click="xoaBienThe(item)" class="btn btn-danger btn-sm">
                                            <i class='bx bx-trash'></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="list_bien_the_loc.length === 0">
                                    <td colspan="7" class="text-center py-4 text-muted">Không tìm thấy biến thể nào.</td>
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
    name: 'AdminChiTietSanPham',
    data() {
        return {
            key_search: '',
            list_bien_the: [],
            list_san_pham: [],
            list_mau_sac: [],
            list_kich_thuoc: [],
            create_BienThe: {
                id_san_pham: '',
                id_mau_sac: '',
                id_kich_thuoc: '',
                so_luong_ton: 50,
                gia_chenh_lech: 0
            }
        }
    },
    computed: {
        list_bien_the_loc() {
            if (!this.key_search) return this.list_bien_the;
            let kw = this.key_search.toLowerCase().trim();
            return this.list_bien_the.filter(i => {
                const sp = i.san_pham ? i.san_pham.ten_san_pham.toLowerCase() : '';
                const ms = i.mau_sac ? i.mau_sac.ten_mau.toLowerCase() : '';
                const kt = i.kich_thuoc ? i.kich_thuoc.ten_kich_thuoc.toLowerCase() : '';
                return sp.includes(kw) || ms.includes(kw) || kt.includes(kw);
            });
        }
    },
    mounted() {
        this.loadData();
        this.loadOptions();
    },
    methods: {
        loadData() {
            axios.get('http://127.0.0.1:8000/api/admin/chi-tiet-san-pham/get-data')
                .then(res => { this.list_bien_the = res.data.data; });
        },
        loadOptions() {
            axios.get('http://127.0.0.1:8000/api/admin/san-pham/get-data')
                .then(res => { this.list_san_pham = res.data.data; });
            axios.get('http://127.0.0.1:8000/api/admin/mau-sac/get-data')
                .then(res => { this.list_mau_sac = res.data.data; });
            axios.get('http://127.0.0.1:8000/api/admin/kich-thuoc/get-data')
                .then(res => { this.list_kich_thuoc = res.data.data; });
        },
        themBienThe() {
            if (!this.create_BienThe.id_san_pham || this.create_BienThe.so_luong_ton === '') {
                this.$toast.error('Vui lòng chọn sản phẩm và nhập số lượng tồn kho');
                return;
            }
            axios.post('http://127.0.0.1:8000/api/admin/chi-tiet-san-pham/create', this.create_BienThe)
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                        this.create_BienThe = { id_san_pham: '', id_mau_sac: '', id_kich_thuoc: '', so_luong_ton: 50, gia_chenh_lech: 0 };
                    }
                });
        },
        async xoaBienThe(item) {
            const confirmed = await this.$confirmDelete(`Bạn có chắc chắn muốn xóa biến thể <b>${item.ten_mau || ''} - ${item.ten_kich_thuoc || ''}</b> này không?`);
            if (confirmed) {
                axios.post('http://127.0.0.1:8000/api/admin/chi-tiet-san-pham/destroy', { id: item.id })
                    .then(res => {
                        if (res.data.status) {
                            this.$toast.success(res.data.message);
                            this.loadData();
                        }
                    });
            }
        }
    }
}
</script>
