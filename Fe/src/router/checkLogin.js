import axios from "axios";
import { adminToken, clearAdminSession } from "../services/authSessions";
import { useToast } from "vue-toastification";

export default function checkLogin(to, from, next) {
    const toast = useToast();
    const token = adminToken();

    if (!token) {
        toast.error("Vui lòng đăng nhập quyền Quản trị viên để truy cập!");
        next("/dang-nhap");
        return;
    }

    return axios.get("http://127.0.0.1:8000/api/kiem-tra-dang-nhap", {
        headers: {
            "Authorization": `Bearer ${token}`
        }
    }).then((res) => {
        if (res.data.status === 1) {
            // Cập nhật lại thông tin người dùng vào localStorage
            if (res.data.nguoi_dung) {
                localStorage.setItem("admin_user", JSON.stringify(res.data.nguoi_dung));
            }

            // Kiểm tra phân quyền admin
            if (res.data.nguoi_dung && res.data.nguoi_dung.role === 'admin') {
                next();
            } else {
                toast.warning("Tài khoản của bạn không có quyền truy cập khu vực Quản Trị!");
                next("/");
            }
        } else {
            clearAdminSession(token);
            toast.error("Phiên đăng nhập đã hết hạn, vui lòng đăng nhập lại!");
            next("/dang-nhap");
        }
    }).catch(() => {
        clearAdminSession(token);
        toast.error("Không thể xác thực quyền truy cập!");
        next("/dang-nhap");
    });
}
