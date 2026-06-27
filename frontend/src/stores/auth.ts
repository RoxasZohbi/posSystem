import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import AuthRepository from '../Repositories/AuthRepository'
import type { User, LoginCredentials } from '../types/index'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(JSON.parse(localStorage.getItem('auth_user') || 'null'))
  const token = ref<string | null>(localStorage.getItem('auth_token'))

  const isAuthenticated = computed(() => !!token.value)
  const role = computed(() => user.value?.roles?.[0] ?? null)
  const isOwner = computed(() => user.value?.roles?.includes('owner') ?? false)
  const isManager = computed(() => user.value?.roles?.includes('manager') ?? false)
  const isStaff = computed(() => user.value?.roles?.includes('staff') ?? false)

  function can(permission: string): boolean {
    return user.value?.permissions?.includes(permission) ?? false
  }

  async function login(credentials: LoginCredentials) {
    const { data } = await AuthRepository.login(credentials)
    token.value = data.token
    user.value = data.user
    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('auth_user', JSON.stringify(data.user))
    return data
  }

  async function logout() {
    try { await AuthRepository.logout() } catch {}
    finally {
      token.value = null
      user.value = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    }
  }

  async function fetchUser() {
    const { data } = await AuthRepository.me()
    user.value = data
    localStorage.setItem('auth_user', JSON.stringify(data))
    return data
  }

  return { user, token, role, isAuthenticated, isOwner, isManager, isStaff, can, login, logout, fetchUser }
})
