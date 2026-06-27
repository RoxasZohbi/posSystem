<template>
  <div class="page">
    <h2 class="page-title">Dashboard</h2>
    <div class="date-bar">
      <label>Date:</label>
      <input type="date" v-model="selectedDate" @change="loadReport" />
    </div>
    <div v-if="loading" class="loading">Loading...</div>
    <div v-else-if="report">
      <div class="stat-grid">
        <div class="stat-card"><div class="stat-label">Revenue</div><div class="stat-value">{{ fmt(report.total_revenue) }}</div></div>
        <div class="stat-card"><div class="stat-label">Expenses</div><div class="stat-value">{{ fmt(report.total_expenses) }}</div></div>
        <div class="stat-card"><div class="stat-label">Net</div><div class="stat-value" :class="report.net >= 0 ? 'green' : 'red'">{{ fmt(report.net) }}</div></div>
        <div class="stat-card"><div class="stat-label">Bills</div><div class="stat-value">{{ report.bill_count }}</div></div>
      </div>
      <div v-if="report.staff_summary?.length" class="section">
        <h3>Staff Summary</h3>
        <table class="data-table">
          <thead><tr><th>Staff</th><th>Bills</th><th>Total</th></tr></thead>
          <tbody>
            <tr v-for="s in report.staff_summary" :key="s.staff_id">
              <td>{{ s.staff_name }}</td><td>{{ s.bill_count }}</td><td>{{ fmt(s.total) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getDailyReport } from '../api/index.js'

const selectedDate = ref(new Date().toISOString().split('T')[0])
const report = ref(null)
const loading = ref(false)

const fmt = (val) => new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR' }).format(val)

async function loadReport() {
  loading.value = true
  try { const { data } = await getDailyReport(selectedDate.value); report.value = data }
  catch (e) { console.error(e) }
  finally { loading.value = false }
}

onMounted(loadReport)
</script>

<style scoped>
.page { max-width: 900px; }
.page-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 1.5rem; color: #1a1a2e; }
.date-bar { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; }
.date-bar input { padding: 0.4rem 0.6rem; border: 1px solid #d1d5db; border-radius: 4px; }
.loading { color: #6b7280; }
.stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.stat-card { background: #fff; border-radius: 8px; padding: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
.stat-label { font-size: 0.8rem; color: #6b7280; margin-bottom: 0.5rem; }
.stat-value { font-size: 1.4rem; font-weight: 700; color: #1a1a2e; }
.green { color: #22c55e; } .red { color: #ef4444; }
.section h3 { font-size: 1rem; font-weight: 600; margin: 0 0 1rem; }
.data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
.data-table th, .data-table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #f3f4f6; }
.data-table th { background: #f9fafb; font-weight: 600; font-size: 0.875rem; }
</style>
