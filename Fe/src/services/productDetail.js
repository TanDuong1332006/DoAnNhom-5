import { isProductImage, productImageGallery, productImageUrl } from './productImages.js';
import { variantsOf, hasVariants, variantPrice, availableStock, galleryOf } from './productCatalog.js';

// Ảnh stock từ seeder chưa được xác minh với SKU; không dùng ảnh stock chưa xác minh để minh họa SKU.
export const productGallery = productImageGallery;

export function relatedProducts(products, product) {
  if (!product?.id_danh_muc) return [];
  return products.filter(item => Number(item.id) !== Number(product.id)
    && Number(item.id_danh_muc) === Number(product.id_danh_muc)).slice(0, 4);
}

export const optionKey = value => value == null ? 'none' : String(value);

export function colorGroups(product) {
  const groups = new Map();
  for (const variant of variantsOf(product)) {
    const key = optionKey(variant.id_mau_sac);
    if (!groups.has(key)) groups.set(key, { key, color: variant.mau_sac || null });
  }
  return [...groups.values()];
}

export function sizesFor(product, colorKey) {
  return variantsOf(product).filter(v => optionKey(v.id_mau_sac) === colorKey);
}

export function findVariant(product, colorKey, sizeKey) {
  return variantsOf(product).find(v => optionKey(v.id_mau_sac) === colorKey && optionKey(v.id_kich_thuoc) === sizeKey);
}

export function initialSelection(product, colorKey) {
  const variants = colorKey == null ? variantsOf(product) : sizesFor(product, colorKey);
  const variant = variants.find(v => Number(v.so_luong_ton) > 0) || variants[0];
  return { colorKey: optionKey(variant?.id_mau_sac), sizeKey: optionKey(variant?.id_kich_thuoc) };
}

export function sizeGroups(product, colorKey = null) {
  const variants = colorKey === null ? variantsOf(product) : sizesFor(product, colorKey);
  return [...new Map(variants.map(variant => [optionKey(variant.id_kich_thuoc), variant])).values()];
}

export function imageForColor(product, colorKey) {
  const imageIds = sizesFor(product, colorKey).map(variant => variant.id_anh_san_pham).filter(Boolean).map(String);
  const path = (product?.anh_san_phams || []).find(image => imageIds.includes(String(image.id)))?.duong_dan_anh;
  return isProductImage(path) ? productImageUrl(path) : undefined;
}

export function remainingQuantity(product, variant, items = []) {
  const matching = items.filter(item => {
    if (Number(item.id) !== Number(product.id)) return false;
    if (item.id_chi_tiet_san_pham != null) return Number(item.id_chi_tiet_san_pham) === Number(variant?.id);
    return item.mau_sac === (variant?.mau_sac?.ten_mau || 'Tiêu chuẩn')
      && item.kich_thuoc === (variant?.kich_thuoc?.ten_kich_thuoc || 'Freesize');
  });
  return Math.max(0, availableStock(product, variant) - matching.reduce((sum, item) => sum + Number(item.so_luong || 0), 0));
}

export function discountFor(product, variant) {
  const price = variantPrice(product, variant);
  const original = Math.round((Number(product?.gia_ban || 0) + Number(variant?.gia_chenh_lech || 0)) * 100) / 100;
  const saving = Math.max(0, Math.round((original - price) * 100) / 100);
  return { price, original, saving, percent: original > 0 ? Math.round(saving / original * 100) : 0 };
}

// Tái sử dụng cartStore và luồng toggleCheckout từ trang cũ.
export function purchaseSelection(store, product, variant, quantity, buyNow = false) {
  if (!product || (hasVariants(product) && !variantsOf(product).some(v => v.id === variant?.id))) return false;
  if (!Number.isInteger(quantity) || quantity < 1 || quantity > remainingQuantity(product, variant, store.items)) return false;
  store.addItem({ ...product, gia_ban: variantPrice(product, variant), gia_khuyen_mai: null }, quantity,
    variant?.mau_sac, variant?.kich_thuoc, variant || null);
  if (buyNow) {
    store.toggleDrawer(false);
    store.toggleCheckout(true);
  }
  return true;
}
