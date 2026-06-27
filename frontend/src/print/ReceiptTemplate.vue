<template>
  <div class="receipt">
    <div class="receipt-header">
      <h1 class="business-name">{{ businessName }}</h1>
      <p class="tagline">Professional Salon Services</p>
    </div>
    <div class="divider">- - - - - - - - - - - - - - -</div>
    <div class="receipt-meta">
      <div class="row"><span>Bill ID:</span><span>{{ bill.uuid?.slice(0, 8).toUpperCase() }}</span></div>
      <div class="row"><span>Date:</span><span>{{ formattedDate }}</span></div>
      <div class="row"><span>Time:</span><span>{{ formattedTime }}</span></div>
    </div>
    <div class="divider">- - - - - - - - - - - - - - -</div>
    <div class="receipt-customer">
      <div class="row"><span>Customer:</span><span>{{ bill.customer_name }}</span></div>
      <div class="row"><span>Phone:</span><span>{{ bill.customer_phone }}</span></div>
      <div class="row"><span>Staff:</span><span>{{ bill.staff?.name || bill.staff_name }}</span></div>
    </div>
    <div class="divider">- - - - - - - - - - - - - - -</div>
    <div class="receipt-items">
      <div class="items-header"><span>Item</span><span>Price</span></div>
      <div v-for="item in bill.items" :key="item.id || item.name" class="item-row">
        <span class="item-name">{{ item.name }}</span>
        <span>PKR {{ parseFloat(item.price).toFixed(2) }}</span>
      </div>
    </div>
    <div class="divider">- - - - - - - - - - - - - - -</div>
    <div class="receipt-total"><span>TOTAL</span><span>PKR {{ parseFloat(bill.total).toFixed(2) }}</span></div>
    <div class="divider">- - - - - - - - - - - - - - -</div>
    <div class="receipt-footer">
      <p>Thank you for visiting us!</p>
      <p>Please come again</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  bill: { type: Object, required: true },
  businessName: { type: String, default: 'Salon POS' },
})

const billDate = computed(() => props.bill.created_at ? new Date(props.bill.created_at) : new Date())
const formattedDate = computed(() => billDate.value.toLocaleDateString('en-PK', { year: 'numeric', month: 'short', day: 'numeric' }))
const formattedTime = computed(() => billDate.value.toLocaleTimeString('en-PK', { hour: '2-digit', minute: '2-digit' }))
</script>

<style scoped>
.receipt { width: 80mm; font-family: 'Courier New', Courier, monospace; font-size: 12px; background: #fff; padding: 8mm; color: #000; }
.receipt-header { text-align: center; margin-bottom: 4px; }
.business-name { font-size: 16px; font-weight: bold; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
.tagline { font-size: 10px; color: #555; margin: 2px 0 0; }
.divider { text-align: center; color: #888; margin: 6px 0; font-size: 10px; }
.row { display: flex; justify-content: space-between; margin: 2px 0; }
.items-header { display: flex; justify-content: space-between; font-weight: bold; border-bottom: 1px dashed #ccc; padding-bottom: 2px; margin-bottom: 4px; }
.item-row { display: flex; justify-content: space-between; margin: 2px 0; }
.item-name { flex: 1; padding-right: 4px; }
.receipt-total { display: flex; justify-content: space-between; font-size: 14px; font-weight: bold; margin: 6px 0; }
.receipt-footer { text-align: center; margin-top: 8px; font-size: 11px; color: #555; }
.receipt-footer p { margin: 2px 0; }
@media print { .receipt { width: 80mm; padding: 0; } }
</style>
