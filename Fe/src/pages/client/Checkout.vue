<template>
  <div class="container py-5">
    <h3 class="fw-bold mb-4"><i class="bi bi-bag-check me-2"></i>Thanh Toán</h3>
    <div class="row g-4">
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
          <div class="card-header fw-bold bg-dark text-white">Thông tin giao hàng</div>
          <div class="card-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Họ và tên *</label>
              <input v-model="form.name" type="text" class="form-control" placeholder="Nguyễn Văn A">
              <div v-if="errors.name" class="text-danger small">{{ errors.name }}</div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Email *</label>
                <input v-model="form.email" type="email" class="form-control" placeholder="email@example.com">
                <div v-if="errors.email" class="text-danger small">{{ errors.email }}</div>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Số điện thoại *</label>
                <input v-model="form.phone" type="tel" class="form-control" placeholder="0909123456">
                <div v-if="errors.phone" class="text-danger small">{{ errors.phone }}</div>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Địa chỉ giao hàng *</label>
              <textarea v-model="form.address" class="form-control" rows="2" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành"></textarea>
              <div v-if="errors.address" class="text-danger small">{{ errors.address }}</div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Ghi chú</label>
              <textarea v-model="form.note" class="form-control" rows="2" placeholder="Ghi chú cho người giao hàng..."></textarea>
            </div>

            <hr>
            <label class="form-label fw-semibold">Phương thức thanh toán *</label>
            <div class="d-flex gap-3">
              <label class="flex-grow-1">
                <input type="radio" v-model="form.payment_method" value="1" class="me-2">
                <i class="bi bi-cash-stack me-2 text-success"></i>Thanh toán khi nhận hàng (COD)
              </label>
              <label class="flex-grow-1">
                <input type="radio" v-model="form.payment_method" value="2" class="me-2">
                <i class="bi bi-qr-code me-2 text-primary"></i>Chuyển khoản ngân hàng
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
          <div class="card-header fw-bold bg-dark text-white">Đơn hàng của bạn</div>
          <div class="card-body">
            <div v-for="item in cart.items" :key="item.id" class="d-flex align-items-center mb-3">
              <img :src="item.product?.image" width="50" height="50" class="rounded me-2" style="object-fit:cover;">
              <div class="flex-grow-1">
                <div class="fw-semibold small">{{ item.product?.name }}</div>
                <div class="text-muted small">x{{ item.quantity }}</div>
              </div>
              <div class="fw-bold">{{ formatPrice(item.price * item.quantity) }}đ</div>
            </div>
            <hr>
            <div class="d-flex justify-content-between mb-1"><span>Tạm tính</span><span>{{ formatPrice(cart.subtotal) }}đ</span></div>
            <div v-if="coupon.discount > 0" class="d-flex justify-content-between mb-1 text-success"><span>Giảm giá ({{ coupon.code }})</span><span>-{{ formatPrice(coupon.discount) }}đ</span></div>
            <div class="d-flex justify-content-between mb-1"><span>Phí ship</span><span v-if="shippingFee === 0" class="text-success">Miễn phí</span><span v-else>{{ formatPrice(shippingFee) }}đ</span></div>
            <hr>
            <div class="d-flex justify-content-between fw-bold fs-5">
              <span>Tổng cộng</span>
              <span class="text-danger">{{ formatPrice(grandTotal) }}đ</span>
            </div>
          </div>
          <div class="card-footer">
            <button @click="placeOrder" :disabled="submitting" class="btn btn-warning w-100 fw-bold btn-lg">
              <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="bi bi-check-circle me-2"></i>Đặt Hàng Ngay
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axiosClient from '@/services/axiosClient'
import { authStore } from '@/stores/authStore'
import { cartStore } from '@/stores/cartStore'

export default {
  name: 'Checkout',
  data() {
    return {
      cart: cartStore,
      form: { name: authStore.user?.name || '', email: authStore.user?.email || '', phone: authStore.user?.phone || '', address: authStore.user?.address || '', payment_method: '1', note: '' },
      errors: {},
      submitting: false,
      coupon: { code: '', discount: 0 },
    }
  },
  computed: {
    shippingFee() { return this.cart.subtotal >= 500000 ? 0 : 30000 },
    grandTotal() { return Math.max(0, this.cart.subtotal + this.shippingFee - this.coupon.discount) },
  },
  mounted() {
    cartStore.fetchCart()
    const saved = localStorage.getItem('cart_coupon')
    if (saved) this.coupon = JSON.parse(saved)
  },
  methods: {
    formatPrice(p) { return new Intl.NumberFormat('vi-VN').format(p) },
    async placeOrder() {
      this.errors = {}
      if (!this.form.name) { this.errors.name = 'Vui lòng nhập tên.'; return }
      if (!this.form.email) { this.errors.email = 'Vui lòng nhập email.'; return }
      if (!this.form.phone) { this.errors.phone = 'Vui lòng nhập SĐT.'; return }
      if (!this.form.address) { this.errors.address = 'Vui lòng nhập địa chỉ.'; return }

      this.submitting = true
      try {
        const res = await axiosClient.post('/orders', {
          ...this.form,
          payment_method: parseInt(this.form.payment_method),
          coupon_code: this.coupon.code || null,
        })
        if (res.data.status === 1) {
          localStorage.removeItem('cart_coupon')
          this.$router.push({ path: '/order-success', query: { code: res.data.data.code } })
        } else {
          this.$toast.error(res.data.message)
        }
      } catch (e) {
        if (e.response?.data?.errors) {
          const firstKey = Object.keys(e.response.data.errors)[0]
          this.$toast.error(e.response.data.errors[firstKey][0])
        } else {
          this.$toast.error('Có lỗi xảy ra. Vui lòng thử lại!')
        }
      }
      this.submitting = false
    },
  },
}
</script>
