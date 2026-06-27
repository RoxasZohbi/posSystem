<template>
  <div class="page">
    <div class="page-header">
      <h2 class="page-title">Staff</h2>
      <button @click="showForm = true" class="btn-primary">+ Add Staff</button>
    </div>
    <div v-if="showForm" class="form-card">
      <h3>{{ editing ? 'Edit' : 'New' }} Staff Member</h3>
      <form @submit.prevent="save">
        <div class="form-row">
          <div class="form-group"><label>Name</label><input v-model="form.name" required /></div>
          <div class="form-group"><label>Phone</label><input v-model="form.phone" required /></div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Email</label><input v-model="form.email" type="email" /></div>
          <div class="form-group"><label>NIC</label><input v-model="form.nic" required /></div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Commission Rate (%)</label>
            <input v-model.number="form.commission_rate" type="number" min="0" max="100" step="0.01" placeholder="0.00" />
          </div>
        </div>
        <div class="form-actions">
          <button type="submit" class="btn-primary">{{ editing ? 'Update' : 'Create' }}</button>
          <button type="button" @click="cancel" class="btn-secondary">Cancel</button>
        </div>
      </form>
    </div>
    <div v-if="loading" class="loading">Loading...</div>
    <table v-else class="data-table">
      <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>NIC</th><th>Commission</th><th>Actions</th></tr></thead>
      <tbody>
        <tr v-for="s in staff" :key="s.id">
          <td>{{ s.name }}</td><td>{{ s.phone }}</td><td>{{ s.email || '-' }}</td><td>{{ s.nic }}</td>
          <td>{{ parseFloat(String(s.commission_rate ?? 0)).toFixed(2) }}%</td>
          <td>
            <button @click="edit(s)" class="btn-sm">Edit</button>
            <button @click="remove(s.id)" class="btn-sm btn-danger">Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import StaffRepository from '../Repositories/StaffRepository'
import type { StaffMember } from '../types/index'

interface StaffForm {
  name: string
  email?: string
  phone: string
  nic: string
  commission_rate: number
}

const staff = ref<StaffMember[]>([])
const loading = ref(false)
const showForm = ref(false)
const editing = ref<number | null>(null)
const form = ref<StaffForm>({ name: '', email: '', phone: '', nic: '', commission_rate: 0 })

async function load() {
  loading.value = true
  try { const { data } = await StaffRepository.getAll(); staff.value = data }
  finally { loading.value = false }
}

function edit(s: StaffMember) {
  editing.value = s.id
  form.value = { name: s.name, email: s.email, phone: s.phone, nic: s.nic, commission_rate: parseFloat(String(s.commission_rate ?? 0)) }
  showForm.value = true
}

function cancel() {
  showForm.value = false
  editing.value = null
  form.value = { name: '', email: '', phone: '', nic: '', commission_rate: 0 }
}

async function save() {
  if (editing.value) {
    await StaffRepository.update(editing.value, form.value)
  } else {
    await StaffRepository.create(form.value)
  }
  cancel()
  load()
}

async function remove(id: number) {
  if (!confirm('Delete this staff member?')) return
  await StaffRepository.delete(id)
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
.form-group input { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.95rem; }
.form-actions { display: flex; gap: 0.5rem; }
.btn-primary { background: #1a1a2e; color: #fff; border: none; padding: 0.5rem 1.25rem; border-radius: 6px; cursor: pointer; font-weight: 600; }
.btn-secondary { background: #f3f4f6; color: #374151; border: none; padding: 0.5rem 1.25rem; border-radius: 6px; cursor: pointer; }
.btn-sm { padding: 0.25rem 0.6rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; background: #e5e7eb; margin-right: 0.25rem; }
.btn-danger { background: #fee2e2; color: #ef4444; }
.loading { color: #6b7280; }
.data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
.data-table th, .data-table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #f3f4f6; }
.data-table th { background: #f9fafb; font-weight: 600; font-size: 0.875rem; }
</style>
