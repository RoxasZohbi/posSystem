<template>
  <div class="app-layout">
    <!-- Mobile overlay -->
    <div v-if="sidebarOpen" class="sidebar-overlay" @click="sidebarOpen = false"></div>

    <aside :class="['sidebar', { open: sidebarOpen }]">
      <div class="sidebar-brand">
        <span class="brand-icon">✂️</span>
        <span class="brand-name">Salon POS</span>
        <button class="sidebar-close" @click="sidebarOpen = false">✕</button>
      </div>
      <nav class="sidebar-nav">
        <RouterLink to="/dashboard" class="nav-item" @click="sidebarOpen = false">
          <span class="nav-icon">📊</span><span>Dashboard</span>
        </RouterLink>
        <RouterLink v-if="auth.can('create-bill')" to="/billing" class="nav-item" @click="sidebarOpen = false">
          <span class="nav-icon">🧾</span><span>Billing</span>
        </RouterLink>
        <RouterLink v-if="auth.can('manage-services')" to="/services" class="nav-item" @click="sidebarOpen = false">
          <span class="nav-icon">✂️</span><span>Services</span>
        </RouterLink>
        <RouterLink v-if="auth.can('manage-deals')" to="/deals" class="nav-item" @click="sidebarOpen = false">
          <span class="nav-icon">🏷️</span><span>Deals</span>
        </RouterLink>
        <RouterLink v-if="auth.can('manage-staff')" to="/staff" class="nav-item" @click="sidebarOpen = false">
          <span class="nav-icon">👥</span><span>Staff</span>
        </RouterLink>
        <RouterLink v-if="auth.can('add-expense')" to="/expenses" class="nav-item" @click="sidebarOpen = false">
          <span class="nav-icon">💰</span><span>Expenses</span>
        </RouterLink>
        <RouterLink v-if="auth.can('view-own-commission')" to="/commission" class="nav-item" @click="sidebarOpen = false">
          <span class="nav-icon">📈</span><span>Commission</span>
        </RouterLink>
      </nav>
      <div class="sidebar-footer">
        <div class="user-info">
          <div class="user-avatar">{{ auth.user?.name?.charAt(0).toUpperCase() }}</div>
          <div class="user-details">
            <div class="user-name">{{ auth.user?.name }}</div>
            <div class="user-role">{{ auth.role }}</div>
          </div>
        </div>
        <button @click="handleLogout" class="logout-btn" title="Logout">⏏️</button>
      </div>
    </aside>

    <div class="main">
      <header class="topbar">
        <div class="topbar-left">
          <button class="hamburger" @click="sidebarOpen = true" aria-label="Open menu">
            <span></span><span></span><span></span>
          </button>
          <span :class="['status-dot', offline.isOffline ? 'offline' : 'online']"></span>
          <span class="status-label">{{ offline.isOffline ? 'Offline' : 'Online' }}</span>
          <transition name="fade">
            <div v-if="offline.queuedBillCount > 0" class="queue-pill">
              <span>{{ offline.queuedBillCount }} queued</span>
              <button @click="offline.sync()" class="sync-btn">Sync ↑</button>
            </div>
          </transition>
        </div>
        <div class="topbar-right">
          <span class="topbar-date">{{ today }}</span>
        </div>
      </header>
      <main class="content"><RouterView /></main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useOfflineStore } from '../stores/offline'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const offline = useOfflineStore()
const router = useRouter()
const sidebarOpen = ref(false)
const today = new Date().toLocaleDateString('en-PK', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' })

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
* { box-sizing: border-box; }
.app-layout { display: flex; min-height: 100vh; font-family: 'Inter', system-ui, sans-serif; background: #f1f5f9; }

/* Sidebar */
.sidebar {
  width: 240px; background: #0f172a; color: #e2e8f0;
  display: flex; flex-direction: column; flex-shrink: 0;
  transition: transform 0.25s ease;
}
.sidebar-brand {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 1.5rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.06);
  position: relative;
}
.brand-icon { font-size: 1.4rem; }
.brand-name { font-size: 1.1rem; font-weight: 700; color: #fff; letter-spacing: -0.02em; flex: 1; }
.sidebar-close { display: none; background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 1rem; padding: 0.25rem; line-height: 1; }

.sidebar-nav { flex: 1; padding: 0.75rem; display: flex; flex-direction: column; gap: 0.25rem; overflow-y: auto; }
.nav-item {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 0.65rem 0.875rem; border-radius: 8px;
  color: #94a3b8; text-decoration: none; font-size: 0.9rem; font-weight: 500;
  transition: all 0.15s ease;
}
.nav-item:hover { background: rgba(255,255,255,0.07); color: #e2e8f0; }
.nav-item.router-link-active { background: rgba(139,92,246,0.2); color: #a78bfa; border-left: 3px solid #8b5cf6; padding-left: calc(0.875rem - 3px); }
.nav-icon { font-size: 1rem; width: 20px; text-align: center; }

.sidebar-footer { padding: 0.75rem; border-top: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; gap: 0.5rem; }
.user-info { display: flex; align-items: center; gap: 0.6rem; flex: 1; min-width: 0; }
.user-avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg,#7c3aed,#a78bfa); color: #fff; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.user-details { min-width: 0; }
.user-name { font-size: 0.825rem; font-weight: 600; color: #e2e8f0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-role { font-size: 0.7rem; color: #64748b; }
.logout-btn { background: none; border: none; color: #64748b; cursor: pointer; font-size: 1rem; padding: 0.25rem; border-radius: 4px; transition: color 0.15s; }
.logout-btn:hover { color: #ef4444; }

/* Main area */
.main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

/* Topbar */
.topbar { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1.25rem; background: #fff; border-bottom: 1px solid #e2e8f0; z-index: 10; gap: 0.5rem; }
.topbar-left { display: flex; align-items: center; gap: 0.6rem; min-width: 0; }
.hamburger { display: none; flex-direction: column; justify-content: center; gap: 5px; background: none; border: none; cursor: pointer; padding: 0.25rem; flex-shrink: 0; }
.hamburger span { display: block; width: 20px; height: 2px; background: #374151; border-radius: 2px; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.status-dot.online { background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,0.2); }
.status-dot.offline { background: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.2); }
.status-label { font-size: 0.8rem; color: #64748b; font-weight: 500; white-space: nowrap; }
.queue-pill { display: flex; align-items: center; gap: 0.4rem; background: #fef3c7; border: 1px solid #fcd34d; border-radius: 20px; padding: 0.2rem 0.6rem; font-size: 0.75rem; color: #92400e; white-space: nowrap; }
.sync-btn { background: #f59e0b; color: #fff; border: none; border-radius: 10px; padding: 0.15rem 0.5rem; cursor: pointer; font-size: 0.7rem; font-weight: 600; }
.topbar-right { font-size: 0.8rem; color: #94a3b8; white-space: nowrap; flex-shrink: 0; }

/* Content */
.content { flex: 1; padding: 1.25rem; overflow-y: auto; }

/* Overlay */
.sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 39; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ── Mobile ── */
@media (max-width: 768px) {
  .sidebar {
    position: fixed; top: 0; left: 0; bottom: 0; z-index: 40;
    transform: translateX(-100%);
    width: 260px;
  }
  .sidebar.open { transform: translateX(0); }
  .sidebar-close { display: flex; align-items: center; justify-content: center; }
  .sidebar-overlay { display: block; }
  .hamburger { display: flex; }
  .content { padding: 1rem; }
  .topbar { padding: 0.65rem 1rem; }
  .topbar-right { display: none; }
}
</style>
