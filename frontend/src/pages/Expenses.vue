<template>
  <div class="page">
    <div class="page-header">
      <h2 class="page-title">Expenses</h2>
      <button @click="showForm = true" class="btn-primary">+ Add Expense</button>
    </div>
    <div v-if="showForm" class="form-card">
      <h3>New Expense</h3>
      <form @submit.prevent="save">
        <div class="form-row">
          <div class="form-group"><label>Date</label><input v-model="form.date" type="date" required /></div>
          <div class="form-group"><label>Amount (PKR)</label><input v-model="form.amount" type="number" step="0.01" min="0" required /></div>
        </div>
        <div class="form-group"><label>Note</label><textarea v-model="form.note" rows="2"></textarea></div>
        <div class="form-actions">
          <button type="submit" class="btn-primary">Save</button>
          <button type="button" @click="showForm = false" class="btn-secondary">Cancel</button>
        </div>
      </form>
    </div>
    <div class="filter-bar">
      <label>Filter by date:</label>
      <input type="date" v-model="filterDate" @change="load" />
    </div>
    <div v-if="loading" class="loading">Loading...</div>
    <table v-else class="data-table">
      <thead><tr><th>Date</th><th>Amount</th><th>Note</th><th>Logged By</th><th>Actions</th></tr></thead>
      <tbody>
        <tr v-for="e in expenses" :key="e.id">
          <td>{{ e.date }}</td>
          <td>PKR {{ parseFloat(String(e.amount)).toFixed(2) }}</td>
          <td>{{ e.note || '-' }}</td>
          <td>{{ e.logged_by?.name || '-' }}</td>
          <td><button @click="remove(e.id)" class="btn-sm btn-danger">Delete</button></td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import ExpenseRepository from '../Repositories/ExpenseRepository'
import type { Expense } from '../types/index'

interface ExpenseForm {
  date: string
  amount: number | string
  note: string
}

const expenses = ref<Expense[]>([])
const loading = ref(false)
const showForm = ref(false)
const filterDate = ref('')
const form = ref<ExpenseForm>({ date: new Date().toISOString().split('T')[0], amount: '', note: '' })

async function load() {
  loading.value = true
  try {
    const params = filterDate.value ? { date: filterDate.value } : undefined
    const { data } = await ExpenseRepository.getAll(params)
    expenses.value = data
  } finally {
    loading.value = false
  }
}

async function save() {
  await ExpenseRepository.create(form.value as Omit<Expense, 'id' | 'logged_by'>)
  showForm.value = false
  form.value = { date: new Date().toISOString().split('T')[0], amount: '', note: '' }
  load()
}

async function remove(id: number) {
  if (!confirm('Delete this expense?')) return
  await ExpenseRepository.delete(id)
  load()
}

onMounted(load)
</script>

<style scoped>
.page { max-width: 900px; }
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.page-title { font-size: 1.5rem; font-weight: 700; margin: 0; color: #1a1a2e; }
.form-card { background: #fff; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
.form-card h3 { margin: 0 0 1rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.25rem; margin-bottom: 0.75rem; }
.form-group label { font-size: 0.875rem; font-weight: 500; color: #374151; }
.form-group input, .form-group textarea { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.95rem; }
.form-actions { display: flex; gap: 0.5rem; }
.filter-bar { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; }
.filter-bar input { padding: 0.4rem 0.6rem; border: 1px solid #d1d5db; border-radius: 4px; }
.btn-primary { background: #1a1a2e; color: #fff; border: none; padding: 0.5rem 1.25rem; border-radius: 6px; cursor: pointer; font-weight: 600; }
.btn-secondary { background: #f3f4f6; color: #374151; border: none; padding: 0.5rem 1.25rem; border-radius: 6px; cursor: pointer; }
.btn-sm { padding: 0.25rem 0.6rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; background: #e5e7eb; margin-right: 0.25rem; }
.btn-danger { background: #fee2e2; color: #ef4444; }
.loading { color: #6b7280; }
.data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
.data-table th, .data-table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #f3f4f6; }
.data-table th { background: #f9fafb; font-weight: 600; font-size: 0.875rem; }
</style>
