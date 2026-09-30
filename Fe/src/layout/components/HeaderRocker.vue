<template>
    <nav class="navbar navbar-expand">
        <div class="topbar-logo-header">
            <SofepLogo size="sm" variant="light" subtitle="ADMIN PORTAL" />
        </div>
        <div class="mobile-toggle-menu"><i class="bx bx-menu"></i></div>
        
        <!-- 1. KHU VỰC THANH TÌM KIẾM TƯƠNG TÁC -->
        <div class="search-bar flex-grow-1">
            <div class="position-relative search-bar-box">
                <input 
                    type="text" 
                    class="form-control search-control" 
                    placeholder="Type to search..."
                    v-model="searchQuery"
                    @keyup.enter="handleSearch"
                >
                <span class="position-absolute top-50 search-show translate-middle-y" @click="handleSearch" style="cursor: pointer;">
                    <i class="bx bx-search"></i>
                </span>
                <span class="position-absolute top-50 search-close translate-middle-y" @click="clearSearch" style="cursor: pointer;">
                    <i class="bx bx-x"></i>
                </span>
            </div>
        </div>
        
        <div class="top-menu ms-auto">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item mobile-search-icon">
                    <a class="nav-link" href="#"> <i class="bx bx-search"></i>
                    </a>
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
export default {
    name: 'HeaderRocker',
    data() {
        return {
            currentUser: null,
            searchQuery: '', // Biến lưu chữ tìm kiếm
            isUserMenuOpen: false // Biến điều khiển menu thả xuống (Mặc định là đóng)
        }
    },
    mounted() {
        this.loadUser();
    },
    methods: {
        loadUser() {
            try {
                const raw = localStorage.getItem('nguoi_dung') || localStorage.getItem('user');
                if (raw) {
                    this.currentUser = JSON.parse(raw);
                }
            } catch (e) {
                console.error(e);
            }
        },
        dangXuat() {
            this.isUserMenuOpen = false; // Đóng menu lại khi đăng xuất
            localStorage.removeItem('admin_token');
            localStorage.removeItem('auth_token');
            localStorage.removeItem('nguoi_dung_token');
            localStorage.removeItem('user');
            localStorage.removeItem('nguoi_dung');
            if (this.$toast) {
                this.$toast.success('Đã đăng xuất thành công');
            }
            this.$router.push('/dang-nhap');
        },
        handleSearch() {
            if (!this.searchQuery.trim()) {
                alert('Vui lòng nhập từ khóa tìm kiếm vào ô trống!');
                return;
            }
            
            // Hiển thị một bảng thông báo ngay giữa màn hình để bạn thấy nó hoạt động
            alert('Hệ thống đang tìm kiếm từ khóa: ' + this.searchQuery);
            
            // Ghi chú: Sau này khi nhóm bạn có trang hiển thị kết quả tìm kiếm, 
            // hãy xóa dòng alert() phía trên và bỏ dấu // ở dòng code phía dưới đi:
            // this.$router.push({ path: '/admin/tim-kiem', query: { q: this.searchQuery } });
        },
        clearSearch() {
            this.searchQuery = '';
        }
    }
}
</script>

<style scoped>
/* Không cần thêm CSS phụ vì đã ép style hiển thị menu trực tiếp trên thẻ HTML */
</style>