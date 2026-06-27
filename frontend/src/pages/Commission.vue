<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h2 class="page-title">Commission &amp; Tips Report</h2>
        <p class="page-sub">Day-end breakdown</p>
      </div>
      <input type="date" v-model="selectedDate" @change="load" class="date-input" />
    </div>

    <div v-if="loading" class="skeleton-grid">
      <div v-for="i in 4" :key="i" class="skeleton-card"></div>
    </div>

    <template v-else-if="report">
      <div class="summary-grid">
        <div class="summary-card" style="--accent:#22c55e">
          <div class="s-label">Total Revenue</div>
          <div class="s-value">{{ fmt(report.total_revenue) }}</div>
        </div>
        <div class="summary-card" style="--accent:#f59e0b">
          <div class="s-label">Total Tips 🌟</div>
          <div class="s-value amber">{{ fmt(report.total_tips) }}</div>
        </div>
        <div class="summary-card" style="--accent:#8b5cf6">
          <div class="s-label">Commissions</div>
          <div class="s-value purple">{{ fmt(report.total_commissions) }}</div>
        </div>
        <div class="summary-card" style="--accent:#ef4444">
          <div class="s-label">Expenses</div>
          <div class="s-value red">{{ fmt(report.total_expenses) }}</div>
        </div>
        <div class="summary-card" :style="`--accent:${report.net >= 0 ? '#0ea5e9' : '#ef4444'}`">
          <div class="s-label">Net Profit</div>
          <div class="s-value" :class="report.net >= 0 ? 'green' : 'red'">{{ fmt(report.net) }}</div>
        </div>
      </div>

      <h3 class="section-title">Staff Breakdown</h3>
      <div class="table-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>Staff</th>
              <th>Bills</th>
              <th>Sales</th>
              <th>Rate</th>
              <th>Commission</th>
              <th>Tips 🌟</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in report.staff_summary" :key="s.staff_id">
              <td>
                <div class="staff-cell">
                  <div class="avatar">{{ s.staff_name.charAt(0) }}</div>
                  {{ s.staff_name }}
                </div>
              </td>
              <td><span class="count-badge">{{ s.bill_count }}</span></td>
              <td class="fw600">{{ fmt(s.total) }}</td>
              <td>{{ s.commission_rate }}%</td>
              <td class="purple fw600">{{ fmt(s.commission) }}</td>
              <td class="amber fw600">{{ fmt(s.tips) }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="total-row">
              <td colspan="4"><strong>Totals</strong></td>
              <td class="purple fw600">{{ fmt(report.total_commissions) }}</td>
              <td class="amber fw600">{{ fmt(report.total_tips) }}</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </template>

    <div v-else class="empty-state">
      <div class="empty-icon">📋</div>
      <p>No data for this date.</p>
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

const fmt = (val: number) => new Intl.NumberFormat('en-PK', { style: 'currency', currency: 'PKR', maximumFractionDigits: 0 }).format(val)

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
.page { max-width: 900px; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; }
.page-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 0.2rem; color: #0f172a; }
.page-sub { font-size: 0.8rem; color: #94a3b8; margin: 0; }
.date-input { padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem; }

.skeleton-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(150px,1fr)); gap: 1rem; margin-bottom: 2rem; }
.skeleton-card { height: 80px; background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200%; border-radius: 12px; animation: shimmer 1.4s infinite; }
@keyframes shimmer { 0%{background-position:200%} 100%{background-position:-200%} }

.summary-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(150px,1fr)); gap: 1rem; margin-bottom: 2rem; }
.summary-card { background: #fff; border-radius: 12px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.07); border-top: 3px solid var(--accent); transition: transform 0.15s; }
.summary-card:hover { transform: translateY(-2px); }
.s-label { font-size: 0.75rem; color: #94a3b8; font-weight: 500; margin-bottom: 0.4rem; }
.s-value { font-size: 1.25rem; font-weight: 700; color: #0f172a; }
.green { color: #16a34a; } .red { color: #dc2626; } .amber { color: #d97706; } .purple { color: #7c3aed; }

.section-title { font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0 0 1rem; }
.table-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.07); }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { padding: 0.75rem 1.25rem; text-align: left; font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; background: #f8fafc; border-bottom: 1px solid #f1f5f9; }
.data-table td { padding: 0.875rem 1.25rem; border-bottom: 1px solid #f8fafc; font-size: 0.875rem; color: #374151; }
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr { transition: background 0.12s; }
.data-table tbody tr:hover { background: #f8fafc; }
.total-row td { background: #f8fafc; border-top: 1px solid #e2e8f0; border-bottom: none; }
.staff-cell { display: flex; align-items: center; gap: 0.75rem; }
.avatar { width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg,#7c3aed,#a78bfa); color: #fff; font-weight: 700; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.count-badge { background: #f1f5f9; color: #475569; border-radius: 20px; padding: 0.15rem 0.6rem; font-size: 0.75rem; font-weight: 600; }
.fw600 { font-weight: 600; }
.empty-state { text-align: center; padding: 4rem; color: #94a3b8; background: #fff; border-radius: 12px; }
.empty-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
.empty-state p { margin: 0; font-size: 0.875rem; }
</style>
