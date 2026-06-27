import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { login as apiLogin, logout as apiLogout, me as apiMe } from '../api/index.js'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'))
  const token = ref(localStorage.getItem('auth_token') || null)

  const isAuthenticated = computed(() => !!token.value)
  const role = computed(() => user.value?.roles?.[0] || null)
  const isOwner = computed(() => user.value?.roles?.includes('owner'))
  const isManager = computed(() => user.value?.roles?.includes('manager'))
  const isStaff = computed(() => user.value?.roles?.includes('staff'))

  function can(permission) {
    return user.value?.permissions?.includes(permission) ?? false
  }

  async function login(credentials) {
    const { data } = await apiLogin(credentials)
    token.value = data.token
    user.value = data.user
    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('auth_user', JSON.stringify(data.user))
    return data
  }

  async function logout() {
    try { await apiLogout() } catch (e) {}
    finally {
      token.value = null
      user.value = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    }
  }

  async function fetchUser() {
    const { data } = await apiMe()
    user.value = data
    localStorage.setItem('auth_user', JSON.stringify(data))
    return data
  }

  return { user, token, role, isAuthenticated, isOwner, isManager, isStaff, can, login, logout, fetchUser }
})
