import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import Default from './layout/wrapper/index.vue'
import Blank from './layout/wrapper/blank.vue'
import ClientLayout from './layout/wrapper/ClientLayout.vue'

import "./assets/css/bootstrap.min.css"
import "./assets/css/icons.css"
import "./assets/css/urbn-wear.css"
import "./assets/js/bootstrap.bundle.min.js"

import Toast, { useToast } from "vue-toastification";
import "vue-toastification/dist/index.css";
import axios from 'axios';
import { CustomSwal, showConfirm, showDeleteConfirm, showAlert } from './utils/dialog';

// Cấu hình axios tự động gắn Bearer token
axios.interceptors.request.use((config) => {
    const token = localStorage.getItem('auth_token') || localStorage.getItem('nguoi_dung_token') || localStorage.getItem('admin_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Gắn dialog đẹp thay thế alert của trình duyệt
window.alert = (msg) => showAlert(msg);
window.$confirm = showConfirm;
window.$confirmDelete = showDeleteConfirm;
window.Swal = CustomSwal;

const app = createApp(App)

app.use(Toast, {
    position: 'top-right',
    timeout: 3000,
    closeOnClick: true,
    pauseOnHover: true,
});
app.use(router);
app.config.globalProperties.$toast = useToast();
app.config.globalProperties.$swal = CustomSwal;
app.config.globalProperties.$confirm = showConfirm;
app.config.globalProperties.$confirmDelete = showDeleteConfirm;
app.config.globalProperties.$alert = showAlert;

import SofepLogo from './components/Common/SofepLogo.vue';

app.component("default-layout", Default);
app.component("blank-layout", Blank);
app.component("client-layout", ClientLayout);
app.component("SofepLogo", SofepLogo);

app.mount("#app")