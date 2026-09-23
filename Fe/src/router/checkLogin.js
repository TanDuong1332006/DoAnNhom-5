import axios from "axios";
import { useToast } from "vue-toastification";

export default function checkLogin(to, from, next) {
    const toast = useToast();
    const token = localStorage.getItem("auth_token") || localStorage.getItem("nguoi_dung_token") || localStorage.getItem("admin_token");

    if (!token) {
        toast.error("Vui lòng đăng nhập quyền Quản trị viên để truy cập!");
        next("/dang-nhap");
        return;
    }

    axios.get("http://127.0.0.1:8000/api/kiem-tra-dang-nhap", {
        headers: {
            "Authorization": `Bearer ${token}`
        }
    }).then((res) => {
        if (res.data.status === 1) {
            // Cập nhật lại thông tin người dùng vào localStorage
            if (res.data.nguoi_dung) {
                localStorage.setItem("user", JSON.stringify(res.data.nguoi_dung));
                localStorage.setItem("nguoi_dung", JSON.stringify(res.data.nguoi_dung));
            }

            // Kiểm tra phân quyền admin
            if (res.data.nguoi_dung && res.data.nguoi_dung.role === 'admin') {
                next();
            } else {
                toast.warning("Tài khoản của bạn không có quyền truy cập khu vực Quản Trị!");
                next("/");
            }
        } else {
            localStorage.removeItem("auth_token");
            localStorage.removeItem("nguoi_dung_token");
            localStorage.removeItem("admin_token");
            localStorage.removeItem("user");
            localStorage.removeItem("nguoi_dung");
            toast.error("Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại!");
            next("/dang-nhap");
        }
    }).catch(() => {
        // Fallback: nếu backend có lỗi mạng nhưng có lưu role admin thì cho qua hoặc redirect
        const cachedUser = localStorage.getItem("user");
        if (cachedUser) {
            try {
                const u = JSON.parse(cachedUser);
                if (u.role === 'admin') {
                    next();
                    return;
                }
            } catch (e) {}
        }
        toast.error("Không thể xác thực quyền truy cập!");
        next("/dang-nhap");
    });
}
