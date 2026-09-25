import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

/**
 * Cấu hình giao diện SweetAlert2 cao cấp chuẩn URBN WEAR / Shopee
 */
export const CustomSwal = Swal.mixin({
  customClass: {
    popup: 'urbn-swal-popup rounded-4 shadow-2xl border-0 p-4',
    title: 'urbn-swal-title fw-bold text-dark fs-5 mb-2 font-streetwear',
    htmlContainer: 'urbn-swal-html text-secondary mb-3',
    confirmButton: 'btn btn-danger px-4 py-2.5 rounded-pill fw-bold shadow-sm mx-1.5',
    cancelButton: 'btn btn-light px-4 py-2.5 rounded-pill fw-semibold border mx-1.5 text-dark',
    actions: 'd-flex justify-content-center gap-2 mt-3 w-100',
  },
  buttonsStyling: false,
  backdrop: 'rgba(15, 23, 42, 0.65)'
});

/**
 * Dialog xác nhận xóa dữ liệu hiện đại thay thế confirm() trình duyệt
 * @param {string} text Nội dung cảnh báo (VD: "Bạn có chắc muốn xóa mã giảm giá này?")
 * @param {string} title Tiêu đề dialog
 * @returns {Promise<boolean>}
 */
export const showDeleteConfirm = (
  text = 'Bạn có chắc chắn muốn xóa mục này? Thao tác này không thể hoàn tác.',
  title = 'Xác Nhận Xóa'
) => {
  return CustomSwal.fire({
    title: title,
    html: `<div class="py-1 text-secondary" style="font-size: 14.5px; line-height: 1.5;">${text}</div>`,
    icon: 'warning',
    iconColor: '#ee4d2d',
    showCancelButton: true,
    confirmButtonText: '<i class="bx bx-trash me-1 align-middle"></i> Đồng Ý Xóa',
    cancelButtonText: '<i class="bx bx-x me-1 align-middle"></i> Hủy Bỏ',
    reverseButtons: true,
    focusCancel: true
  }).then(result => result.isConfirmed);
};

/**
 * Dialog xác nhận chung cho mọi hành động
 * @param {string} text
 * @param {string} title
 * @param {'warning'|'question'|'info'|'error'} icon
 * @returns {Promise<boolean>}
 */
export const showConfirm = (
  text = 'Bạn có chắc chắn muốn thực hiện thao tác này?',
  title = 'Xác Nhận Thao Tác',
  icon = 'question'
) => {
  return CustomSwal.fire({
    title: title,
    html: `<div class="py-1 text-secondary" style="font-size: 14.5px; line-height: 1.5;">${text}</div>`,
    icon: icon,
    iconColor: icon === 'warning' ? '#f59e0b' : (icon === 'question' ? '#002fda' : '#ee4d2d'),
    showCancelButton: true,
    confirmButtonText: '<i class="bx bx-check me-1 align-middle"></i> Xác Nhận',
    cancelButtonText: '<i class="bx bx-x me-1 align-middle"></i> Hủy',
    reverseButtons: true,
    focusCancel: true
  }).then(result => result.isConfirmed);
};

/**
 * Dialog thông báo hiện đại thay thế alert()
 * @param {string} text
 * @param {string} title
 * @param {'success'|'error'|'warning'|'info'} icon
 */
export const showAlert = (text, title = 'Thông Báo', icon = 'info') => {
  return CustomSwal.fire({
    title: title,
    html: `<div class="py-1 text-secondary" style="font-size: 14.5px; line-height: 1.5;">${text}</div>`,
    icon: icon,
    confirmButtonText: 'Đã Hiểu',
    confirmButtonColor: '#002fda'
  });
};

export default {
  CustomSwal,
  showDeleteConfirm,
  showConfirm,
  showAlert
};
