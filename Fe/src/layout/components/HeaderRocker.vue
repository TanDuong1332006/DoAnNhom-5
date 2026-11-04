<template>
    <nav class="navbar navbar-expand">
        <div class="topbar-logo-header">
            <SofepLogo size="sm" variant="light" subtitle="ADMIN PORTAL" />
        </div>
        <button type="button" class="mobile-toggle-menu border-0 bg-transparent" aria-label="Mở menu Admin" @click.stop="$emit('toggle-menu')"><i class="bx bx-menu"></i></button>
        
        <!-- 1. KHU VỰC THANH TÌM KIẾM TƯƠNG TÁC -->
        <div class="search-bar flex-grow-1" :class="{ 'full-search-bar': mobileSearchOpen }"><AdminSearch /><button v-if="mobileSearchOpen" type="button" class="btn btn-light ms-2" aria-label="Đóng tìm kiếm" @click="mobileSearchOpen = false">×</button></div>

        <div class="top-menu ms-auto">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item mobile-search-icon">
                    <button type="button" class="nav-link border-0 bg-transparent" aria-label="Mở tìm kiếm Admin" @click="mobileSearchOpen = true"><i class="bx bx-search"></i></button>
                </li>
                
                <!-- Bỏ qua phần menu thông báo (Notifications, Messages...) giữ nguyên như cũ -->
                <!-- ... Bạn có thể chèn lại cụm <li> thông báo của nhóm bạn vào đây nếu cần ... -->
            </ul>
        </div>

        <!-- 2. KHU VỰC TÀI KHOẢN QUẢN TRỊ VIÊN (Sử dụng Vue để đóng/mở) -->
        <div class="user-box dropdown">
            <!-- Xóa data-bs-toggle, thay bằng @click.prevent để Vue tự quản lý -->
            <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" role="button"
                @click.prevent="isUserMenuOpen = !isUserMenuOpen">
                <img src="../../assets/images/avatars/avatar-2.png" class="user-img" alt="user avatar">
                <div class="user-info ps-3">
                    <p class="user-name mb-0 fw-bold">{{ currentUser ? (currentUser.ho_va_ten || currentUser.name || 'Admin') : 'Admin Shop' }}</p>
                    <p class="designattion mb-0 text-muted">{{ currentUser && currentUser.role === 'admin' ? 'Quản Trị Viên' : 'Admin Hệ Thống' }}</p>
                </div>
            </a>
            
            <!-- Thêm :class và :style để ép menu hiển thị khi isUserMenuOpen là true -->
            <ul class="dropdown-menu dropdown-menu-end" :class="{ 'show': isUserMenuOpen }" :style="{ display: isUserMenuOpen ? 'block' : 'none' }">
                <li><router-link class="dropdown-item" to="/admin" @click="isUserMenuOpen = false"><i class="bx bx-home-circle"></i><span>Dashboard</span></router-link></li>
                <li><router-link class="dropdown-item" to="/" @click="isUserMenuOpen = false"><i class="bx bx-store"></i><span>Xem Cửa Hàng</span></router-link></li>
                <li>
                    <div class="dropdown-divider mb-0"></div>
                </li>
                <li><a @click="dangXuat" class="dropdown-item text-danger" href="javascript:;"><i
                            class="bx bx-log-out-circle"></i><span>Đăng Xuất</span></a>
                </li>
            </ul>
        </div>
    </nav>
</template>

<script>
import AdminSearch from './AdminSearch.vue';
import axios from 'axios';
import { adminToken, clearAdminSession } from '../../services/authSessions';
export default {
    name: 'HeaderRocker',
    components: { AdminSearch },
    emits: ['toggle-menu'],
    data() {
        return {
            currentUser: null, mobileSearchOpen: false,
            searchQuery: '', // Biến lưu chữ tìm kiếm
            isUserMenuOpen: false // Biến điều khiển menu thả xuống (Mặc định là đóng)
        }
    },
    watch: { '$route.fullPath'() { this.mobileSearchOpen = false; } },
    mounted() {
        this.loadUser();
    },
    methods: {
        loadUser() {
            try {
                const raw = localStorage.getItem('admin_user') || localStorage.getItem('nguoi_dung') || localStorage.getItem('user');
                if (raw) {
                    this.currentUser = JSON.parse(raw);
                }
            } catch (e) {
                console.error(e);
            }
        },
        async dangXuat() {
            const token = adminToken();
            try { if (token) await axios.post('http://127.0.0.1:8000/api/dang-xuat', {}, { headers: { Authorization: `Bearer ${token}` }, timeout: 8000 }); } catch {}
            this.isUserMenuOpen = false; // Đóng menu lại khi đăng xuất
            clearAdminSession(token);
            if (this.$toast) {
                this.$toast.success('Đã đăng xuất thành công');
            }
            this.$router.push('/dang-nhap');
        },

    }
}
</script>

<style scoped>
/* Không cần thêm CSS phụ vì đã ép style hiển thị menu trực tiếp trên thẻ HTML */
</style>