<template>
  <main class="container py-5">
    <h3 class="fw-bold mb-4">Đơn hàng của tôi</h3>
    <div v-if="!signedIn" class="alert alert-info">Vui lòng đăng nhập Customer để xem đơn hàng.
      <router-link to="/dang-nhap" class="btn btn-dark ms-3">Đăng nhập</router-link>
    </div>
    <template v-else>
      <button class="btn btn-outline-dark mb-3" :disabled="loading" @click="fetchOrders(page)">Tải lại danh sách</button>
      <p v-if="loading" role="status">Đang tải đơn hàng…</p>
      <div v-else-if="error" role="alert" class="alert alert-danger">{{ error }}</div>
      <p v-else-if="orders.length === 0">Bạn chưa có đơn hàng nào. <router-link to="/shop">Mua hàng ngay</router-link></p>
      <div v-else>
        <article v-for="order in orders" :key="order.id" class="card mb-3">
          <div class="card-body d-flex justify-content-between align-items-center gap-3">
            <div><strong>{{ order.ma_don_hang }}</strong><br><small>{{ formatDate(order.created_at) }}</small><br>
              {{ statusLabel(order.trang_thai_don_hang) }} · {{ paymentLabel(order.trang_thai_thanh_toan) }}
            </div>
            <div class="text-end"><strong>{{ formatPrice(order.tong_thanh_toan) }}đ</strong><br>
              <button class="btn btn-outline-dark mt-2" :disabled="detailLoading" @click="showOrder(order.id)">Xem chi tiết {{ order.ma_don_hang }}</button>
            </div>
          </div>
        </article>
        <nav aria-label="Phân trang đơn hàng" class="d-flex gap-3 align-items-center">
          <button class="btn btn-outline-dark" :disabled="page <= 1 || loading" @click="fetchOrders(page - 1)">Trang trước</button>
          <span>Trang {{ page }} / {{ lastPage }}</span>
          <button class="btn btn-outline-dark" :disabled="page >= lastPage || loading" @click="fetchOrders(page + 1)">Trang sau</button>
        </nav>
      </div>
      <p v-if="detailLoading" role="status">Đang tải chi tiết…</p>
      <div v-if="detailError" role="alert" class="alert alert-danger">{{ detailError }}
        <button class="btn btn-outline-danger ms-2" :disabled="detailLoading" @click="showOrder(selectedId)">Tải lại chi tiết</button>
      </div>
      <section v-if="selected && !detailLoading && !detailError" class="card mt-4" aria-label="Chi tiết đơn hàng">
        <div class="card-body">
          <h4>Chi tiết {{ selected.ma_don_hang }}</h4>
          <p>{{ statusLabel(selected.trang_thai_don_hang) }} · {{ paymentLabel(selected.trang_thai_thanh_toan) }} · {{ selected.phuong_thuc_thanh_toan }}</p>
          <p>Người nhận: {{ selected.ho_ten }} · {{ selected.so_dien_thoai }}<br>Địa chỉ: {{ selected.dia_chi_giao }}</p>
          <table class="table"><thead><tr><th>Sản phẩm / Biến thể</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
            <tbody><tr v-for="item in selected.chi_tiets" :key="item.id"><td>{{ item.ten_san_pham }} · {{ item.ten_mau }} / {{ item.ten_kich_thuoc }}</td><td>{{ item.so_luong }}</td><td>{{ formatPrice(item.gia_ban) }}đ</td><td>{{ formatPrice(item.thanh_tien) }}đ</td></tr></tbody>
          </table>
          <p>Tiền hàng: {{ formatPrice(selected.tong_tien_hang) }}đ · Giảm giá: {{ formatPrice(selected.tien_giam_gia) }}đ · Vận chuyển: {{ formatPrice(selected.phi_van_chuyen) }}đ</p>
          <strong>Tổng thanh toán: {{ formatPrice(selected.tong_thanh_toan) }}đ</strong>
          <div class="mt-3"><button class="btn btn-outline-dark" @click="showOrder(selected.id)">Cập nhật trạng thái</button></div>
        </div>
      </section>
    </template>
  </main>
