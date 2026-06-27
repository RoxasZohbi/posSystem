<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h2 class="page-title">Expenses</h2>
        <p class="page-sub">Total: <strong>PKR {{ totalAmount.toFixed(0) }}</strong></p>
      </div>
      <button @click="showForm = !showForm" class="btn-primary">{{ showForm ? '✕ Cancel' : '+ Add Expense' }}</button>
    </div>

    <transition name="slide-down">
      <div v-if="showForm" class="form-card">
        <h3>New Expense</h3>
        <form @submit.prevent="save">
          <div class="form-row">
            <div class="form-group"><label>Date</label><input v-model="form.date" type="date" required /></div>
            <div class="form-group"><label>Amount (PKR)</label><input v-model="form.amount" type="number" step="0.01" min="0" placeholder="0.00" required /></div>
          </div>
          <div class="form-group"><label>Note</label><textarea v-model="form.note" rows="2" placeholder="What was this expense for?"></textarea></div>
          <div class="form-actions">
            <button type="submit" class="btn-primary">Save Expense</button>
            <button type="button" @click="showForm = false" class="btn-secondary">Cancel</button>
          </div>
        </form>
      </div>
    </transition>

    <div class="filter-bar">
      <span class="filter-label">Filter by date</span>
      <input type="date" v-model="filterDate" @change="load" class="date-input" />
      <button v-if="filterDate" @click="filterDate = ''; load()" class="clear-btn">✕ Clear</button>
    </div>

    <div v-if="loading" class="loading-state">
      <div v-for="i in 3" :key="i" class="skeleton-row"></div>
    </div>
    <div v-else-if="!expenses.length" class="empty-state">
      <div class="empty-icon">💰</div>
      <p>No expenses recorded{{ filterDate ? ' for this date' : '' }}.</p>
    </div>
    <div v-else class="table-card">
      <table class="data-table">
        <thead><tr><th>Date</th><th>Amount</th><th>Note</th><th>Logged By</th><th>Actions</th></tr></thead>
        <tbody>
          <tr v-for="e in expenses" :key="e.id">
            <td class="fw600">{{ e.date }}</td>
            <td><span class="amount-badge">PKR {{ parseFloat(String(e.amount)).toFixed(2) }}</span></td>
            <td class="text-muted">{{ e.note || '—' }}</td>
            <td>
              <div class="logged-by">
                <div class="tiny-avatar">{{ e.logged_by?.name?.charAt(0) ?? '?' }}</div>
                {{ e.logged_by?.name || '—' }}
              </div>
            </td>
            <td>
              <button @click="remove(e.id)" class="btn-icon danger">🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import ExpenseRepository from '../Repositories/ExpenseRepository'
import type { Expense } from '../types/index'

interface ExpenseForm { date: string; amount: number | string; note: string }

const expenses = ref<Expense[]>([])
const loading = ref(false)
const showForm = ref(false)
const filterDate = ref('')
const form = ref<ExpenseForm>({ date: new Date().toISOString().split('T')[0], amount: '', note: '' })
const totalAmount = computed(() => expenses.value.reduce((sum, e) => sum + parseFloat(String(e.amount)), 0))

async function load() {
  loading.value = true
  try {
    const params = filterDate.value ? { date: filterDate.value } : undefined
    const { data } = await ExpenseRepository.getAll(params)
    expenses.value = data
  } finally { loading.value = false }
}

async function save() {
  await ExpenseRepository.create(form.value as Omit<Expense, 'id' | 'logged_by'>)
  showForm.value = false
  form.value = { date: new Date().toISOString().split('T')[0], amount: '', note: '' }
  load()
}

async function remove(id: number) {
  if (!confirm('Delete this expense?')) return
  await ExpenseRepository.delete(id); load()
}

onMounted(load)
</script>

<style scoped>
.page { max-width: 900px; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.page-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 0.2rem; color: #0f172a; }
.page-sub { font-size: 0.8rem; color: #94a3b8; margin: 0; }
.btn-primary { background: #7c3aed; color: #fff; border: none; padding: 0.55rem 1.25rem; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 0.875rem; transition: background 0.15s; }
.btn-primary:hover { background: #6d28d9; }
.btn-secondary { background: #f1f5f9; color: #475569; border: none; padding: 0.55rem 1.25rem; border-radius: 8px; cursor: pointer; font-size: 0.875rem; }

.form-card { background: #fff; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.07); border: 1px solid #f1f5f9; }
.form-card h3 { margin: 0 0 1.25rem; font-size: 1rem; font-weight: 700; color: #0f172a; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 0.875rem; }
.form-group label { font-size: 0.8rem; font-weight: 600; color: #64748b; }
.form-group input, .form-group textarea { padding: 0.6rem 0.875rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem; outline: none; transition: border 0.15s; }
.form-group input:focus, .form-group textarea:focus { border-color: #8b5cf6; }
.form-actions { display: flex; gap: 0.5rem; }

.filter-bar { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
.filter-label { font-size: 0.8rem; color: #94a3b8; font-weight: 500; }
.date-input { padding: 0.4rem 0.75rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 0.825rem; outline: none; }
.date-input:focus { border-color: #8b5cf6; }
.clear-btn { background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 6px; }
.clear-btn:hover { background: #f1f5f9; color: #374151; }

.table-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.07); }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { padding: 0.75rem 1.25rem; text-align: left; font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; background: #f8fafc; border-bottom: 1px solid #f1f5f9; }
.data-table td { padding: 0.875rem 1.25rem; border-bottom: 1px solid #f8fafc; font-size: 0.875rem; color: #374151; }
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr { transition: background 0.12s; }
.data-table tbody tr:hover { background: #f8fafc; }
.fw600 { font-weight: 600; color: #0f172a; }
.text-muted { color: #94a3b8; }
.amount-badge { background: #fee2e2; color: #dc2626; padding: 0.25rem 0.65rem; border-radius: 20px; font-size: 0.8rem; font-weight: 700; }
.logged-by { display: flex; align-items: center; gap: 0.5rem; }
.tiny-avatar { width: 22px; height: 22px; border-radius: 50%; background: linear-gradient(135deg,#7c3aed,#a78bfa); color: #fff; font-size: 0.65rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.btn-icon { background: none; border: none; cursor: pointer; font-size: 1rem; padding: 0.35rem; border-radius: 6px; transition: background 0.12s; }
.btn-icon.danger:hover { background: #fee2e2; }

.loading-state { display: flex; flex-direction: column; gap: 0.5rem; }
.skeleton-row { height: 52px; background: linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%); background-size: 200%; border-radius: 8px; animation: shimmer 1.4s infinite; }
@keyframes shimmer { 0%{background-position:200%} 100%{background-position:-200%} }
.empty-state { text-align: center; padding: 4rem; color: #94a3b8; background: #fff; border-radius: 12px; }
.empty-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
.empty-state p { margin: 0; font-size: 0.875rem; }

.slide-down-enter-active { transition: all 0.2s ease; }
.slide-down-enter-from { opacity: 0; transform: translateY(-10px); }
.slide-down-leave-active { transition: all 0.15s ease; }
.slide-down-leave-to { opacity: 0; transform: translateY(-10px); }
</style>
