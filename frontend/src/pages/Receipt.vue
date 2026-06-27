<template>
  <div class="receipt-wrapper">
    <div v-if="loading" class="loading">Loading receipt...</div>
    <div v-else-if="bill" class="receipt">
      <div class="receipt-header">
        <h1 class="salon-name">Salon POS</h1>
        <p class="receipt-date">{{ formatDate(bill.created_at) }}</p>
      </div>
      <div class="divider"></div>
      <div class="receipt-meta">
        <div><span class="label">Customer</span><span>{{ bill.customer_name }}</span></div>
        <div><span class="label">Phone</span><span>{{ bill.customer_phone }}</span></div>
        <div><span class="label">Staff</span><span>{{ bill.staff?.name }}</span></div>
        <div><span class="label">Payment</span><span class="payment-type" :class="bill.payment_type">{{ bill.payment_type?.toUpperCase() }}</span></div>
      </div>
      <div class="divider"></div>
      <table class="items-table">
        <thead><tr><th>Item</th><th class="text-right">Price</th></tr></thead>
        <tbody>
          <tr v-for="item in bill.items" :key="item.id">
            <td>{{ item.name }}</td>
            <td class="text-right">PKR {{ parseFloat(String(item.price)).toFixed(2) }}</td>
          </tr>
        </tbody>
      </table>
      <div class="divider"></div>
      <div class="subtotal-row">
        <span>Subtotal</span>
        <span>PKR {{ parseFloat(String(bill.total)).toFixed(2) }}</span>
      </div>
      <div v-if="tipAmount > 0" class="tip-row">
        <span>🌟 Tip</span>
        <span class="tip-val">PKR {{ tipAmount.toFixed(2) }}</span>
      </div>
      <div class="total-row">
        <strong>Total</strong>
        <strong>PKR {{ grandTotal.toFixed(2) }}</strong>
      </div>
      <div class="divider"></div>
      <p class="thank-you">Thank you for visiting!</p>
      <p class="receipt-id">Ref: {{ bill.uuid }}</p>
      <div class="print-actions no-print">
        <button @click="window.print()" class="btn-print">Print Receipt</button>
        <button @click="router.push({ name: 'Billing' })" class="btn-new">New Bill</button>
      </div>
    </div>
    <div v-else class="not-found">Receipt not found.</div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import BillRepository from '../Repositories/BillRepository'
import type { Bill } from '../types/index'

const route = useRoute()
const router = useRouter()
const bill = ref<Bill | null>(null)
const loading = ref(true)
const window = globalThis.window

const tipAmount = computed(() => parseFloat(String(bill.value?.tip ?? 0)))
const grandTotal = computed(() => parseFloat(String(bill.value?.total ?? 0)) + tipAmount.value)

function formatDate(dt?: string) {
  if (!dt) return ''
  return new Date(dt).toLocaleString('en-PK', { dateStyle: 'medium', timeStyle: 'short' })
}

onMounted(async () => {
  try {
    const { data } = await BillRepository.getByUuid(route.params.uuid as string)
    bill.value = data
  } catch {
    bill.value = null
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.receipt-wrapper { display: flex; justify-content: center; padding: 2rem; min-height: 100vh; background: #f1f5f9; }
.receipt { background: #fff; width: 320px; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); font-size: 0.875rem; }
.receipt-header { text-align: center; margin-bottom: 0.5rem; }
.salon-name { font-size: 1.25rem; font-weight: 700; margin: 0 0 0.25rem; color: #0f172a; }
.receipt-date { color: #6b7280; margin: 0; font-size: 0.8rem; }
.divider { border-top: 1px dashed #d1d5db; margin: 0.75rem 0; }
.receipt-meta { display: flex; flex-direction: column; gap: 0.3rem; }
.receipt-meta div { display: flex; justify-content: space-between; }
.label { color: #6b7280; }
.payment-type { font-weight: 600; }
.payment-type.cash { color: #16a34a; }
.payment-type.card { color: #1d4ed8; }
.payment-type.online { color: #7c3aed; }
.items-table { width: 100%; border-collapse: collapse; }
.items-table th { font-size: 0.8rem; color: #6b7280; padding-bottom: 0.4rem; text-align: left; }
.items-table td { padding: 0.3rem 0; border-bottom: 1px solid #f3f4f6; }
.text-right { text-align: right; }
.subtotal-row { display: flex; justify-content: space-between; font-size: 0.85rem; color: #64748b; margin-bottom: 0.25rem; }
.tip-row { display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.25rem; }
.tip-val { color: #d97706; font-weight: 600; }
.total-row { display: flex; justify-content: space-between; font-size: 1rem; padding-top: 0.4rem; border-top: 1px solid #e2e8f0; }
.thank-you { text-align: center; color: #6b7280; margin: 0.5rem 0 0.25rem; font-size: 0.8rem; }
.receipt-id { text-align: center; color: #9ca3af; font-size: 0.7rem; margin: 0; word-break: break-all; }
.print-actions { display: flex; gap: 0.5rem; margin-top: 1.25rem; }
.btn-print { flex: 1; padding: 0.6rem; background: #0f172a; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
.btn-new { flex: 1; padding: 0.6rem; background: #f1f5f9; color: #374151; border: none; border-radius: 6px; cursor: pointer; }
.loading, .not-found { text-align: center; color: #6b7280; padding: 3rem; }
@media print {
  .no-print { display: none !important; }
  .receipt-wrapper { background: none; padding: 0; }
  .receipt { box-shadow: none; border-radius: 0; width: 100%; }
}
</style>
