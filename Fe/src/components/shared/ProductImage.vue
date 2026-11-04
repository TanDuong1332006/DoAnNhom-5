<template><span v-bind="$attrs" class="product-photo"><img :src="failed ? imagePlaceholder : productImageUrl(src)" :alt="alt" @error="failed = true" /><span v-if="!failed && isIllustrativeImage(src)" class="illustration-label">Ảnh minh họa · khác mẫu thực tế</span></span></template>
<script>
import { productImageUrl, imagePlaceholder, isIllustrativeImage } from '../../services/productImages.js';
export default { inheritAttrs: false, props: { src: { type: String, default: '' }, alt: { type: String, default: 'Ảnh sản phẩm' } }, data() { return { failed: false, imagePlaceholder }; }, watch: { src() { this.failed = false; } }, methods: { productImageUrl, isIllustrativeImage } };
</script>

<style scoped>
.product-photo{position:relative;display:inline-block;width:100%;overflow:hidden;vertical-align:middle}.product-photo>img{display:block;width:100%;height:100%;max-height:inherit;object-fit:inherit}.illustration-label{position:absolute;bottom:8px;left:8px;right:8px;max-width:max-content;border-radius:6px;padding:5px 8px;background:#fffaf0f2;color:#72552c;font:10px/1.4 sans-serif;pointer-events:none;z-index:1}
</style>
