<template>
  <div class="page">
    <div class="page-header">
      <h2 class="page-title">Services</h2>
      <button @click="showForm = true" class="btn-primary">+ Add Service</button>
    </div>
    <div v-if="showForm" class="form-card">
      <h3>{{ editing ? 'Edit' : 'New' }} Service</h3>
      <form @submit.prevent="save">
        <div class="form-row">
          <div class="form-group"><label>Name</label><input v-model="form.name" required /></div>
          <div class="form-group"><label>Price (PKR)</label><input v-model="form.price" type="number" step="0.01" min="0" required /></div>
        </div>
        <div class="form-group"><label>Description</label><textarea v-model="form.description" rows="2"></textarea></div>
        <div class="form-group"><label><input type="checkbox" v-model="form.is_active" /> Active</label></div>
        <div class="form-actions">
          <button type="submit" class="btn-primary">{{ editing ? 'Update' : 'Create' }}</button>
          <button type="button" @click="cancel" class="btn-secondary">Cancel</button>
        </div>
      </form>
    </div>
    <div v-if="loading" class="loading">Loading...</div>
    <table v-else class="data-table">
      <thead><tr><th>Name</th><th>Description</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <tr v-for="s in services" :key="s.id">
          <td>{{ s.name }}</td>
          <td>{{ s.description || '-' }}</td>
          <td>PKR {{ parseFloat(String(s.price)).toFixed(2) }}</td>
          <td><span :class="s.is_active ? 'badge-active' : 'badge-inactive'">{{ s.is_active ? 'Active' : 'Inactive' }}</span></td>
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
import ServiceRepository from '../Repositories/ServiceRepository'
import type { Service } from '../types/index'

const services = ref<Service[]>([])
const loading = ref(false)
const showForm = ref(false)
const editing = ref<number | null>(null)
const form = ref<Omit<Service, 'id'>>({ name: '', description: '', price: '', is_active: true })

async function load() {
  loading.value = true
  try { const { data } = await ServiceRepository.getAll(); services.value = data }
  finally { loading.value = false }
}

function edit(s: Service) {
  editing.value = s.id
  form.value = { name: s.name, description: s.description, price: s.price, is_active: s.is_active }
  showForm.value = true
}

function cancel() {
  showForm.value = false
  editing.value = null
  form.value = { name: '', description: '', price: '', is_active: true }
}

async function save() {
  if (editing.value) {
    await ServiceRepository.update(editing.value, form.value)
  } else {
    await ServiceRepository.create(form.value)
  }
  cancel()
  load()
}

async function remove(id: number) {
  if (!confirm('Delete this service?')) return
  await ServiceRepository.delete(id)
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
.btn-primary { background: #1a1a2e; color: #fff; border: none; padding: 0.5rem 1.25rem; border-radius: 6px; cursor: pointer; font-weight: 600; }
.btn-secondary { background: #f3f4f6; color: #374151; border: none; padding: 0.5rem 1.25rem; border-radius: 6px; cursor: pointer; }
.btn-sm { padding: 0.25rem 0.6rem; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; background: #e5e7eb; margin-right: 0.25rem; }
.btn-danger { background: #fee2e2; color: #ef4444; }
.loading { color: #6b7280; }
.data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
.data-table th, .data-table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #f3f4f6; }
.data-table th { background: #f9fafb; font-weight: 600; font-size: 0.875rem; }
.badge-active { background: #dcfce7; color: #166534; padding: 0.2rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; }
.badge-inactive { background: #f3f4f6; color: #6b7280; padding: 0.2rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; }
</style>
