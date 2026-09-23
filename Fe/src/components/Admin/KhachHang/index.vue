<template>
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0 text-dark fw-bold text-uppercase fs-6">
                                <i class="bx bx-user-pin text-primary me-1"></i> Quản Lý Khách Hàng ({{ list_khach_hang_loc.length }})
                            </h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="input-group input-group-sm ms-auto" style="max-width: 320px;">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class='bx bx-search'></i></span>
                                <input v-model="key_search" type="text" class="form-control bg-light border-start-0" placeholder="Tìm theo tên, email, SĐT...">
                            </div>
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
                                    <th>Họ Và Tên</th>
                                    <th>Email</th>
                                    <th>Số Điện Thoại</th>
                                    <th>Địa Chỉ</th>
                                    <th>Số Đơn Hàng</th>
                                    <th>Trạng Thái Kích Hoạt</th>
                                    <th>Khóa Tài Khoản</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in list_khach_hang_loc" :key="item.id">
                                    <td class="text-center">{{ index + 1 }}</td>
                                    <td class="fw-bold text-dark">
                                        <i class='bx bx-user text-primary me-1'></i>{{ item.ho_va_ten }}
                                    </td>
                                    <td class="text-muted">{{ item.email }}</td>
                                    <td class="text-center font-monospace">{{ item.so_dien_thoai || '---' }}</td>
                                    <td class="text-wrap" style="max-width: 200px;">{{ item.dia_chi || '---' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                                            {{ item.don_hangs_count || 0 }} đơn
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button v-on:click="changeStatus(item)" v-if="item.is_kich_hoat == 1" class="btn btn-sm btn-success py-0 px-2">
                                            Đã kích hoạt
                                        </button>
                                        <button v-on:click="changeStatus(item)" v-else class="btn btn-sm btn-secondary py-0 px-2">
                                            Chưa kích hoạt
                                        </button>
                                    </td>
                                    <td class="text-center">
                                        <button v-on:click="toggleBlock(item)" v-if="item.is_khoa == 1" class="btn btn-sm btn-danger py-0 px-2">
                                            <i class='bx bx-lock-alt'></i> Đang bị khóa
                                        </button>
                                        <button v-on:click="toggleBlock(item)" v-else class="btn btn-sm btn-outline-success py-0 px-2">
                                            <i class='bx bx-lock-open-alt'></i> Bình thường
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="list_khach_hang_loc.length === 0">
                                    <td colspan="8" class="text-center py-4 text-muted">Không tìm thấy khách hàng nào.</td>
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
    name: 'AdminKhachHang',
    data() {
        return {
            key_search: '',
            list_khach_hang: [],
        }
    },
    computed: {
        list_khach_hang_loc() {
            if (!this.key_search) return this.list_khach_hang;
            let kw = this.key_search.toLowerCase().trim();
            return this.list_khach_hang.filter(i =>
                (i.ho_va_ten && i.ho_va_ten.toLowerCase().includes(kw)) ||
                (i.email && i.email.toLowerCase().includes(kw)) ||
                (i.so_dien_thoai && i.so_dien_thoai.toLowerCase().includes(kw))
            );
        }
    },
    mounted() {
        this.loadData();
    },
    methods: {
        loadData() {
            axios.get('http://127.0.0.1:8000/api/admin/khach-hang/get-data')
                .then(res => { this.list_khach_hang = res.data.data; });
        },
        changeStatus(item) {
            axios.post('http://127.0.0.1:8000/api/admin/khach-hang/change-status', { id: item.id })
                .then(res => {
                    if (res.data.status) {
                        this.$toast.success(res.data.message);
                        this.loadData();
                    }
                });
        },
        toggleBlock(item) {
            axios.post('http://127.0.0.1:8000/api/admin/khach-hang/toggle-block', { id: item.id })
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
