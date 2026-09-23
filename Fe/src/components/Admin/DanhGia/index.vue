<template>
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0 text-dark fw-bold text-uppercase fs-6">
                                <i class="bx bx-star text-warning me-1"></i> Quản Lý Đánh Giá &amp; Phản Hồi ({{ list_danh_gia.length }})
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-top border-3 border-primary shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0 align-middle text-nowrap">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th>STT</th>
                                    <th>Sản Phẩm</th>
                                    <th>Khách Hàng</th>
                                    <th>Đánh Giá (Sao)</th>
                                    <th>Tiêu Đề &amp; Nội Dung</th>
                                    <th>Trạng Thái</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in list_danh_gia" :key="item.id">
                                    <td class="text-center">{{ index + 1 }}</td>
                                    <td class="fw-semibold text-dark text-wrap" style="max-width: 220px;">
                                        {{ item.san_pham ? item.san_pham.ten_san_pham : '---' }}
                                    </td>
                                    <td class="text-center">
                                        {{ item.khach_hang ? item.khach_hang.ho_va_ten : 'Ẩn danh' }}
                                    </td>
                                    <td class="text-center text-warning fs-6">
                                        <span v-for="s in item.so_sao" :key="s">★</span>
                                    </td>
                                    <td class="text-wrap" style="max-width: 320px;">
                                        <strong v-if="item.tieu_de" class="d-block text-dark">{{ item.tieu_de }}</strong>
                                        <small class="text-muted">{{ item.noi_dung }}</small>
                                    </td>
                                    <td class="text-center">
                                        <button v-on:click="changeStatus(item)" v-if="item.trang_thai == 1" class="btn btn-sm btn-success py-0 px-2">
                                            Hiển thị
                                        </button>
                                        <button v-on:click="changeStatus(item)" v-else class="btn btn-sm btn-secondary py-0 px-2">
                                            Đang ẩn
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button v-on:click="xoaDanhGia(item)" class="btn btn-danger btn-sm">
                                            <i class='bx bx-trash'></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="list_danh_gia.length === 0">
                                    <td colspan="7" class="text-center py-4 text-muted">Chưa có đánh giá nào.</td>
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
    name: 'AdminDanhGia',
    data() {
        return {
            list_danh_gia: [],
        }
    },
    mounted() {
        this.loadData();
    },
    methods: {
        loadData() {
            axios.get('http://127.0.0.1:8000/api/admin/danh-gia/get-data')
                .then(res => { this.list_danh_gia = res.data.data; });
        },
        changeStatus(item) {
            axios.post('http://127.0.0.1:8000/api/admin/danh-gia/change-status', { id: item.id })
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                    }
                });
        },
        xoaDanhGia(item) {
            if (confirm('Bạn có chắc muốn xóa đánh giá này?')) {
                axios.post('http://127.0.0.1:8000/api/admin/danh-gia/destroy', { id: item.id })
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
