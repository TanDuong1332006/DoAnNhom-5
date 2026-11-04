<template>
  <div class="urbn-home-container">

    <!-- 1. HERO BANNER SECTION (SOFEP LUXURY ATELIER 2026) -->
    <section class="hero-banner-section position-relative overflow-hidden" style="background: linear-gradient(135deg, #fdfbf7 0%, #fef3c7 40%, #fef9ee 100%); border-bottom: 1px solid #f1ede6;">
      <!-- Background Image with Soft Luminous Gradient -->
      <div class="position-absolute top-0 end-0 h-100 w-100 z-0 opacity-25 d-none d-md-block" style="mix-blend-mode: multiply;">
        <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1800&q=80"
             class="w-100 h-100 object-fit-cover"
             alt="SOFEP Luxury Hero">
      </div>

      <div class="container position-relative z-1 py-5 py-lg-6">
        <div class="row align-items-center min-vh-75 py-4">
          <div class="col-lg-8 col-xl-7">

            <!-- Capsule Tag -->
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 rounded-pill bg-white border shadow-xs small fw-bold mb-3" style="border-color: rgba(180, 131, 37, 0.3) !important; color: #78350f;">
              <i class='bx bxs-crown fs-5' style="color: #b48325;"></i>
              <span>BỘ SƯU TẬP XUÂN HÈ 2026 • SOFEP ATELIER</span>
            </div>

            <!-- Big Bold Luxury Title -->
            <h1 class="display-3 fw-extrabold text-uppercase tracking-tighter lh-1 mb-3 font-streetwear hero-main-title text-dark">
              SOFEP <br>
              <span class="text-gradient-gold">HAUTE ATELIER</span>
            </h1>

            <!-- Subtitle -->
            <p class="lead text-secondary mb-4 max-w-550 fw-normal" style="font-size: 1.05rem; line-height: 1.6;">
              Khám phá sản phẩm thời trang SOFEP và lựa chọn phong cách phù hợp với bạn.
            </p>

            <!-- Action Buttons -->
            <div class="d-flex flex-wrap align-items-center gap-3 pt-2">
              <router-link to="/tim-kiem" class="btn btn-urbn-primary btn-lg shadow-sm">
                <span>Khám Phá Bộ Sưu Tập</span>
                <i class='bx bx-right-arrow-alt fs-4'></i>
              </router-link>

              <button @click="cartStore.toggleSizeAdvisor(true)" class="btn btn-outline-dark rounded-pill px-4 py-2.5 fw-bold">
                <i class='bx bx-ruler me-1' style="color: #b48325;"></i> Bảng Chọn Size
              </button>
            </div>

            <!-- Key Metric Highlights -->


          </div>
        </div>
      </div>
    </section>

    <!-- 2. QUICK CATEGORY CHIPS BAR (5 Contemporary Streetwear Cards) -->
    <section class="quick-categories py-5 bg-white border-bottom">
      <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-4">
          <div>
            <span class="text-urbn-primary fw-bold text-uppercase small tracking-wide">PHÂN LOẠI TRANG PHỤC</span>
            <h2 class="fw-extrabold text-dark text-uppercase font-streetwear mb-0">Danh Mục Nổi Bật</h2>
          </div>
          <router-link to="/tim-kiem" class="btn btn-outline-dark btn-sm rounded-pill px-3">
            Xem Tất Cả ({{ san_phams.length }}) <i class='bx bx-right-arrow-alt'></i>
          </router-link>
        </div>

        <div class="row g-3 row-cols-2 row-cols-md-3 row-cols-lg-5">
          <div v-for="cat in quickCategories" :key="cat.id" class="col">
            <div @click="locTheoTenDanhMuc(cat.name)"
                 class="category-street-card rounded-4 overflow-hidden position-relative cursor-pointer group-cat shadow-sm border">
              <div class="cat-img-box" style="padding-top: 100%;">
                <img v-if="cat.img" :src="cat.img" class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover cat-zoom" :alt="cat.label">
              </div>
              <div class="cat-gradient-overlay position-absolute bottom-0 start-0 end-0 p-3 text-white">
                <h6 class="fw-extrabold mb-0 text-uppercase small font-streetwear">{{ cat.label }}</h6>
                <span class="small text-white-50" style="font-size: 11px;">{{ cat.count }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 3. FLASH SALE COUNTDOWN SECTION (Giờ vàng giá sốc) -->
    <section v-if="flash_sale_products.length" id="flash-sale-section" class="flash-sale-section py-5 bg-light">
      <div class="container">

        <!-- Radiant Banner Card (SOFEP Luxury Sunrise Gold Style) -->


        <h2 class="fw-bold mb-4">Sản phẩm ưu đãi</h2>
        <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-4">
          <div v-for="sp in flash_sale_products" :key="'fs_' + sp.id" class="col">
            <div class="card-urbn h-100 d-flex flex-column position-relative">

              <!-- Sale Badge -->
              <span class="badge-urbn-sale position-absolute top-0 start-0 m-3 z-2">
                -{{ tinhPhanTramGiam(sp.gia_ban, sp.gia_khuyen_mai) }}%
              </span>

              <!-- Aspect Ratio 4:5 Image Box -->
              <div class="position-relative overflow-hidden cursor-pointer" style="padding-top: 120%;" @click="xemChiTiet(sp)">
                <ProductImage :src="sp.hinh_anh"
                     class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover img-hover-zoom"
                     :alt="sp.ten_san_pham" />
                <!-- Action Hover Buttons -->
                <div class="product-hover-overlay position-absolute bottom-0 start-0 end-0 p-2 d-flex justify-content-center gap-2" @click.stop>
                  <button @click="xemChiTiet(sp)" class="btn btn-light btn-sm rounded-circle shadow" title="Xem chi tiết">
                    <i class='bx bx-show fs-5'></i>
                  </button>
                  <button @click="themNhanhVaoGio(sp)" class="btn btn-urbn-dark btn-sm rounded-pill px-3 shadow" title="Thêm vào giỏ">
                    <i class='bx bx-shopping-bag me-1'></i> Thêm Giỏ
                  </button>
                </div>
              </div>

              <!-- Product Info -->
              <div class="p-3 d-flex flex-column flex-grow-1">
                <span class="text-muted small text-uppercase mb-1" style="font-size: 11px;">
                  {{ sp.danh_muc ? sp.danh_muc.ten_danh_muc : 'SOFEP' }}
                </span>
                <h6 class="fw-bold text-dark text-truncate mb-2 cursor-pointer" @click="xemChiTiet(sp)" :title="sp.ten_san_pham">
                  {{ sp.ten_san_pham }}
                </h6>
                <div class="d-flex align-items-baseline gap-2 mt-auto">
                  <span class="fw-extrabold text-urbn-primary fs-5">{{ formatMoney(basePrice(sp)) }}₫</span>
                  <span v-if="sp.gia_khuyen_mai" class="text-muted text-decoration-line-through small">{{ formatMoney(sp.gia_ban) }}₫</span>
                </div>
                <button @click="themNhanhVaoGio(sp)" class="btn btn-dark w-100 btn-sm rounded-pill fw-bold mt-3">
                  <i class='bx bx-cart-add me-1'></i> Thêm Vào Giỏ
                </button>
              </div>

            </div>
          </div>
        </div>

      </div>
    </section>


    <!-- 5. MAIN PRODUCT CATALOG SECTION (Filter Tabs & Grid) -->
    <section id="products-section" class="products-catalog-section py-5">
      <div class="container">

        <!-- Section Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
          <div>
            <span class="text-urbn-primary fw-bold text-uppercase small tracking-wide">BỘ SƯU TẬP THỜI TRANG ĐÔ THỊ</span>
            <h2 class="fw-extrabold text-dark text-uppercase font-streetwear mb-0">Sản Phẩm SOFEP</h2>
          </div>

          <!-- Category Filter Tabs -->
          <div class="d-flex flex-wrap gap-2">
            <button @click="tabActive = 'tat-ca'"
                    :class="{ 'btn-dark': tabActive === 'tat-ca', 'btn-light border': tabActive !== 'tat-ca' }"
                    class="btn btn-sm rounded-pill px-3 fw-bold">
              Tất Cả
            </button>
            <button @click="tabActive = 'hot'"
                    :class="{ 'btn-dark': tabActive === 'hot', 'btn-light border': tabActive !== 'hot' }"
                    class="btn btn-sm rounded-pill px-3 fw-bold">
              <i class='bx bxs-hot text-danger me-1'></i> Nổi Bật
            </button>
            <button v-for="dm in danh_mucs" :key="dm.id"
                    @click="tabActive = dm.id"
                    :class="{ 'btn-dark': tabActive === dm.id, 'btn-light border': tabActive !== dm.id }"
                    class="btn btn-sm rounded-pill px-3 fw-semibold">
              {{ dm.ten_danh_muc }}
            </button>
          </div>
        </div>

        <!-- Search Alert Notification if filtering by keyword -->
        <div v-if="searchKeyword" class="alert alert-light border rounded-4 p-3 mb-4 d-flex justify-content-between align-items-center">
          <div>
            Kết quả tìm kiếm cho từ khóa: <strong>"{{ searchKeyword }}"</strong> ({{ filteredProducts.length }} sản phẩm)
          </div>
          <button @click="searchKeyword = ''; $router.push('/')" class="btn btn-sm btn-outline-secondary rounded-pill">
            Xóa tìm kiếm
          </button>
        </div>

        <div v-if="loading" class="alert alert-light" role="status">Đang tải sản phẩm…</div>
<div v-if="error" class="alert alert-warning" role="alert">{{ error }} <button class="btn btn-sm btn-outline-dark" @click="loadData">Thử lại</button></div>
<div v-if="quickLoading" class="alert alert-light">Đang tải biến thể…</div>
<div v-if="quickError" class="alert alert-warning" role="alert">{{ quickError }}</div>
        <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-4">
          <div v-for="sp in filteredProducts" :key="sp.id" class="col">
            <div class="card-urbn h-100 d-flex flex-column position-relative group-product">

              <!-- Badges -->
              <div class="position-absolute top-0 start-0 m-3 z-2 d-flex flex-column gap-1">
                <span v-if="sp.gia_khuyen_mai" class="badge-urbn-sale">
                  -{{ tinhPhanTramGiam(sp.gia_ban, sp.gia_khuyen_mai) }}%
                </span>
                <span v-if="sp.is_noi_bat" class="badge-urbn-hot">
                  HOT DROP
                </span>
              </div>

              <!-- Product Image Box -->
              <div class="position-relative overflow-hidden cursor-pointer" style="padding-top: 125%;" @click="xemChiTiet(sp)">
                <ProductImage :src="sp.hinh_anh"
                     class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover img-hover-zoom"
                     :alt="sp.ten_san_pham" />

                <!-- Hover Overlay Action Buttons -->
                <div class="product-hover-overlay position-absolute bottom-0 start-0 end-0 p-2 d-flex justify-content-center gap-2" @click.stop>
                  <button @click="xemChiTiet(sp)" class="btn btn-light btn-sm rounded-circle shadow" title="Xem chi tiết">
                    <i class='bx bx-show fs-5'></i>
                  </button>
                  <button @click="themNhanhVaoGio(sp)" class="btn btn-urbn-dark btn-sm rounded-pill px-3 shadow" title="Thêm vào giỏ">
                    <i class='bx bx-shopping-bag me-1'></i> Thêm Giỏ
                  </button>
                </div>
              </div>

              <!-- Product Information -->
              <div class="p-3 d-flex flex-column flex-grow-1">
                <span class="text-muted small text-uppercase mb-1" style="font-size: 11px;">
                  {{ sp.danh_muc ? sp.danh_muc.ten_danh_muc : 'SOFEP' }}
                </span>
                <h6 class="fw-bold text-dark text-truncate mb-2 cursor-pointer" @click="xemChiTiet(sp)" :title="sp.ten_san_pham">
                  {{ sp.ten_san_pham }}
                </h6>

                <!-- Pricing -->
                <div class="d-flex align-items-baseline gap-2 mb-2">
                  <span class="fw-extrabold text-urbn-primary fs-5">{{ formatMoney(basePrice(sp)) }}₫</span>
                  <span v-if="sp.gia_khuyen_mai" class="text-muted text-decoration-line-through small">{{ formatMoney(sp.gia_ban) }}₫</span>
                </div>

                <!-- Sizes & Color swatches preview -->


                <!-- Action Button -->
                <button @click="themNhanhVaoGio(sp)" class="btn btn-dark w-100 btn-sm rounded-pill fw-bold mt-2">
                  <i class='bx bx-bag-plus me-1'></i> Thêm Vào Giỏ
                </button>
              </div>

            </div>
          </div>
        </div>

        <!-- Empty State if no product found -->
        <div v-if="!loading && !error && filteredProducts.length === 0" class="text-center py-5">
          <i class='bx bx-search-alt text-muted' style="font-size: 3rem;"></i>
          <h5 class="fw-bold mt-3">Không tìm thấy sản phẩm phù hợp</h5>
          <p class="text-secondary small">Vui lòng thử chọn danh mục khác hoặc kiểm tra lại từ khóa tìm kiếm.</p>
          <button @click="tabActive = 'tat-ca'; searchKeyword = ''" class="btn btn-urbn-primary btn-sm rounded-pill px-4">
            Xem Tất Cả Sản Phẩm
          </button>
        </div>

      </div>
    </section>

    <!-- 6. STREETWEAR HIGHLIGHTS & BRAND VALUES -->


    <!-- 7. COMMUNITY REVIEWS SECTION -->


    <!-- 8. BRAND PARTNERS TICKER -->


    <!-- 9. QUICK VIEW PRODUCT MODAL -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-2xl overflow-hidden" v-if="selectedProduct">
          <div class="modal-header border-0 pb-0">
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4 pt-0">
            <div class="row g-4 align-items-center">
              <div class="col-md-6 text-center">
                <ProductImage :src="quickColorImage || selectedProduct.hinh_anh" class="img-fluid rounded-4 shadow-sm" style="max-height: 420px; object-fit: cover;" :alt="selectedProduct.ten_san_pham" />
              </div>
              <div class="col-md-6">
                <span class="badge bg-urbn-primary bg-opacity-10 text-urbn-primary rounded-pill px-3 py-1 small fw-bold mb-2">
                  {{ selectedProduct.danh_muc ? selectedProduct.danh_muc.ten_danh_muc : 'Streetwear Drop' }}
                </span>
                <h4 class="fw-extrabold text-dark mb-2 font-streetwear">{{ selectedProduct.ten_san_pham }}</h4>
                <div class="d-flex align-items-baseline gap-2 mb-3">
                  <span class="text-urbn-primary fw-extrabold fs-4">{{ formatMoney(basePrice(selectedProduct)) }}₫</span>
                  <span v-if="selectedProduct.gia_khuyen_mai" class="text-muted text-decoration-line-through small">{{ formatMoney(selectedProduct.gia_ban) }}₫</span>
                </div>
                <p class="text-secondary small mb-3">
                  {{ selectedProduct.mo_ta_ngan || 'Chưa có mô tả ngắn.' }}
                </p>

<ProductPurchase :key="selectedProduct.id" :product="selectedProduct" @color-image="quickColorImage = $event" />

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import { cartStore } from '../../stores/cartStore';
import axios from 'axios';
import ProductImage from '../shared/ProductImage.vue';
import ProductPurchase from './ProductPurchase.vue';
import { basePrice } from '../../services/productCatalog';
export default {
 name: 'ClientTrangChu', components: { ProductPurchase, ProductImage },
 data() { return { cartStore, danh_mucs: [], san_phams: [], tabActive: 'tat-ca', searchKeyword: '', selectedProduct: null, loading: false, error: '', quickError: '', quickLoading: false, quickRequest: 0, quickColorImage: null }; },
 computed: {
  quickCategories() { return this.danh_mucs.map(d => ({ id:d.id, name:d.ten_danh_muc, label:d.ten_danh_muc, count:(d.san_phams_count || 0)+' sản phẩm', img:d.hinh_anh })); },
  flash_sale_products() { return this.san_phams.filter(p => Number(p.gia_khuyen_mai)>0 && Number(p.gia_khuyen_mai)<Number(p.gia_ban)).slice(0,4); },
  filteredProducts() { let list=this.san_phams.filter(p=>!this.searchKeyword || p.ten_san_pham.toLowerCase().includes(this.searchKeyword.toLowerCase())); if(this.tabActive==='hot')return list.filter(p=>Number(p.is_noi_bat)===1); return this.tabActive==='tat-ca'?list:list.filter(p=>p.id_danh_muc==this.tabActive); }
 },
 watch: {
  '$route.query.danh_muc': { immediate:true, handler(value){this.tabActive=value || 'tat-ca';} },
  '$route.query.q': { immediate:true, handler(value){this.searchKeyword=value || '';} }
 },
 mounted(){this.loadData();}, beforeUnmount(){this.quickRequest++;},
 methods: {
  basePrice,
  formatMoney(value){return Number(value || 0).toLocaleString('vi-VN');},
  tinhPhanTramGiam(base,sale){return Number(base)>Number(sale)&&Number(sale)>0?Math.round((base-sale)/base*100):0;},
  async loadData(){
   this.loading=true;this.error='';this.san_phams=[];this.danh_mucs=[];
   const responses=await Promise.allSettled([axios.get('http://127.0.0.1:8000/api/client/danh-mucs'),axios.get('http://127.0.0.1:8000/api/client/san-phams?all=1')]);
   if(responses[0].status==='fulfilled'&&responses[0].value.data.status)this.danh_mucs=responses[0].value.data.data;else this.error='Không thể tải danh mục. Vui lòng thử lại.';
   if(responses[1].status==='fulfilled'&&responses[1].value.data.status&&Array.isArray(responses[1].value.data.data))this.san_phams=responses[1].value.data.data;else this.error='Không thể tải sản phẩm. Vui lòng thử lại.';
   this.loading=false;
  },
  locTheoTenDanhMuc(name){const c=this.danh_mucs.find(d=>d.ten_danh_muc===name);this.$router.push({path:'/tim-kiem',query:c?{danh_muc:c.id}:{q:name}});},
  xemChiTiet(p){this.$router.push('/san-pham/'+(p.slug_san_pham||p.id));},
  themNhanhVaoGio(p){return this.moQuickView(p);},
  async moQuickView(p){
   const request=++this.quickRequest;this.selectedProduct=null;this.quickLoading=true;this.quickError='';this.quickColorImage=null;
   try{
    const res=await axios.get('http://127.0.0.1:8000/api/client/san-pham/'+encodeURIComponent(p.slug_san_pham||p.id));
    if(request!==this.quickRequest)return;
    if(!res.data.status||!res.data.data?.san_pham)throw new Error('Invalid response');
    this.selectedProduct=res.data.data.san_pham;await this.$nextTick();
    const element=document.getElementById('quickViewModal');
    if(element&&window.bootstrap)window.bootstrap.Modal.getOrCreateInstance(element).show();
   }catch(error){if(request===this.quickRequest)this.quickError='Không thể tải sản phẩm để xem nhanh. Vui lòng thử lại.';}
   finally{if(request===this.quickRequest)this.quickLoading=false;}
  }
 }
};
</script>

<style scoped>
.hero-banner-section {
  min-height: 80vh;
}
.filter-hero {
  filter: brightness(0.65) contrast(1.15);
}
.hero-gradient-overlay {
  background: linear-gradient(90deg, rgba(10, 13, 18, 0.95) 0%, rgba(10, 13, 18, 0.75) 50%, rgba(10, 13, 18, 0.3) 100%);
}
.backdrop-blur {
  backdrop-filter: blur(8px);
}
.text-gradient-electric {
  background: linear-gradient(90deg, #ffffff 0%, #cbd5e1 40%, #002fda 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.text-gradient-gold {
  background: linear-gradient(135deg, #b45309 0%, #d97706 45%, #b48325 75%, #f59e0b 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.max-w-550 {
  max-width: 550px;
}
.max-w-600 {
  max-width: 600px;
}
.cat-img-box {
  background-color: #f1f3f5;
  transition: transform 0.4s ease;
}
.cat-zoom {
  transition: transform 0.4s ease;
}
.group-cat:hover .cat-zoom {
  transform: scale(1.08);
}
.cat-gradient-overlay {
  background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0) 100%);
}
.img-hover-zoom {
  transition: transform 0.4s ease;
}
.group-product:hover .img-hover-zoom {
  transform: scale(1.06);
}
.product-hover-overlay {
  background: linear-gradient(to top, rgba(0,0,0,0.4) 0%, transparent 100%);
  opacity: 0;
  transform: translateY(10px);
  transition: all 0.25s ease;
}
.group-product:hover .product-hover-overlay {
  opacity: 1;
  transform: translateY(0);
}
.hover-white:hover {
  color: #ffffff !important;
}
</style>
