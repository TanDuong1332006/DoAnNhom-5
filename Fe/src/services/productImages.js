import { galleryOf } from './productCatalog.js';
export const imagePlaceholder = '/product-placeholder.svg';
export function isProductImage(value) {
  if (!value || /[<>\x00-\x1f]/.test(value)) return false;
  try { const url = new URL(value, 'http://localhost'); return ['http:', 'https:'].includes(url.protocol) && !/(^|\.)unsplash\.com$/i.test(url.hostname); }
  catch { return false; }
}
export function productImageUrl(value, apiBase = import.meta.env?.VITE_API_URL || 'http://127.0.0.1:8000/api') {
  if (!isProductImage(value)) return imagePlaceholder;
  if (/^\/(?:storage|api\/product-images)\//.test(value)) return new URL(value, apiBase).href;
  return value;
}
export function productImageGallery(product) { return galleryOf(product).filter(isProductImage).map(value => productImageUrl(value)); }

export function isIllustrativeImage(value) { return /\/illustration-[a-f0-9-]{36}\.(?:jpe?g|png|webp)(?:[?#]|$)/i.test(value || ''); }
