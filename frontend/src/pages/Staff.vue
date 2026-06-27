<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h2 class="page-title">Staff</h2>
        <p class="page-sub">{{ staff.length }} member{{ staff.length !== 1 ? 's' : '' }}</p>
      </div>
      <button @click="showForm = !showForm" class="btn-primary">{{ showForm ? '✕ Cancel' : '+ Add Staff' }}</button>
    </div>

    <transition name="slide-down">
      <div v-if="showForm" class="form-card">
        <h3>{{ editing ? 'Edit Staff Member' : 'New Staff Member' }}</h3>
        <form @submit.prevent="save">
          <div class="form-row">
            <div class="form-group"><label>Name</label><input v-model="form.name" placeholder="Full name" required /></div>
            <div class="form-group"><label>Phone</label><input v-model="form.phone" placeholder="Phone number" required /></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label>Email</label><input v-model="form.email" type="email" placeholder="Optional" /></div>
            <div class="form-group"><label>NIC</label><input v-model="form.nic" placeholder="NIC number" required /></div>
          </div>
          <div class="form-group">
            <label>Commission Rate (%)</label>
            <input v-model.number="form.commission_rate" type="number" min="0" max="100" step="0.01" placeholder="0.00" />
          </div>
          <div class="form-actions">
            <button type="submit" class="btn-primary">{{ editing ? 'Update' : 'Create' }}</button>
            <button type="button" @click="cancel" class="btn-secondary">Cancel</button>
          </div>
        </form>
      </div>
    </transition>

    <div v-if="loading" class="loading-state">
      <div v-for="i in 3" :key="i" class="skeleton-row"></div>
    </div>
    <div v-else-if="!staff.length" class="empty-state">
      <div class="empty-icon">👥</div>
      <p>No staff members yet.</p>
    </div>
    <div v-else class="table-card">
      <table class="data-table">
        <thead>
          <tr><th>Member</th><th>Phone</th><th>NIC</th><th>Commission</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <tr v-for="s in staff" :key="s.id">
            <td>
              <div class="member-cell">
                <div class="avatar">{{ s.name.charAt(0).toUpperCase() }}</div>
                <div>
                  <div class="member-name">{{ s.name }}</div>
                  <div class="member-email">{{ s.email || '—' }}</div>
                </div>
              </div>
            </td>
            <td>{{ s.phone }}</td>
            <td class="text-muted">{{ s.nic }}</td>
            <td>
              <span class="commission-badge">{{ parseFloat(String(s.commission_rate ?? 0)).toFixed(1) }}%</span>
            </td>
            <td>
              <div class="action-group">
                <button @click="edit(s)" class="btn-icon">✏️</button>
                <button @click="remove(s.id)" class="btn-icon danger">🗑️</button>
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
import StaffRepository from '../Repositories/StaffRepository'
import type { StaffMember } from '../types/index'

interface StaffForm {
  name: string; email?: string; phone: string; nic: string; commission_rate: number
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

function cancel() { showForm.value = false; editing.value = null; form.value = { name: '', email: '', phone: '', nic: '', commission_rate: 0 } }

async function save() {
  if (editing.value) await StaffRepository.update(editing.value, form.value)
  else await StaffRepository.create(form.value)
  cancel(); load()
}

async function remove(id: number) {
  if (!confirm('Delete this staff member?')) return
  await StaffRepository.delete(id); load()
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
.form-group input { padding: 0.6rem 0.875rem; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem; outline: none; transition: border 0.15s; }
.form-group input:focus { border-color: #8b5cf6; }
.form-actions { display: flex; gap: 0.5rem; }

.table-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.07); }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { padding: 0.75rem 1.25rem; text-align: left; font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; background: #f8fafc; border-bottom: 1px solid #f1f5f9; }
.data-table td { padding: 0.875rem 1.25rem; border-bottom: 1px solid #f8fafc; font-size: 0.875rem; color: #374151; }
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr { transition: background 0.12s; }
.data-table tbody tr:hover { background: #f8fafc; }
.member-cell { display: flex; align-items: center; gap: 0.875rem; }
.avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg,#7c3aed,#a78bfa); color: #fff; font-weight: 700; font-size: 0.875rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.member-name { font-weight: 600; color: #0f172a; font-size: 0.875rem; }
.member-email { font-size: 0.75rem; color: #94a3b8; }
.text-muted { color: #94a3b8; }
.commission-badge { background: #ede9fe; color: #7c3aed; padding: 0.25rem 0.65rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
.action-group { display: flex; gap: 0.25rem; }
.btn-icon { background: none; border: none; cursor: pointer; font-size: 1rem; padding: 0.35rem; border-radius: 6px; transition: background 0.12s; }
.btn-icon:hover { background: #f1f5f9; }
.btn-icon.danger:hover { background: #fee2e2; }

.loading-state { display: flex; flex-direction: column; gap: 0.5rem; }
.skeleton-row { height: 60px; background: linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%); background-size: 200%; border-radius: 8px; animation: shimmer 1.4s infinite; }
@keyframes shimmer { 0%{background-position:200%} 100%{background-position:-200%} }
.empty-state { text-align: center; padding: 4rem; color: #94a3b8; background: #fff; border-radius: 12px; }
.empty-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
.empty-state p { margin: 0; font-size: 0.875rem; }

.slide-down-enter-active { transition: all 0.2s ease; }
.slide-down-enter-from { opacity: 0; transform: translateY(-10px); }
.slide-down-leave-active { transition: all 0.15s ease; }
.slide-down-leave-to { opacity: 0; transform: translateY(-10px); }
</style>
