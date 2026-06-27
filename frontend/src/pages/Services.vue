<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h2 class="page-title">Services</h2>
        <p class="page-sub">{{ services.length }} service{{ services.length !== 1 ? 's' : '' }}</p>
      </div>
      <button @click="showForm = !showForm" class="btn-primary">{{ showForm ? '✕ Cancel' : '+ Add Service' }}</button>
    </div>

    <transition name="slide-down">
      <div v-if="showForm" class="form-card">
        <h3>{{ editing ? 'Edit Service' : 'New Service' }}</h3>
        <form @submit.prevent="save">
          <div class="form-row">
            <div class="form-group">
              <label>Service Name</label>
              <input v-model="form.name" placeholder="e.g. Haircut" required />
            </div>
            <div class="form-group">
              <label>Price (PKR)</label>
              <input v-model="form.price" type="number" step="0.01" min="0" placeholder="0.00" required />
            </div>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="form.description" rows="2" placeholder="Optional description..."></textarea>
          </div>
          <div class="form-toggle">
            <label class="toggle">
              <input type="checkbox" v-model="form.is_active" />
              <span class="toggle-track"></span>
              <span class="toggle-label">Active</span>
            </label>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-primary">{{ editing ? 'Update' : 'Create' }}</button>
            <button type="button" @click="cancel" class="btn-secondary">Cancel</button>
          </div>
        </form>
      </div>
    </transition>

    <div v-if="loading" class="loading-state">
      <div v-for="i in 4" :key="i" class="skeleton-row"></div>
    </div>
    <div v-else-if="!services.length" class="empty-state">
      <div class="empty-icon">✂️</div>
      <p>No services yet. Add your first one.</p>
    </div>
    <div v-else class="table-card">
      <table class="data-table">
        <thead>
          <tr><th>Name</th><th>Description</th><th>Price</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <tr v-for="s in services" :key="s.id">
            <td class="fw600">{{ s.name }}</td>
            <td class="text-muted">{{ s.description || '—' }}</td>
            <td class="fw600">PKR {{ parseFloat(String(s.price)).toFixed(2) }}</td>
            <td>
              <span :class="s.is_active ? 'badge-green' : 'badge-gray'">
                {{ s.is_active ? '● Active' : '○ Inactive' }}
              </span>
            </td>
            <td>
              <div class="action-group">
                <button @click="edit(s)" class="btn-icon" title="Edit">✏️</button>
                <button @click="remove(s.id)" class="btn-icon danger" title="Delete">🗑️</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
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

function cancel() { showForm.value = false; editing.value = null; form.value = { name: '', description: '', price: '', is_active: true } }

async function save() {
  if (editing.value) await ServiceRepository.update(editing.value, form.value)
  else await ServiceRepository.create(form.value)
  cancel(); load()
}

async function remove(id: number) {
  if (!confirm('Delete this service?')) return
  await ServiceRepository.delete(id); load()
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
.form-toggle { margin-bottom: 1rem; }
.toggle { display: flex; align-items: center; gap: 0.6rem; cursor: pointer; }
.toggle input { display: none; }
.toggle-track { width: 36px; height: 20px; border-radius: 10px; background: #e2e8f0; position: relative; transition: background 0.2s; }
.toggle input:checked + .toggle-track { background: #7c3aed; }
.toggle-track::after { content: ''; position: absolute; width: 14px; height: 14px; border-radius: 50%; background: #fff; top: 3px; left: 3px; transition: transform 0.2s; }
.toggle input:checked + .toggle-track::after { transform: translateX(16px); }
.toggle-label { font-size: 0.875rem; color: #374151; font-weight: 500; }
.form-actions { display: flex; gap: 0.5rem; }

.table-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.07); }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { padding: 0.75rem 1.25rem; text-align: left; font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; background: #f8fafc; border-bottom: 1px solid #f1f5f9; }
.data-table td { padding: 0.875rem 1.25rem; border-bottom: 1px solid #f8fafc; font-size: 0.875rem; color: #374151; }
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr { transition: background 0.12s; }
.data-table tbody tr:hover { background: #f8fafc; }
.fw600 { font-weight: 600; color: #0f172a; }
.text-muted { color: #94a3b8; }
.badge-green { background: #dcfce7; color: #15803d; padding: 0.25rem 0.65rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.badge-gray { background: #f1f5f9; color: #94a3b8; padding: 0.25rem 0.65rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
.action-group { display: flex; gap: 0.25rem; }
.btn-icon { background: none; border: none; cursor: pointer; font-size: 1rem; padding: 0.35rem; border-radius: 6px; transition: background 0.12s; }
.btn-icon:hover { background: #f1f5f9; }
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
