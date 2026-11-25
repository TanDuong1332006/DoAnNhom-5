<template><span v-bind="$attrs" class="product-photo" :style="dimensions"><img :style="height != null && width == null ? { width: 'auto' } : null" :src="failed ? imagePlaceholder : productImageUrl(src)" :alt="alt" @error="failed = true" /></span></template>
<script>
import { productImageUrl, imagePlaceholder } from '../../services/productImages.js';
export default { inheritAttrs: false, props: { width: [String, Number], height: [String, Number], src: { type: String, default: '' }, alt: { type: String, default: 'Ảnh sản phẩm' } }, data() { return { failed: false, imagePlaceholder }; }, watch: { src() { this.failed = false; } }, computed: { dimensions() { const size = value => /^\d+$/.test(String(value)) ? value + 'px' : value; return { ...(this.width != null ? { width: size(this.width) } : this.height != null ? { width: 'auto' } : {}), ...(this.height != null ? { height: size(this.height) } : {}) }; } }, methods: { productImageUrl } };
</script>

<style scoped>
.product-photo{position:relative;display:inline-block;width:100%;overflow:hidden;vertical-align:middle}.product-photo>img{display:block;width:100%;height:100%;max-height:inherit;object-fit:inherit}
</style>
