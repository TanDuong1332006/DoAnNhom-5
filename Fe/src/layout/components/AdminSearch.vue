<template>
  <div class="admin-search position-relative" @focusout="closeOnBlur">
    <input v-model="query" class="form-control" placeholder="Tìm chức năng, tên sản phẩm, SKU…" aria-label="Tìm kiếm Admin"
      role="combobox" aria-autocomplete="list" aria-controls="admin-search-results" :aria-expanded="open" :aria-activedescendant="open && active >= 0 ? `admin-result-${active}` : undefined"
      @focus="open = true" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter.prevent="choose(results[active] || results[0])" @keydown.esc="open = false" />
    <div v-if="open && query.trim()" id="admin-search-results" class="results shadow" role="listbox" aria-label="Kết quả tìm kiếm">
      <div v-if="loading" class="p-2 text-muted" role="status">Đang tìm sản phẩm…</div>
      <div v-if="error" class="p-2 text-danger" role="status">{{ error }}</div>
      <button v-for="(result, index) in results" :id="`admin-result-${index}`" :key="`${result.path}-${result.label}`" type="button" role="option" :aria-selected="index === active" :class="{ selected: index === active }" @mousedown.prevent @click="choose(result)">{{ result.label }}</button>
      <div v-if="!loading && !results.length" class="p-2 text-muted" role="status">Không có kết quả phù hợp.</div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { searchSections, matchesSearch } from '../../services/adminSearch';
export default {
  data: () => ({ query: '', products: [], open: false, active: -1, loading: false, error: '', timer: null, controller: null, loadedAt: 0, generation: 0 }),
  computed: {
    results() {
      return [...searchSections(this.query), ...(this.query.trim().length >= 2 ? this.products.filter(p => matchesSearch(`${p.ten_san_pham} ${p.ma_san_pham || ''}`, this.query)).slice(0, 6).map(p => ({ label: `${p.ten_san_pham} · ${p.ma_san_pham || p.id}`, path: '/admin/san-pham', query: { q: p.ma_san_pham || p.ten_san_pham } })) : [])];
    },
  },
  watch: {
    query() {
      this.active = -1; this.open = true; this.error = ''; clearTimeout(this.timer);
      this.controller?.abort(); this.generation++; this.loading = false;
      if (this.query.trim().length < 2 || Date.now() - this.loadedAt < 60000) return;
      this.timer = setTimeout(this.loadProducts, 300);
    },
    '$route.fullPath'() { this.open = false; },
  },
  beforeUnmount() { clearTimeout(this.timer); this.controller?.abort(); this.generation++; },
  methods: {
    async loadProducts() {
      const generation = this.generation;
      this.controller = new AbortController(); this.loading = true;
      try {
        const { data } = await axios.get('http://127.0.0.1:8000/api/admin/san-pham/get-data?search_options=1', { signal: this.controller.signal });
        if (generation !== this.generation) return;
        this.products = data.data || []; this.loadedAt = Date.now();
      } catch (error) {
        if (generation === this.generation && error.code !== 'ERR_CANCELED') { this.products = []; this.error = 'Không thể tải sản phẩm. Vui lòng thử lại.'; }
      } finally { if (generation === this.generation) this.loading = false; }
    },
    move(delta) { this.open = true; if (this.results.length) this.active = (this.active + delta + this.results.length) % this.results.length; },
    choose(result) { if (!result) return; this.open = false; this.$router.push({ path: result.path, query: result.query }); },
    closeOnBlur(event) { if (!event.currentTarget.contains(event.relatedTarget)) this.open = false; },
  },
};
</script>

<style scoped>
.admin-search { width: 100%; max-width: 560px; }
.results { position: absolute; inset: 100% 0 auto; z-index: 1200; background: white; border: 1px solid #ddd; border-radius: 8px; max-height: 340px; overflow-y: auto; }
.results button { display: block; width: 100%; border: 0; background: white; text-align: left; padding: 10px 14px; color: #222; }
.results button:hover, .results button.selected { background: #f2f2f2; }
</style>
