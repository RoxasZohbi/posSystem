import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth.js'

const routes = [
  { path: '/login', name: 'Login', component: () => import('../pages/Login.vue'), meta: { requiresAuth: false } },
  {
    path: '/',
    component: () => import('../layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '', redirect: '/dashboard' },
      { path: 'dashboard', name: 'Dashboard', component: () => import('../pages/Dashboard.vue') },
      { path: 'services', name: 'Services', component: () => import('../pages/Services.vue'), meta: { permission: 'manage-services' } },
      { path: 'staff', name: 'Staff', component: () => import('../pages/Staff.vue'), meta: { permission: 'manage-staff' } },
      { path: 'deals', name: 'Deals', component: () => import('../pages/Deals.vue'), meta: { permission: 'manage-deals' } },
      { path: 'billing', name: 'Billing', component: () => import('../pages/Billing.vue'), meta: { permission: 'create-bill' } },
      { path: 'expenses', name: 'Expenses', component: () => import('../pages/Expenses.vue'), meta: { permission: 'add-expense' } },
      { path: 'commission', name: 'Commission', component: () => import('../pages/Commission.vue'), meta: { permission: 'view-own-commission' } },
    ],
  },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({ history: createWebHistory(), routes })

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  if (to.meta.requiresAuth === false) {
    return authStore.isAuthenticated && to.name === 'Login' ? next({ name: 'Dashboard' }) : next()
  }
  if (!authStore.isAuthenticated) return next({ name: 'Login', query: { redirect: to.fullPath } })
  if (to.meta.permission && !authStore.can(to.meta.permission)) return next({ name: 'Dashboard' })
  next()
})

export default router
