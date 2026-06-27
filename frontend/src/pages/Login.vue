<template>
  <div class="login-page">
    <div class="login-card">
      <h1 class="login-title">Salon POS</h1>
      <p class="login-subtitle">Sign in to your account</p>
      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" type="email" placeholder="owner@salon.com" required autocomplete="email" />
        </div>
        <div class="form-group">
          <label>Password</label>
          <input v-model="form.password" type="password" placeholder="••••••••" required autocomplete="current-password" />
        </div>
        <p v-if="error" class="error-msg">{{ error }}</p>
        <button type="submit" :disabled="loading" class="login-btn">{{ loading ? 'Signing in...' : 'Sign In' }}</button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import type { LoginCredentials } from '../types/index'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const form = ref<LoginCredentials>({ email: '', password: '' })
const loading = ref(false)
const error = ref<string | null>(null)

async function handleLogin() {
  loading.value = true
  error.value = null
  try {
    await auth.login(form.value)
    router.push((route.query.redirect as string) || '/dashboard')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Login failed. Check your credentials.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f3f4f6; }
.login-card { background: #fff; border-radius: 12px; padding: 2.5rem 2rem; width: 100%; max-width: 400px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
.login-title { text-align: center; font-size: 1.75rem; font-weight: 700; color: #1a1a2e; margin: 0 0 0.25rem; }
.login-subtitle { text-align: center; color: #6b7280; margin: 0 0 2rem; }
.login-form { display: flex; flex-direction: column; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.25rem; }
.form-group label { font-size: 0.875rem; font-weight: 500; color: #374151; }
.form-group input { padding: 0.625rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; outline: none; }
.form-group input:focus { border-color: #1a1a2e; }
.error-msg { color: #ef4444; font-size: 0.875rem; margin: 0; }
.login-btn { padding: 0.75rem; background: #1a1a2e; color: #fff; border: none; border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer; }
.login-btn:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
