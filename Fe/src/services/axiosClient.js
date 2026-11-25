import axios from 'axios'
import { apiBaseUrl } from './apiConfig.js'

const axiosClient = axios.create({
  baseURL: apiBaseUrl,
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
})

// Tự động đính kèm token
axiosClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('shop_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// Xử lý lỗi toàn cục
axiosClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('shop_token')
      localStorage.removeItem('shop_user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default axiosClient