</template>

<script>
import { apiUrl } from '../../services/apiConfig.js';
import axios from 'axios';
import { customerSession } from '../../services/authSessions';

export default {
  name: 'MyOrders',
  data() { return { signedIn: false, orders: [], loading: false, error: '', page: 1, lastPage: 1,
    selected: null, selectedId: null, detailLoading: false, detailError: '', sessionToken: null, requestEpoch: 0 }; },
  mounted() {
    this.syncSession();
    window.addEventListener('sofep-inbox-changed', this.syncSession);
    window.addEventListener('storage', this.syncSession);
  },
  beforeUnmount() {
    this.requestEpoch++;
    window.removeEventListener('sofep-inbox-changed', this.syncSession);
    window.removeEventListener('storage', this.syncSession);
  },
  methods: {
    syncSession() {
      const token = customerSession()?.token || null;
      if (token === this.sessionToken) return;
      this.sessionToken = token; this.requestEpoch++;
      this.signedIn = !!token; this.orders = []; this.selected = null; this.selectedId = null;
      this.error = ''; this.detailError = ''; this.loading = false; this.detailLoading = false;
      this.page = 1; this.lastPage = 1;
      if (token) this.fetchOrders();
    },
    async fetchOrders(page = 1) {
      if (this.loading) return;
      const epoch = this.requestEpoch;
      this.loading = true; this.error = ''; this.selected = null; this.detailError = '';
      try {
        const { data } = await axios.get(apiUrl('/client/don-hang'), { params: { page } });
        if (epoch !== this.requestEpoch) return;
        if (data.status !== 1 || !Array.isArray(data.data?.data)) throw new Error('Invalid order list');
        this.orders = data.data.data; this.page = data.data.current_page; this.lastPage = data.data.last_page;
      } catch (error) { if (epoch === this.requestEpoch) { this.error = this.orderError(error); this.orders = []; } }
      finally { if (epoch === this.requestEpoch) this.loading = false; }
    },
    async showOrder(id) {
      if (this.detailLoading) return;
      const epoch = this.requestEpoch;
      this.selectedId = id; this.detailLoading = true; this.detailError = ''; this.selected = null;
      try {
        const { data } = await axios.get(apiUrl('/client/don-hang/') + id);
        if (epoch !== this.requestEpoch) return;
        if (data.status !== 1 || !data.data?.id) throw new Error('Invalid order detail');
        this.selected = data.data;
      } catch (error) { if (epoch === this.requestEpoch) this.detailError = this.orderError(error); }
      finally { if (epoch === this.requestEpoch) this.detailLoading = false; }
    },
    orderError(error) {
      const status = error.response?.status;
      if (status === 401) return 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.';
      if (status === 403 || status === 404) return 'Đơn hàng không tồn tại hoặc bạn không có quyền truy cập.';
      return 'Không tải được đơn hàng. Vui lòng thử lại.';
    },
    formatPrice(value) { return new Intl.NumberFormat('vi-VN').format(Number(value || 0)); },
    formatDate(value) { return value ? new Date(value).toLocaleDateString('vi-VN') : ''; },
    statusLabel(value) { return ({ cho_xac_nhan: 'Chờ xác nhận', da_xac_nhan: 'Đã xác nhận', dang_giao: 'Đang giao', da_giao: 'Đã giao', da_huy: 'Đã hủy' })[value] || 'Chưa xác định'; },
    paymentLabel(value) { return ({ chua_thanh_toan: 'Chưa thanh toán', da_thanh_toan: 'Đã thanh toán', hoan_tien: 'Đã hoàn tiền' })[value] || 'Chưa xác định'; },
  },
};
</script>
