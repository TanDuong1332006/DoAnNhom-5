<template>
  <div class="container py-5">
    <h3 class="fw-bold mb-4"><i class="bi bi-cart3 me-2"></i>Giỏ Hàng</h3>

    <div v-if="cart.items.length === 0" class="text-center py-5 text-muted">
      <i class="bi bi-cart-x fs-1"></i>
      <p class="mt-3 fs-5">Giỏ hàng trống</p>
      <router-link to="/shop" class="btn btn-warning">Tiếp tục mua sắm</router-link>
    </div>

    <div v-else class="row g-4">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-0">
            <div v-for="item in cart.items" :key="item.id" class="d-flex align-items-center p-3 border-bottom">
              <img :src="item.product?.image" width="80" height="80" class="rounded me-3" style="object-fit:cover;" :alt="item.product?.name">
              <div class="flex-grow-1">
                <h6 class="mb-1 fw-semibold">{{ item.product?.name }}</h6>
                <span class="text-warning fw-bold">{{ formatPrice(item.price) }}đ</span>
              </div>
              <div class="input-group" style="width: 130px;">
                <button class="btn btn-outline-secondary btn-sm" @click="updateQty(item, item.quantity - 1)">−</button>
                <input type="number" class="form-control form-control-sm text-center" :value="item.quantity" min="1" @change="updateQty(item, +$event.target.value)">
                <button class="btn btn-outline-secondary btn-sm" @click="updateQty(item, item.quantity + 1)">+</button>
              </div>
              <div class="text-end ms-3" style="min-width: 100px;">
                <div class="fw-bold">{{ formatPrice(item.price * item.quantity) }}đ</div>
                <button class="btn btn-link text-danger p-0 btn-sm" @click="removeItem(item.id)"><i class="bi bi-trash"></i> Xoá</button>
              </div>
            </div>
          </div>
        </div>
        <button @click="clearCart" class="btn btn-outline-danger mt-3 btn-sm">
          <i class="bi bi-trash me-1"></i>Xoá toàn bộ giỏ hàng
        </button>
      </div>

      <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-dark text-white fw-bold">Tóm tắt đơn hàng</div>
          <div class="card-body">
            <!-- Coupon -->
            <div class="mb-3">
              <label class="form-label small fw-semibold">Mã giảm giá</label>
              <div class="input-group input-group-sm">
                <input v-model="couponCode" type="text" class="form-control" placeholder="Nhập mã..." @keyup.enter="applyCoupon">
                <button class="btn btn-warning" @click="applyCoupon">Áp dụng</button>
              </div>
              <div v-if="couponMessage" :class="couponStatus === 1 ? 'text-success' : 'text-danger'" class="small mt-1">
                {{ couponMessage }}
              </div>
            </div>
            <hr>
            <div class="d-flex justify-content-between mb-2">
              <span>Tạm tính</span>
              <span>{{ formatPrice(cart.subtotal) }}đ</span>
            </div>
            <div v-if="discount > 0" class="d-flex justify-content-between mb-2 text-success">
              <span>Giảm giá</span>
              <span>-{{ formatPrice(discount) }}đ</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
              <span>Phí vận chuyển</span>
              <span v-if="cart.subtotal >= 500000" class="text-success">Miễn phí</span>
              <span v-else>30.000đ</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between fw-bold fs-5">
              <span>Tổng cộng</span>
              <span class="text-danger">{{ formatPrice(totalWithShipping) }}đ</span>
            </div>
          </div>
          <div class="card-footer">
            <router-link to="/checkout" class="btn btn-warning w-100 fw-bold">
              <i class="bi bi-credit-card me-2"></i>Tiến hành thanh toán
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { cartStore } from '@/stores/cartStore'
import axiosClient from '@/services/axiosClient'

export default {
  name: 'Cart',
  data() {
    return { cart: cartStore, couponCode: '', couponMessage: '', couponStatus: 0, discount: 0 }
  },
  computed: {
    shippingFee() { return this.cart.subtotal >= 500000 ? 0 : 30000 },
    totalWithShipping() { return Math.max(0, this.cart.subtotal + this.shippingFee - this.discount) },
  },
  methods: {
    formatPrice(p) { return new Intl.NumberFormat('vi-VN').format(p) },
    async updateQty(item, qty) {
      if (qty < 1) return
      const res = await cartStore.updateItem(item.id, qty)
      if (res.status !== 1) this.$toast.error(res.message)
    },
    async removeItem(id) {
      const res = await cartStore.removeItem(id)
      if (res.status === 1) this.$toast.success('Đã xoá sản phẩm!')
    },
    async clearCart() {
      if (!confirm('Xoá toàn bộ giỏ hàng?')) return
      await cartStore.clearCart()
      this.$toast.success('Đã xoá giỏ hàng!')
    },
    async applyCoupon() {
      if (!this.couponCode.trim()) return
      const res = await axiosClient.post('/coupons/check', { code: this.couponCode, subtotal: this.cart.subtotal })
      this.couponStatus = res.data.status
      this.couponMessage = res.data.message
      if (res.data.status === 1) {
        this.discount = res.data.discount
        localStorage.setItem('cart_coupon', JSON.stringify({ code: this.couponCode, discount: this.discount }))
      } else {
        this.discount = 0
        localStorage.removeItem('cart_coupon')
      }
    },
  },
  mounted() { cartStore.fetchCart() },
}
</script>
