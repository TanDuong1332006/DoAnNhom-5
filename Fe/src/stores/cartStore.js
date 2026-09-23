import { reactive } from 'vue'

const CART_KEY = 'fashion_store_cart';

function getLocalCart() {
    try {
        const data = localStorage.getItem(CART_KEY);
        return data ? JSON.parse(data) : [];
    } catch (e) {
        return [];
    }
}

function saveLocalCart(items) {
    try {
        localStorage.setItem(CART_KEY, JSON.stringify(items));
    } catch (e) {}
}

export const cartStore = reactive({
    items: getLocalCart(),
    isOpenDrawer: false,
    isOpenSizeAdvisor: false,
    selectedSizeAdvisorSize: 'L',

    get count() {
        return this.items.reduce((sum, item) => sum + (Number(item.so_luong) || 0), 0);
    },

    get subtotal() {
        return this.items.reduce((sum, item) => sum + ((Number(item.gia) || 0) * (Number(item.so_luong) || 0)), 0);
    },

    addItem(product, quantity = 1, selectedColor = null, selectedSize = null) {
        const gia = product.gia_khuyen_mai ? Number(product.gia_khuyen_mai) : Number(product.gia_ban);
        const colorName = selectedColor ? selectedColor.ten_mau : 'Tiêu chuẩn';
        const sizeName = selectedSize ? selectedSize.ten_kich_thuoc : 'Freesize';
        const itemKey = `${product.id}_${colorName}_${sizeName}`;

        const existing = this.items.find(i => i.itemKey === itemKey);
        if (existing) {
            existing.so_luong += quantity;
        } else {
            this.items.push({
                itemKey,
                id: product.id,
                ten_san_pham: product.ten_san_pham,
                hinh_anh: product.hinh_anh,
                gia: gia,
                gia_goc: Number(product.gia_ban),
                mau_sac: colorName,
                kich_thuoc: sizeName,
                so_luong: quantity,
            });
        }
        saveLocalCart(this.items);
        this.isOpenDrawer = true; // Mở drawer giỏ hàng tự động
    },

    updateQuantity(itemKey, delta) {
        const item = this.items.find(i => i.itemKey === itemKey);
        if (item) {
            item.so_luong += delta;
            if (item.so_luong <= 0) {
                this.removeItem(itemKey);
            } else {
                saveLocalCart(this.items);
            }
        }
    },

    removeItem(itemKey) {
        this.items = this.items.filter(i => i.itemKey !== itemKey);
        saveLocalCart(this.items);
    },

    clearCart() {
        this.items = [];
        saveLocalCart(this.items);
    },

    toggleDrawer(val) {
        this.isOpenDrawer = typeof val === 'boolean' ? val : !this.isOpenDrawer;
    },

    toggleSizeAdvisor(val) {
        this.isOpenSizeAdvisor = typeof val === 'boolean' ? val : !this.isOpenSizeAdvisor;
    }
});
