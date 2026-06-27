<template>
  <div class="page">
    <h2 class="page-title">Commission Report</h2>
    <div class="date-bar">
      <label>Date:</label>
      <input type="date" v-model="selectedDate" @change="load" />
    </div>
    <div v-if="loading" class="loading">Loading...</div>
    <div v-else-if="report">
      <div class="summary-grid">
        <div class="summary-card">
          <div class="summary-label">Total Revenue</div>
          <div class="summary-value">{{ fmt(report.total_revenue) }}</div>
        </div>
        <div class="summary-card">
          <div class="summary-label">Total Commissions</div>
          <div class="summary-value red">{{ fmt(report.total_commissions) }}</div>
        </div>
        <div class="summary-card">
          <div class="summary-label">Expenses</div>
          <div class="summary-value red">{{ fmt(report.total_expenses) }}</div>
        </div>
        <div class="summary-card">
          <div class="summary-label">Net Profit</div>
          <div class="summary-value" :class="report.net >= 0 ? 'green' : 'red'">{{ fmt(report.net) }}</div>
        </div>
      </div>
      <h3>Staff Commission Breakdown</h3>
      <table class="data-table">
        <thead>
          <tr>
            <th>Staff</th>
            <th>Bills</th>
            <th>Sales</th>
            <th>Rate</th>
            <th>Commission</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in report.staff_summary" :key="s.staff_id">
            <td>{{ s.staff_name }}</td>
            <td>{{ s.bill_count }}</td>
            <td>{{ fmt(s.total) }}</td>
            <td>{{ s.commission_rate }}%</td>
            <td class="red">{{ fmt(s.commission) }}</td>
          </tr>
        </tbody>
        <tfoot>
          <tr class="total-row">
            <td colspan="4"><strong>Total Commissions</strong></td>
            <td class="red"><strong>{{ fmt(report.total_commissions) }}</strong></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import ReportRepository from '../Repositories/ReportRepository'
import type { DailyReport } from '../types/index'

const selectedDate = ref(new Date().toISOString().split('T')[0])
const report = ref<DailyReport | null>(null)
const loading = ref(false)

const fmt = (val: number) => new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR' }).format(val)

async function load() {
  loading.value = true
  try {
    const { data } = await ReportRepository.daily(selectedDate.value)
    report.value = data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.page { max-width: 800px; }
.page-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 1.5rem; color: #1a1a2e; }
.date-bar { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; }
.date-bar input { padding: 0.4rem 0.6rem; border: 1px solid #d1d5db; border-radius: 4px; }
.summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.summary-card { background: #fff; border-radius: 8px; padding: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
.summary-label { font-size: 0.8rem; color: #6b7280; margin-bottom: 0.5rem; }
.summary-value { font-size: 1.3rem; font-weight: 700; color: #1a1a2e; }
.green { color: #22c55e; } .red { color: #ef4444; }
h3 { font-size: 1rem; font-weight: 600; margin: 0 0 0.75rem; }
.loading { color: #6b7280; }
.data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
.data-table th, .data-table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #f3f4f6; }
.data-table th { background: #f9fafb; font-weight: 600; font-size: 0.875rem; }
.total-row td { background: #f9fafb; }
</style>
