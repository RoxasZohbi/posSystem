<template>
  <div class="page">
    <h2 class="page-title">Commission Report</h2>
    <div class="date-bar">
      <label>Date:</label>
      <input type="date" v-model="selectedDate" @change="load" />
    </div>
    <div v-if="loading" class="loading">Loading...</div>
    <div v-else-if="report">
      <div class="summary-card">
        <p>Date: <strong>{{ report.date }}</strong></p>
        <p>Total Bills: <strong>{{ report.bill_count }}</strong></p>
        <p>Total Revenue: <strong>PKR {{ parseFloat(String(report.total_revenue)).toFixed(2) }}</strong></p>
      </div>
      <h3>Staff Breakdown</h3>
      <table class="data-table">
        <thead><tr><th>Staff</th><th>Bills</th><th>Revenue Generated</th></tr></thead>
        <tbody>
          <tr v-for="s in report.staff_summary" :key="s.staff_id">
            <td>{{ s.staff_name }}</td><td>{{ s.bill_count }}</td><td>PKR {{ parseFloat(String(s.total)).toFixed(2) }}</td>
          </tr>
        </tbody>
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
.summary-card { background: #fff; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
.summary-card p { margin: 0.25rem 0; }
h3 { font-size: 1rem; font-weight: 600; margin-bottom: 0.75rem; }
.loading { color: #6b7280; }
.data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; }
.data-table th, .data-table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #f3f4f6; }
.data-table th { background: #f9fafb; font-weight: 600; font-size: 0.875rem; }
</style>
