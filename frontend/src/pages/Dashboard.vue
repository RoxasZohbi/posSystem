<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h2 class="page-title">Dashboard</h2>
        <p class="page-sub">Overview for {{ selectedDate }}</p>
      </div>
      <input type="date" v-model="selectedDate" @change="loadReport" class="date-input" />
    </div>

    <div v-if="loading" class="skeleton-grid">
      <div v-for="i in 5" :key="i" class="skeleton-card"></div>
    </div>

    <template v-else-if="report">
      <!-- Stat Cards -->
      <div class="stat-grid">
        <div class="stat-card" style="--accent:#22c55e">
          <div class="stat-icon" style="background:#dcfce7;color:#16a34a">💵</div>
          <div class="stat-body">
            <div class="stat-label">Revenue</div>
            <div class="stat-value">{{ fmt(report.total_revenue) }}</div>
          </div>
        </div>
        <div class="stat-card" style="--accent:#8b5cf6">
          <div class="stat-icon" style="background:#ede9fe;color:#7c3aed">📊</div>
          <div class="stat-body">
            <div class="stat-label">Commissions</div>
            <div class="stat-value">{{ fmt(report.total_commissions) }}</div>
          </div>
        </div>
        <div class="stat-card" style="--accent:#ef4444">
          <div class="stat-icon" style="background:#fee2e2;color:#dc2626">💸</div>
          <div class="stat-body">
            <div class="stat-label">Expenses</div>
            <div class="stat-value">{{ fmt(report.total_expenses) }}</div>
          </div>
        </div>
        <div class="stat-card" :style="`--accent:${report.net >= 0 ? '#0ea5e9' : '#ef4444'}`">
          <div class="stat-icon" :style="report.net >= 0 ? 'background:#e0f2fe;color:#0369a1' : 'background:#fee2e2;color:#dc2626'">📈</div>
          <div class="stat-body">
            <div class="stat-label">Net Profit</div>
            <div class="stat-value" :class="report.net >= 0 ? 'text-green' : 'text-red'">{{ fmt(report.net) }}</div>
          </div>
        </div>
        <div class="stat-card" style="--accent:#f59e0b">
          <div class="stat-icon" style="background:#fef3c7;color:#d97706">🧾</div>
          <div class="stat-body">
            <div class="stat-label">Bills</div>
            <div class="stat-value">{{ report.bill_count }}</div>
          </div>
        </div>
      </div>

      <!-- Payment Breakdown -->
      <div v-if="report.payment_breakdown" class="section">
        <h3 class="section-title">Payment Breakdown</h3>
        <div class="payment-grid">
          <div class="payment-card">
            <div class="payment-header">
              <span class="payment-dot" style="background:#22c55e"></span>
              <span class="payment-method">Cash</span>
            </div>
            <div class="payment-amount">{{ fmt(report.payment_breakdown.cash) }}</div>
            <div class="payment-bar">
              <div class="payment-fill" style="background:#22c55e" :style="{ width: pct(report.payment_breakdown.cash, report.total_revenue) }"></div>
            </div>
            <div class="payment-pct">{{ pct(report.payment_breakdown.cash, report.total_revenue) }}</div>
          </div>
          <div class="payment-card">
            <div class="payment-header">
              <span class="payment-dot" style="background:#3b82f6"></span>
              <span class="payment-method">Card</span>
            </div>
            <div class="payment-amount">{{ fmt(report.payment_breakdown.card) }}</div>
            <div class="payment-bar">
              <div class="payment-fill" style="background:#3b82f6" :style="{ width: pct(report.payment_breakdown.card, report.total_revenue) }"></div>
            </div>
            <div class="payment-pct">{{ pct(report.payment_breakdown.card, report.total_revenue) }}</div>
          </div>
          <div class="payment-card">
            <div class="payment-header">
              <span class="payment-dot" style="background:#8b5cf6"></span>
              <span class="payment-method">Online</span>
            </div>
            <div class="payment-amount">{{ fmt(report.payment_breakdown.online) }}</div>
            <div class="payment-bar">
              <div class="payment-fill" style="background:#8b5cf6" :style="{ width: pct(report.payment_breakdown.online, report.total_revenue) }"></div>
            </div>
            <div class="payment-pct">{{ pct(report.payment_breakdown.online, report.total_revenue) }}</div>
          </div>
        </div>
      </div>

      <!-- Staff Table -->
      <div v-if="report.staff_summary?.length" class="section">
        <h3 class="section-title">Staff Performance</h3>
        <div class="table-card">
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
                <td>
                  <div class="staff-cell">
                    <div class="avatar">{{ s.staff_name.charAt(0) }}</div>
                    {{ s.staff_name }}
                  </div>
                </td>
                <td><span class="count-badge">{{ s.bill_count }}</span></td>
                <td class="fw600">{{ fmt(s.total) }}</td>
                <td>{{ s.commission_rate }}%</td>
                <td class="text-red fw600">{{ fmt(s.commission) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
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
const pct = (val: number, total: number) => total > 0 ? Math.round((val / total) * 100) + '%' : '0%'

async function loadReport() {
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

onMounted(loadReport)
</script>

<style scoped>
.page { max-width: 1000px; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.75rem; }
.page-title { font-size: 1.6rem; font-weight: 700; margin: 0 0 0.2rem; color: #0f172a; }
.page-sub { font-size: 0.85rem; color: #94a3b8; margin: 0; }
.date-input { padding: 0.5rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.875rem; color: #374151; background: #fff; }

/* Skeleton */
.skeleton-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(160px,1fr)); gap: 1rem; margin-bottom: 2rem; }
.skeleton-card { height: 96px; background: linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%); background-size: 200%; border-radius: 12px; animation: shimmer 1.4s infinite; }
@keyframes shimmer { 0%{background-position:200%} 100%{background-position:-200%} }

/* Stat cards */
.stat-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(160px,1fr)); gap: 1rem; margin-bottom: 2rem; }
.stat-card {
  background: #fff; border-radius: 12px; padding: 1.25rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.07);
  border-top: 3px solid var(--accent);
  display: flex; align-items: center; gap: 1rem;
  transition: transform 0.15s, box-shadow 0.15s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
.stat-body { min-width: 0; }
.stat-label { font-size: 0.75rem; color: #94a3b8; font-weight: 500; margin-bottom: 0.25rem; }
.stat-value { font-size: 1.25rem; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Sections */
.section { margin-bottom: 2rem; }
.section-title { font-size: 1rem; font-weight: 700; color: #0f172a; margin: 0 0 1rem; }

/* Payment cards */
.payment-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; }
.payment-card { background: #fff; border-radius: 12px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.07); }
.payment-header { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; }
.payment-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.payment-method { font-size: 0.8rem; font-weight: 600; color: #64748b; }
.payment-amount { font-size: 1.3rem; font-weight: 700; color: #0f172a; margin-bottom: 0.75rem; }
.payment-bar { height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden; margin-bottom: 0.4rem; }
.payment-fill { height: 100%; border-radius: 3px; transition: width 0.6s ease; }
.payment-pct { font-size: 0.75rem; color: #94a3b8; }

/* Table */
.table-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.07); }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { padding: 0.75rem 1.25rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; background: #f8fafc; border-bottom: 1px solid #f1f5f9; }
.data-table td { padding: 0.875rem 1.25rem; border-bottom: 1px solid #f8fafc; font-size: 0.875rem; color: #374151; }
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table tbody tr { transition: background 0.12s; }
.data-table tbody tr:hover { background: #f8fafc; }
.staff-cell { display: flex; align-items: center; gap: 0.75rem; }
.avatar { width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg,#7c3aed,#a78bfa); color: #fff; font-weight: 700; font-size: 0.75rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.count-badge { background: #f1f5f9; color: #475569; border-radius: 20px; padding: 0.15rem 0.6rem; font-size: 0.75rem; font-weight: 600; }
.fw600 { font-weight: 600; }
.text-green { color: #16a34a; }
.text-red { color: #dc2626; }

.empty-state { text-align: center; padding: 4rem; color: #94a3b8; }
.empty-icon { font-size: 3rem; margin-bottom: 1rem; }
</style>
