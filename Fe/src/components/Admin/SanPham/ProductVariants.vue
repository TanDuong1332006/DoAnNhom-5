<template>
  <section class="border rounded p-3 mt-3" aria-label="Màu sắc, size và tồn kho">
    <h6>Màu sắc, size và tồn kho</h6>
    <p class="small text-muted">Mỗi dòng là một tổ hợp có tồn kho riêng. Có thể để trống màu hoặc size nếu sản phẩm không dùng lựa chọn đó.</p>
    <div v-if="error" class="alert alert-warning py-2" role="alert">{{ error }}</div>
    <button v-if="!ready && !busy" type="button" class="btn btn-outline-dark btn-sm" @click="load">Tải cấu hình</button>
    <fieldset v-if="ready" :disabled="busy">
      <div class="row g-3">
        <label class="col-sm-6">Màu sắc
          <select v-model="draft.id_mau_sac" class="form-select" @change="resetColorDraft">
            <option :value="null">Không phân màu</option>
            <option v-for="color in colors" :key="color.id" :value="color.id" :disabled="Number(color.trang_thai) !== 1">{{ color.ten_mau }} · {{ color.ma_mau }}</option>
          </select>
        </label>
        <label v-if="draft.id" class="col-sm-6">Size / Quy cách
          <select v-model="draft.id_kich_thuoc" class="form-select">
            <option :value="null">Không phân size</option>
            <option v-for="size in sizes" :key="size.id" :value="size.id" :disabled="Number(size.trang_thai) !== 1">{{ size.ten_kich_thuoc }}</option>
          </select>
        </label>
        <fieldset v-else class="col-sm-6">
          <legend class="fs-6 mb-1">Size cho màu này</legend>
          <div class="d-flex flex-wrap gap-2">
            <label v-for="size in availableSizes" :key="size.id" class="border rounded px-2 py-1">
              <input v-model="selectedSizes" type="checkbox" :value="size.id"> {{ size.ten_kich_thuoc }}
            </label>
          </div>
          <small class="text-muted">Chọn nhiều size. Để trống nếu màu này bán theo một quy cách chung.</small>
        </fieldset>
        <div class="col-sm-6">
          <label class="w-100">Tên màu mới<input v-model.trim="colorName" class="form-control" maxlength="100" placeholder="VD: Vàng champagne"></label>
          <div class="d-flex align-items-center gap-2 mt-2">
            <input v-model="hex" type="color" aria-label="Chọn mã màu" class="form-control form-control-color">
            <button type="button" class="btn btn-outline-dark btn-sm" :disabled="!colorName" @click="addOption('color')">Tạo màu</button>
          </div>
        </div>
        <div class="col-sm-6">
          <label class="w-100">Size tự nhập<input v-model.trim="sizeName" class="form-control" maxlength="50" placeholder="VD: 42 hoặc 6 × 8 cm"></label>
          <button type="button" class="btn btn-outline-dark btn-sm mt-2" :disabled="!sizeName" @click="addOption('size')">Tạo size</button>
        </div>
        <label v-if="draft.id_mau_sac && canSaveColorImage" class="col-12">Ảnh đại diện của màu này
          <select v-model="draft.id_anh_san_pham" class="form-select">
            <option :value="undefined">Giữ ảnh đang gắn</option>
            <option v-if="rows.some(row => row.id_mau_sac === draft.id_mau_sac && row.id_anh_san_pham)" :value="null">Gỡ ảnh khỏi màu này</option>
            <option v-for="(image, index) in product.anh_san_phams || []" :key="image.id" :value="image.id">Ảnh thư viện {{ index + 1 }}</option>
          </select>
          <small v-if="!product.anh_san_phams?.length" class="text-muted">Thêm ảnh vào thư viện của sản phẩm để có ảnh cho màu.</small>
          <ProductImage v-if="selectedImage" :src="selectedImage.duong_dan_anh" alt="Xem trước ảnh của màu" class="d-block mt-2" style="width:72px;height:72px;object-fit:contain" />
        </label>
        <p v-else-if="draft.id_mau_sac" class="col-12 small text-muted mb-0">Lưu ảnh theo màu sẽ khả dụng sau khi cập nhật database. Bạn vẫn có thể lưu size và tồn kho.</p>
        <div v-if="!draft.id && selectedSizes.length" class="col-12">
          <div class="row g-2">
            <label v-for="id in selectedSizes" :key="id" class="col-6 col-sm-4">
              Tồn kho · {{ sizes.find(size => size.id === id)?.ten_kich_thuoc }}
              <input v-model.number="stockBySize[id]" type="number" min="0" step="1" placeholder="0" class="form-control">
            </label>
          </div>
        </div>
        <label v-else class="col-sm-4">Tồn kho<input v-model.number="draft.so_luong_ton" type="number" min="0" step="1" class="form-control"></label>
        <label class="col-sm-4">Chênh lệch giá<input v-model.number="draft.gia_chenh_lech" type="number" step="0.01" class="form-control"></label>
        <label class="col-sm-4">Trạng thái<select v-model="draft.trang_thai" class="form-select"><option :value="1">Đang bán</option><option :value="0">Ngừng bán</option></select></label>
      </div>
      <div class="d-flex gap-2 mt-3">
        <button type="button" class="btn btn-primary btn-sm" :disabled="!draft.id_mau_sac && !selectedSizes.length && !draft.id" @click="save">{{ draft.id ? 'Lưu biến thể' : selectedSizes.length > 1 ? 'Thêm các size đã chọn' : 'Thêm tổ hợp' }}</button>
        <button v-if="draft.id" type="button" class="btn btn-outline-secondary btn-sm" @click="draft = emptyDraft()">Tạo tổ hợp khác</button>
      </div>
      <div v-if="rows.length" class="table-responsive mt-3">
        <table class="table table-sm align-middle">
          <thead><tr><th>Màu</th><th>Size</th><th>Tồn</th><th>Trạng thái</th><th></th></tr></thead>
          <tbody><tr v-for="row in rows" :key="row.id">
            <td>{{ row.mau_sac?.ten_mau || '—' }}</td><td>{{ row.kich_thuoc?.ten_kich_thuoc || '—' }}</td>
            <td>{{ row.so_luong_ton }}</td><td>{{ Number(row.trang_thai) === 1 ? 'Đang bán' : 'Ngừng bán' }}</td>
            <td><div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-dark btn-sm" @click="edit(row)">Sửa</button>
              <button type="button" class="btn btn-outline-danger btn-sm" @click="pendingDelete = row">Xóa</button>
            </div></td>
          </tr></tbody>
        </table>
      </div>
      <div v-if="pendingDelete" class="alert alert-warning mt-3 mb-0">
        <p class="mb-2">Xóa tổ hợp {{ pendingDelete.mau_sac?.ten_mau || 'Không phân màu' }} / {{ pendingDelete.kich_thuoc?.ten_kich_thuoc || 'Không phân size' }}?</p>
        <button type="button" class="btn btn-danger btn-sm me-2" @click="remove">Xác nhận xóa</button>
        <button type="button" class="btn btn-outline-dark btn-sm" @click="pendingDelete = null">Hủy</button>
      </div>
    </fieldset>
    <p v-if="busy" role="status" class="small mt-2 mb-0">Đang xử lý…</p>
  </section>
