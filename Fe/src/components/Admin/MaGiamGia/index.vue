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
                        <input :disabled="!!editingId" v-model="create_MaGiamGia.ma_code" type="text" class="form-control form-control-sm text-uppercase font-monospace" placeholder="VD: SALE20, FREESHIP...">
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
                    <div class="row g-2 mb-2">
                        <div class="col-6"><label class="form-label">Ngày bắt đầu</label><input type="date" class="form-control form-control-sm" v-model="create_MaGiamGia.ngay_bat_dau"></div>
                        <div class="col-6"><label class="form-label">Ngày hết hạn</label><input type="date" class="form-control form-control-sm" v-model="create_MaGiamGia.ngay_ket_thuc"></div>
                    </div>
                    <template v-if="supportsEligibility">
                        <label class="form-label">Đối tượng khách</label>
                        <select v-model="create_MaGiamGia.doi_tuong" class="form-select form-select-sm mb-2">
                            <option value="all">Mọi khách</option><option value="new">Khách chưa có đơn còn hiệu lực</option><option value="selected">Khách được chọn</option>
                        </select>
                        <label class="form-label">Lượt tối đa mỗi khách (cần đăng nhập)</label>
                        <input type="number" min="1" v-model="create_MaGiamGia.gioi_han_moi_khach" class="form-control form-control-sm mb-2" placeholder="Để trống: không giới hạn">
                        <template v-if="create_MaGiamGia.doi_tuong === 'selected'"><label class="form-label">ID khách đủ điều kiện</label><input v-model="customerIds" class="form-control form-control-sm" placeholder="VD: 1, 2, 3"></template>
                    </template>
                    <p v-else class="small text-muted">Điều kiện khách và giới hạn mỗi khách cần migration đã được duyệt.</p>
                </div>
                <div class="card-footer bg-transparent text-end">
                    <button :disabled="saving" v-on:click="themMGG()" type="button" class="btn btn-primary btn-sm px-3">
                        {{ saving ? 'Đang lưu…' : editingId ? 'Lưu thay đổi' : 'Thêm Coupon' }}
                    </button><button v-if="editingId" @click="resetForm" class="btn btn-light btn-sm ms-2">Hủy sửa</button>
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
                                            {{ couponStatus(item) }}
                                        </button>
                                        <button v-on:click="changeStatus(item)" v-else class="btn btn-sm btn-secondary py-0 px-2">
                                            Tạm tắt
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button @click="editCoupon(item)" class="btn btn-outline-primary btn-sm me-1">Sửa</button>
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
            key_search: '', supportsEligibility: false, editingId: null, saving: false, customerIds: '',
            list_mgg: [],
            create_MaGiamGia: {
                ma_code: '',
                ten_giam_gia: '',
                loai_giam: 'phan_tram',
                gia_tri: '',
                gia_tri_toi_thieu: 0,
                giam_toi_da: null,
                so_luong: 100,
                trang_thai: 1, ngay_bat_dau: '', ngay_ket_thuc: '', doi_tuong: 'all', gioi_han_moi_khach: null
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
        showError(error) { this.$toast.error(Object.values(error.response?.data?.errors || {}).flat()[0] || error.response?.data?.message || 'Không thể thực hiện thao tác.'); },
        couponStatus(item) {
            const today = new Date().toLocaleDateString('sv-SE');
            if (item.ngay_bat_dau && item.ngay_bat_dau > today) return 'Chưa bắt đầu';
            if (item.ngay_ket_thuc && item.ngay_ket_thuc < today) return 'Hết hạn';
            if (Number(item.so_luong_da_dung) >= Number(item.so_luong)) return 'Hết lượt';
            return 'Kích hoạt';
        },
        resetForm() {
            this.editingId = null; this.customerIds = '';
            this.create_MaGiamGia = { ma_code: '', ten_giam_gia: '', loai_giam: 'phan_tram', gia_tri: '', gia_tri_toi_thieu: 0, giam_toi_da: null, so_luong: 100, trang_thai: 1, ngay_bat_dau: '', ngay_ket_thuc: '', doi_tuong: 'all', gioi_han_moi_khach: null };
        },
        editCoupon(item) {
            this.resetForm(); this.editingId = item.id;
            for (const key of Object.keys(this.create_MaGiamGia)) if (item[key] !== undefined) this.create_MaGiamGia[key] = item[key];
            this.customerIds = (item.ids_khach_hang || []).join(', ');
        },
        loadData() {
            axios.get('http://127.0.0.1:8000/api/admin/ma-giam-gia/get-data')
                .then(res => { this.list_mgg = res.data.data; this.supportsEligibility = !!res.data.supports_eligibility; }).catch(this.showError);
        },
        async themMGG() {
            if (this.saving) return;
            const payload = { ...this.create_MaGiamGia };
            for (const key of ['ngay_bat_dau', 'ngay_ket_thuc', 'giam_toi_da', 'gioi_han_moi_khach']) if (payload[key] === '') payload[key] = null;
            if (this.supportsEligibility) payload.ids_khach_hang = payload.doi_tuong === 'selected' ? this.customerIds.split(',').map(v => Number(v.trim())).filter(Boolean) : null;
            else for (const key of ['doi_tuong', 'gioi_han_moi_khach', 'ids_khach_hang']) delete payload[key];
            if (this.editingId) { payload.id = this.editingId; delete payload.ma_code; }
            this.saving = true;
            try {
                const { data } = await axios.post('http://127.0.0.1:8000/api/admin/ma-giam-gia/' + (this.editingId ? 'update' : 'create'), payload);
                if (data.status) { this.$toast.success(data.message); this.resetForm(); this.loadData(); }
            } catch (error) { this.showError(error); }
            finally { this.saving = false; }
        },
        async xoaMGG(item) {
            const confirmed = await this.$confirmDelete(`Bạn có chắc muốn xóa mã giảm giá <b>${item.ma_code}</b>? Thao tác này không thể hoàn tác.`);
            if (confirmed) {
                axios.post('http://127.0.0.1:8000/api/admin/ma-giam-gia/destroy', { id: item.id })
                    .then(res => {
                        if (res.data.status) {
                            this.$toast.success(res.data.message);
                            this.loadData();
                        }
                    }).catch(this.showError);
            }
        },
        changeStatus(item) {
            axios.post('http://127.0.0.1:8000/api/admin/ma-giam-gia/change-status', { id: item.id })
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                    }
                }).catch(this.showError);
        }
    }
}
</script>
