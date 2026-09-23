import { reactive } from 'vue'
import axiosClient from '@/services/axiosClient'

export const authStore = reactive({
  user: JSON.parse(localStorage.getItem('shop_user') || 'null'),
  token: localStorage.getItem('shop_token') || null,

  get isLoggedIn() { return !!this.token },
  get isAdmin() { return this.user?.role === 'admin' },
  get isCustomer() { return this.user?.role === 'customer' },

  login(token, user) {
    this.token = token
    this.user = user
    localStorage.setItem('shop_token', token)
    localStorage.setItem('shop_user', JSON.stringify(user))
  },

  logout() {
    axiosClient.post('/auth/logout').catch(() => {})
    this.token = null
    this.user = null
    localStorage.removeItem('shop_token')
    localStorage.removeItem('shop_user')
  },
})
