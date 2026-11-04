<template>
  <div>
    <div class="fs-3 fw-bold mb-3" style="color: #b48325;">{{ price.toLocaleString('vi-VN') }}₫</div>
    <fieldset v-if="hasColors" class="mb-3">
      <legend class="fs-6 fw-semibold">Màu sắc</legend>
      <div class="d-flex gap-2 flex-wrap">
        <button v-for="group in colors" :key="group.key" type="button" class="quick-swatch"
          :aria-pressed="colorKey === group.key" :aria-label="group.color?.ten_mau || 'Tiêu chuẩn'"
          :title="group.color?.ten_mau || 'Tiêu chuẩn'" @click="chooseColor(group.key)">
          <span :style="{ backgroundColor: swatchColor(group.color?.ma_mau) }">{{ swatchColor(group.color?.ma_mau) ? '' : (group.color?.ten_mau || 'TC').slice(0, 2) }}</span>
        </button>
      </div>
    </fieldset>
    <fieldset v-if="hasSizes" class="mb-3">
      <legend class="fs-6 fw-semibold">Size / Quy cách</legend>
      <div class="d-flex gap-2 flex-wrap">
        <button v-for="variant in sizes" :key="optionKey(variant.id_kich_thuoc)" type="button" class="btn btn-outline-dark"
          :aria-pressed="sizeKey === optionKey(variant.id_kich_thuoc)" :disabled="!sizeAvailable(variant)"
          @click="sizeKey = optionKey(variant.id_kich_thuoc); quantity = 1">
          {{ variant.kich_thuoc?.ten_kich_thuoc || 'Tiêu chuẩn' }}
        </button>
      </div>
    </fieldset>
    <p class="small text-secondary" role="status">{{ hasOptions && !selected ? `Tổng tồn kho: ${totalStock} sản phẩm` : `Tồn kho: ${stock} sản phẩm` }}</p>
    <div class="d-flex gap-3">
      <input v-model.number="quantity" type="number" min="1" :max="stock" step="1" class="form-control" style="max-width: 90px;" aria-label="Số lượng" />
      <button class="btn btn-urbn-primary rounded-pill flex-grow-1" :disabled="!canAdd" @click="add">Thêm vào giỏ</button>
    </div>
  </div>
</template>

<script>
import { cartStore } from '../../stores/cartStore';
import { variantsOf, hasVariants, variantPrice, availableStock, totalAvailableStock } from '../../services/productCatalog';
import { colorGroups, findVariant, imageForColor, optionKey, purchaseSelection, remainingQuantity, sizeGroups } from '../../services/productDetail';

export default {
  props: { product: { type: Object, required: true }, inputId: { type: String, default: 'product-variant' } },
  emits: ['color-image'],
  data() { return { colorKey: null, sizeKey: null, quantity: 1 }; },
  computed: {
    variants() { return variantsOf(this.product); },
    hasOptions() { return hasVariants(this.product); },
    colors() { return colorGroups(this.product); },
    sizes() { return this.hasColors && this.colorKey === null ? [] : sizeGroups(this.product, this.colorKey); },
    hasColors() { return this.colors.some(group => group.color); },
    hasSizes() { return this.sizes.some(variant => variant.kich_thuoc); },
    selected() { return findVariant(this.product, this.hasColors ? this.colorKey : 'none', this.hasSizes ? this.sizeKey : 'none'); },
    price() { return variantPrice(this.product, this.selected); },
    stock() { return availableStock(this.product, this.selected); },
    totalStock() { return totalAvailableStock(this.product); },
    canAdd() {
      return (!this.hasOptions || !!this.selected) && Number.isInteger(this.quantity)
        && this.quantity > 0 && this.quantity <= remainingQuantity(this.product, this.selected, cartStore.items);
    }
  },
  watch: { product() { this.colorKey = null; this.sizeKey = null; this.quantity = 1; } },
  methods: {
    optionKey,
    swatchColor(value) { return /^#(?:[0-9a-f]{3}|[0-9a-f]{6})$/i.test(value || '') ? value : null; },
    chooseColor(key) {
      this.colorKey = key;
      if (this.hasSizes && !this.selected) this.sizeKey = null;
      this.quantity = 1;
      this.$emit('color-image', imageForColor(this.product, key));
    },
    sizeAvailable(variant) {
      const key = optionKey(variant.id_kich_thuoc);
      return this.variants.some(item => optionKey(item.id_kich_thuoc) === key
        && (this.colorKey === null || optionKey(item.id_mau_sac) === this.colorKey)
        && Number(item.so_luong_ton) > 0);
    },
    add() {
      if (!this.canAdd || !purchaseSelection(cartStore, this.product, this.selected, this.quantity)) return;
      this.$toast?.success('Đã thêm sản phẩm vào giỏ hàng.');
    }
  }
};
</script>

<style scoped>
.quick-swatch { width: 44px; height: 44px; border-radius: 50%; padding: 4px; background: white; border: 1px solid #aaa; }
.quick-swatch span { display: grid; place-items: center; width: 100%; height: 100%; border-radius: 50%; }
.quick-swatch[aria-pressed="true"] { border: 2px solid #222; }
button[aria-pressed="true"].btn { background: #222; color: white; }
</style>
