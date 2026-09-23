<template>
  <div class="container py-5 text-center">
    <div class="py-5">
      <div class="mb-4">
        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
      </div>
      <h2 class="fw-bold text-success mb-3">Đặt Hàng Thành Công!</h2>
      <p class="text-muted mb-2">Cảm ơn bạn đã mua hàng tại ShopVN</p>
      <p class="mb-4">Mã đơn hàng của bạn: <span class="fw-bold text-warning fs-5">{{ orderCode }}</span></p>
      <p class="text-muted small mb-4">Chúng tôi sẽ liên hệ với bạn sớm để xác nhận đơn hàng.</p>
      <div class="d-flex gap-3 justify-content-center">
        <router-link to="/my-orders" class="btn btn-warning btn-lg"><i class="bi bi-bag me-2"></i>Xem đơn hàng</router-link>
        <router-link to="/shop" class="btn btn-outline-dark btn-lg">Tiếp tục mua sắm</router-link>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OrderSuccess',
  computed: { orderCode() { return this.$route.query.code || 'N/A' } },
}
</script>
