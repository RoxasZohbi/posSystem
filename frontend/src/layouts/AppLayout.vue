<template>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="sidebar-header"><h1 class="sidebar-title">Salon POS</h1></div>
      <nav class="sidebar-nav">
        <RouterLink to="/dashboard" class="nav-link">Dashboard</RouterLink>
        <RouterLink v-if="auth.can('create-bill')" to="/billing" class="nav-link">Billing</RouterLink>
        <RouterLink v-if="auth.can('manage-services')" to="/services" class="nav-link">Services</RouterLink>
        <RouterLink v-if="auth.can('manage-deals')" to="/deals" class="nav-link">Deals</RouterLink>
        <RouterLink v-if="auth.can('manage-staff')" to="/staff" class="nav-link">Staff</RouterLink>
        <RouterLink v-if="auth.can('add-expense')" to="/expenses" class="nav-link">Expenses</RouterLink>
        <RouterLink v-if="auth.can('view-own-commission')" to="/commission" class="nav-link">Commission</RouterLink>
      </nav>
      <div class="sidebar-footer">
        <button @click="handleLogout" class="logout-btn">Logout</button>
      </div>
    </aside>
    <div class="main">
      <header class="topbar">
        <div class="topbar-left">
          <span :class="offline.isOffline ? 'badge-offline' : 'badge-online'">{{ offline.isOffline ? 'Offline' : 'Online' }}</span>
          <span v-if="offline.queuedBillCount > 0" class="queue-info">
            {{ offline.queuedBillCount }} bill(s) queued
            <button @click="offline.sync()" class="sync-btn">Sync now</button>
          </span>
        </div>
        <div class="topbar-right">
          <span>{{ auth.user?.name }}</span>
          <span class="role-badge">{{ auth.role }}</span>
        </div>
      </header>
      <main class="content"><RouterView /></main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '../stores/auth'
import { useOfflineStore } from '../stores/offline'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const offline = useOfflineStore()
const router = useRouter()

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.app-layout { display: flex; min-height: 100vh; font-family: system-ui, sans-serif; }
.sidebar { width: 220px; background: #1a1a2e; color: #fff; display: flex; flex-direction: column; flex-shrink: 0; }
.sidebar-header { padding: 1.5rem 1rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
.sidebar-title { margin: 0; font-size: 1.2rem; font-weight: 700; }
.sidebar-nav { flex: 1; padding: 1rem 0; display: flex; flex-direction: column; }
.nav-link { display: block; padding: 0.75rem 1rem; color: rgba(255,255,255,0.8); text-decoration: none; transition: background 0.2s; }
.nav-link:hover, .nav-link.router-link-active { background: rgba(255,255,255,0.1); color: #fff; }
.sidebar-footer { padding: 1rem; border-top: 1px solid rgba(255,255,255,0.1); }
.logout-btn { width: 100%; padding: 0.5rem; background: rgba(255,255,255,0.1); color: #fff; border: none; border-radius: 4px; cursor: pointer; }
.logout-btn:hover { background: rgba(255,255,255,0.2); }
.main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
.topbar { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1.5rem; background: #fff; border-bottom: 1px solid #e5e7eb; }
.topbar-left { display: flex; align-items: center; gap: 1rem; }
.badge-online { background: #22c55e; color: #fff; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
.badge-offline { background: #ef4444; color: #fff; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
.queue-info { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: #f59e0b; }
.sync-btn { background: #f59e0b; color: #fff; border: none; border-radius: 4px; padding: 0.2rem 0.5rem; cursor: pointer; font-size: 0.75rem; }
.topbar-right { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; }
.role-badge { background: #e5e7eb; color: #374151; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
.content { flex: 1; padding: 1.5rem; background: #f9fafb; overflow-y: auto; }
</style>
