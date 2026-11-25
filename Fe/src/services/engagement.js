import { apiUrl } from './apiConfig.js';
import axios from 'axios';
export const engagementApi = (path, options = {}) => axios({ url: apiUrl(path), ...options });
export const engagementError = error => Object.values(error.response?.data?.errors || {}).flat()[0] || error.response?.data?.message || 'Không thể kết nối. Vui lòng thử lại.';
export const engagementDate = value => value ? new Date(/Z$|[+]\d\d:\d\d$/.test(value) ? value : value.replace(' ', 'T') + 'Z').toLocaleString('vi-VN', { timeZone: 'Asia/Ho_Chi_Minh' }) : 'Gửi ngay';
export const topicLabels = { product: 'Sản phẩm', delivery: 'Giao hàng', service: 'Dịch vụ', website: 'Website', other: 'Khác' };
export const money = value => Number(value || 0).toLocaleString('vi-VN') + 'đ';