</template>

<script>
import axios from 'axios';
import ProductImage from '../../shared/ProductImage.vue';

const api = 'http://127.0.0.1:8000/api/admin/';
const emptyDraft = () => ({ id_mau_sac: null, id_kich_thuoc: null, id_anh_san_pham: undefined, so_luong_ton: 0, gia_chenh_lech: 0, trang_thai: 1 });

export default {
  components: { ProductImage },
  props: { product: { type: Object, required: true } },
  emits: ['changed'],
  data() {
    return { colors: [], sizes: [], rows: [], draft: emptyDraft(), selectedSizes: [], stockBySize: {}, pendingDelete: null, canSaveColorImage: false, colorName: '', sizeName: '', hex: '#b99255', busy: false, ready: false, error: '' };
  },
  computed: {
    availableSizes() {
      return this.sizes.filter(size => Number(size.trang_thai) === 1 && !this.rows.some(row =>
        row.id_mau_sac === this.draft.id_mau_sac && row.id_kich_thuoc === size.id));
    },
    selectedImage() {
      const current = this.rows.find(row => row.id_mau_sac === this.draft.id_mau_sac && row.id_anh_san_pham);
      const id = this.draft.id_anh_san_pham === undefined ? current?.id_anh_san_pham : this.draft.id_anh_san_pham;
      return (this.product.anh_san_phams || []).find(image => image.id === id);
    }
  },
  mounted() { this.load(); },
  methods: {
    emptyDraft,
    resetColorDraft() {
      this.draft.id_anh_san_pham = undefined;
      this.selectedSizes = [];
      this.stockBySize = {};
    },
    async perform(action) {
      if (this.busy) return;
      this.busy = true;
      this.error = '';
      try { await action(); }
      catch (error) {
        this.error = Object.values(error.response?.data?.errors || {}).flat()[0] || error.response?.data?.message || 'Không thể hoàn tất thao tác. Vui lòng thử lại.';
      } finally { this.busy = false; }
    },
    async refreshRows() {
      const { data } = await axios.get(api + 'chi-tiet-san-pham/get-data', { params: { id_san_pham: this.product.id } });
      this.rows = data.data;
      this.canSaveColorImage = data.supports_color_image === true;
    },
    load() {
      return this.perform(async () => {
        const [colors, sizes] = await Promise.all([
          axios.get(api + 'mau-sac/get-data'), axios.get(api + 'kich-thuoc/get-data'), this.refreshRows()
        ]);
        this.colors = colors.data.data;
        this.sizes = sizes.data.data;
        this.ready = true;
      });
    },
    addOption(kind) {
      return this.perform(async () => {
        const color = kind === 'color';
        const endpoint = color ? 'mau-sac' : 'kich-thuoc';
        const field = color ? 'ten_mau' : 'ten_kich_thuoc';
        const name = color ? this.colorName : this.sizeName;
        const matches = item => item[field].trim().toLowerCase() === name.toLowerCase()
          && (!color || item.ma_mau?.toLowerCase() === this.hex.toLowerCase());
        let option = (color ? this.colors : this.sizes).find(item => matches(item) && Number(item.trang_thai) === 1);
        if (!option) {
          await axios.post(api + endpoint + '/create', { [field]: name, ...(color ? { ma_mau: this.hex } : {}) });
          const { data } = await axios.get(api + endpoint + '/get-data');
          this[color ? 'colors' : 'sizes'] = data.data;
          option = data.data.find(item => matches(item) && Number(item.trang_thai) === 1);
        }
        if (!option) throw new Error('Option missing');
        if (color) {
          this.draft.id_mau_sac = option.id;
          this.resetColorDraft();
          this.colorName = '';
        } else {
          if (this.draft.id) this.draft.id_kich_thuoc = option.id;
          else if (this.availableSizes.some(size => size.id === option.id) && !this.selectedSizes.includes(option.id)) this.selectedSizes.push(option.id);
          this.sizeName = '';
        }
      });
    },
    edit(row) {
      this.selectedSizes = [];
      this.stockBySize = {};
      this.draft = {
        id: row.id, id_mau_sac: row.id_mau_sac, id_kich_thuoc: row.id_kich_thuoc, id_anh_san_pham: undefined,
        so_luong_ton: Number(row.so_luong_ton), gia_chenh_lech: Number(row.gia_chenh_lech), trang_thai: Number(row.trang_thai)
      };
    },
    save() {
      return this.perform(async () => {
        const draft = { ...this.draft, id_san_pham: this.product.id };
        if (!this.canSaveColorImage) delete draft.id_anh_san_pham;
        if (draft.id) {
          await axios.post(api + 'chi-tiet-san-pham/update', draft);
        } else {
          const ids = this.selectedSizes.length ? [...this.selectedSizes] : [null];
          if (ids.some(id => !Number.isInteger(id === null ? draft.so_luong_ton : this.stockBySize[id] ?? 0)
            || (id === null ? draft.so_luong_ton : this.stockBySize[id] ?? 0) < 0)) {
            this.error = 'Tồn kho mỗi size phải là số nguyên từ 0 trở lên.';
            return;
          }
          try {
            for (const id of ids) {
              await axios.post(api + 'chi-tiet-san-pham/create', {
                ...draft, id_kich_thuoc: id, so_luong_ton: id === null ? draft.so_luong_ton : this.stockBySize[id] ?? 0
              });
              this.selectedSizes = this.selectedSizes.filter(size => size !== id);
            }
          } finally {
            await this.reloadAfterChange();
          }
        }
        this.draft = emptyDraft();
        this.stockBySize = {};
        this.$toast?.success('Đã lưu biến thể.');
        if (draft.id) await this.reloadAfterChange();
      });
    },
    async reloadAfterChange() {
      this.$emit('changed');
      try { await this.refreshRows(); }
      catch (error) { this.ready = false; throw error; }
    },
    remove() {
      return this.perform(async () => {
        const id = this.pendingDelete.id;
        const { data } = await axios.post(api + 'chi-tiet-san-pham/destroy', { id });
        this.pendingDelete = null;
        if (this.draft.id === id) this.draft = emptyDraft();
        this.$toast?.success(data.message);
        await this.reloadAfterChange();
      });
    }
  }
};
</script>
