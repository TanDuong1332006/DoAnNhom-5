<template>
    <div class="row">
        <!-- Thanh Tìm Kiếm & Bộ Lọc -->
        <div class="col-12 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="row align-items-center g-2">
                        <div class="col-md-4">
                            <h5 class="mb-0 text-dark fw-bold text-uppercase fs-6">
                                <i class="bx bx-receipt text-primary me-1"></i> Quản Lý Đơn Hàng ({{ list_don_hang_loc.length }})
                            </h5>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class='bx bx-search'></i></span>
                                <input v-model="key_search" type="text" class="form-control bg-light border-start-0" placeholder="Tìm theo mã đơn, tên khách, SĐT...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select v-model="filter_trang_thai" class="form-select form-select-sm">
                                <option value="">-- Tất cả trạng thái --</option>
                                <option value="cho_xac_nhan">Chờ xác nhận</option>
                                <option value="da_xac_nhan">Đã xác nhận</option>
                                <option value="dang_giao">Đang giao hàng</option>
                                <option value="da_giao">Đã giao thành công</option>
                                <option value="da_huy">Đã hủy</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng Đơn Hàng -->
        <div class="col-12">
            <div class="card border-top border-3 border-primary shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0 align-middle text-nowrap">
                            <thead class="table-primary text-center">
                                <tr>
                                    <th>STT</th>
                                    <th>Mã Đơn</th>
                                    <th>Khách Hàng</th>
                                    <th>Số Điện Thoại</th>
                                    <th>Tổng Tiền</th>
                                    <th>Thanh Toán</th>
                                    <th>Trạng Thái Đơn</th>
                                    <th>Ngày Tạo</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in list_don_hang_loc" :key="item.id">
                                    <td class="text-center">{{ index + 1 }}</td>
                                    <td class="text-center fw-bold text-primary font-monospace">{{ item.ma_don_hang }}</td>
                                    <td class="fw-semibold text-dark">{{ item.ho_ten }}</td>
                                    <td class="text-center font-monospace">{{ item.so_dien_thoai }}</td>
                                    <td class="text-end fw-bold text-danger">{{ formatTien(item.tong_thanh_toan) }} đ</td>
                                    <td class="text-center">
                                        <span class="badge" :class="item.trang_thai_thanh_toan === 'da_thanh_toan' ? 'bg-success' : 'bg-secondary'">
                                            {{ item.trang_thai_thanh_toan === 'da_thanh_toan' ? 'Đã TT' : 'Chưa TT' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill" :class="badgeTrangThai(item.trang_thai_don_hang)">
                                            {{ textTrangThai(item.trang_thai_don_hang) }}
                                        </span>
                                    </td>
                                    <td class="text-center text-muted small">
                                        {{ item.created_at ? item.created_at.slice(0, 10) : '' }}
                                    </td>
                                    <td class="text-center">
                                        <button v-on:click="xemChiTiet(item)" class="btn btn-info btn-sm text-white me-1" data-bs-toggle="modal" data-bs-target="#modalChiTietDH">
                                            <i class='bx bx-show'></i> Chi tiết
                                        </button>
                                        <button v-on:click="xoaDonHang(item)" class="btn btn-danger btn-sm">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="list_don_hang_loc.length === 0">
                                    <td colspan="9" class="text-center py-4 text-muted">Không tìm thấy đơn hàng nào.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Chi Tiết & Đổi Trạng Thái Đơn Hàng -->
        <div class="modal fade" id="modalChiTietDH" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" v-if="selected_don_hang">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white">
                            Chi Tiết Đơn Hàng: <span class="font-monospace text-warning">{{ selected_don_hang.ma_don_hang }}</span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Thông tin người nhận -->
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <strong>Người nhận:</strong> {{ selected_don_hang.ho_ten }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Số điện thoại:</strong> {{ selected_don_hang.so_dien_thoai }}
                                    </div>
                                    <div class="col-12">
                                        <strong>Địa chỉ giao:</strong> {{ selected_don_hang.dia_chi_giao }}
                                    </div>
                                    <div class="col-12 text-muted small" v-if="selected_don_hang.ghi_chu">
                                        <strong>Ghi chú:</strong> {{ selected_don_hang.ghi_chu }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Danh sách sản phẩm mua -->
                        <h6 class="fw-bold text-dark mb-2">Danh sách sản phẩm</h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-sm align-middle text-nowrap">
                                <thead class="table-light text-center">
                                    <tr>
                                        <th>Ảnh</th>
                                        <th>Tên Sản Phẩm</th>
                                        <th>Phân Loại</th>
                                        <th>Đơn Giá</th>
                                        <th>Số Lượng</th>
                                        <th>Thành Tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="ct in selected_don_hang.chi_tiets" :key="ct.id">
                                        <td class="text-center">
                                            <img :src="ct.hinh_anh || 'https://via.placeholder.com/40'" class="rounded border" style="width: 35px; height: 35px; object-fit: cover;">
                                        </td>
                                        <td class="text-wrap" style="max-width: 250px;">{{ ct.ten_san_pham }}</td>
                                        <td class="text-center small text-muted">
                                            {{ ct.ten_mau ? 'Màu: ' + ct.ten_mau : '' }} {{ ct.ten_kich_thuoc ? '\| Size: ' + ct.ten_kich_thuoc : '' }}
                                        </td>
                                        <td class="text-end">{{ formatTien(ct.gia_ban) }} đ</td>
                                        <td class="text-center">{{ ct.so_luong }}</td>
                                        <td class="text-end fw-bold text-danger">{{ formatTien(ct.thanh_tien) }} đ</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Tổng kết tiền -->
                        <div class="text-end border-top pt-2">
                            <div>Tiền hàng: <strong>{{ formatTien(selected_don_hang.tong_tien_hang) }} đ</strong></div>
                            <div>Phí vận chuyển: <strong>{{ formatTien(selected_don_hang.phi_van_chuyen) }} đ</strong></div>
                            <div>Giảm giá: <strong>-{{ formatTien(selected_don_hang.tien_giam_gia) }} đ</strong></div>
                            <h5 class="text-danger fw-bold mt-1">Tổng thanh toán: {{ formatTien(selected_don_hang.tong_thanh_toan) }} đ</h5>
                        </div>

                        <!-- Cập nhật trạng thái -->
                        <div class="row g-2 border-top pt-3 mt-3 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Trạng thái đơn hàng:</label>
                                <select v-model="selected_don_hang.trang_thai_don_hang" class="form-select form-select-sm">
                                    <option value="cho_xac_nhan">Chờ xác nhận</option>
                                    <option value="da_xac_nhan">Đã xác nhận</option>
                                    <option value="dang_giao">Đang giao hàng</option>
                                    <option value="da_giao">Đã giao hàng thành công</option>
                                    <option value="da_huy">Hủy đơn hàng</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Trạng thái thanh toán:</label>
                                <select v-model="selected_don_hang.trang_thai_thanh_toan" class="form-select form-select-sm">
                                    <option value="chua_thanh_toan">Chưa thanh toán</option>
                                    <option value="da_thanh_toan">Đã thanh toán</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                        <button v-on:click="capNhatTrangThaiDonHang()" type="button" class="btn btn-primary btn-sm px-3" data-bs-dismiss="modal">
                            Lưu Cập Nhật
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'AdminDonHang',
    data() {
        return {
            key_search: '',
            filter_trang_thai: '',
            list_don_hang: [],
            selected_don_hang: null,
        }
    },
    computed: {
        list_don_hang_loc() {
            let res = this.list_don_hang;
            if (this.filter_trang_thai) {
                res = res.filter(i => i.trang_thai_don_hang === this.filter_trang_thai);
            }
            if (this.key_search) {
                let kw = this.key_search.toLowerCase().trim();
                res = res.filter(i =>
                    (i.ma_don_hang && i.ma_don_hang.toLowerCase().includes(kw)) ||
                    (i.ho_ten && i.ho_ten.toLowerCase().includes(kw)) ||
                    (i.so_dien_thoai && i.so_dien_thoai.toLowerCase().includes(kw))
                );
            }
            return res;
        }
    },
    mounted() {
        this.loadData();
    },
    methods: {
        formatTien(val) {
            if (!val) return '0';
            return Number(val).toLocaleString('vi-VN');
        },
        badgeTrangThai(status) {
            switch(status) {
                case 'cho_xac_nhan': return 'bg-warning text-dark';
                case 'da_xac_nhan': return 'bg-info text-white';
                case 'dang_giao': return 'bg-primary text-white';
                case 'da_giao': return 'bg-success text-white';
                case 'da_huy': return 'bg-danger text-white';
                default: return 'bg-secondary text-white';
            }
        },
        textTrangThai(status) {
            switch(status) {
                case 'cho_xac_nhan': return 'Chờ xác nhận';
                case 'da_xac_nhan': return 'Đã xác nhận';
                case 'dang_giao': return 'Đang giao';
                case 'da_giao': return 'Đã giao hàng';
                case 'da_huy': return 'Đã hủy';
                default: return status;
            }
        },
        loadData() {
            axios.get('http://127.0.0.1:8000/api/admin/don-hang/get-data')
                .then(res => { this.list_don_hang = res.data.data; });
        },
        xemChiTiet(item) {
            this.selected_don_hang = Object.assign({}, item);
        },
        capNhatTrangThaiDonHang() {
            axios.post('http://127.0.0.1:8000/api/admin/don-hang/update-status', {
                id: this.selected_don_hang.id,
                trang_thai_don_hang: this.selected_don_hang.trang_thai_don_hang,
                trang_thai_thanh_toan: this.selected_don_hang.trang_thai_thanh_toan,
            })
            .then(res => {
                if (res.data.status) {
                    this.$toast.success(res.data.message);
                    this.loadData();
                }
            });
        },
        xoaDonHang(item) {
            if (confirm('Bạn có chắc chắn muốn xóa đơn hàng này không?')) {
                axios.post('http://127.0.0.1:8000/api/admin/don-hang/destroy', { id: item.id })
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
