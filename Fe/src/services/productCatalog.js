export function basePrice(product) {
  const sale = Number(product?.gia_khuyen_mai);
  return sale > 0 && sale <= Number(product?.gia_ban) ? sale : Number(product?.gia_ban || 0);
}

export function variantsOf(product) {
  return (product?.chi_tiet_san_phams || []).filter(v => Number(v.trang_thai) === 1);
}

export function hasVariants(product) {
  return Number(product?.chi_tiet_san_phams_count || 0) > 0 || (product?.chi_tiet_san_phams || []).length > 0;
}

export function variantPrice(product, variant) {
  return Math.round((basePrice(product) + Number(variant?.gia_chenh_lech || 0)) * 100) / 100;
}

export function availableStock(product, variant) {
  return Math.max(0, Number(hasVariants(product) ? variant?.so_luong_ton || 0 : product?.so_luong_ton || 0));
}

export function totalAvailableStock(product) {
  return hasVariants(product)
    ? variantsOf(product).reduce((total, variant) => total + availableStock(product, variant), 0)
    : availableStock(product);
}

export function galleryOf(product) {
  return [...new Set([product?.hinh_anh, ...(product?.anh_san_phams || []).map(a => a.duong_dan_anh)].filter(Boolean))];
}
