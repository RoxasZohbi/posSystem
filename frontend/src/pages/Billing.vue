<template>
  <div class="page">
    <h2 class="page-title">New Bill</h2>
    <div class="billing-grid">
      <div class="left-panel">
        <div class="form-card">
          <h3>Customer Info</h3>
          <div class="form-group"><label>Customer Name</label><input v-model="form.customer_name" required /></div>
          <div class="form-group"><label>Customer Phone</label><input v-model="form.customer_phone" required /></div>
          <div class="form-group">
            <label>Staff</label>
            <select v-model="form.staff_id" required>
              <option value="">Select staff...</option>
              <option v-for="s in staffList" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
        </div>
        <div class="form-card">
          <h3>Add Items</h3>
          <div class="tabs">
            <button :class="['tab', activeTab === 'service' && 'active']" @click="activeTab = 'service'">Services</button>
            <button :class="['tab', activeTab === 'deal' && 'active']" @click="activeTab = 'deal'">Deals</button>
          </div>
          <div class="item-list">
            <template v-if="activeTab === 'service'">
              <div v-for="s in services" :key="s.id" class="item-row" @click="addItem('service', s)">
                <span>{{ s.name }}</span><span>PKR {{ parseFloat(s.price).toFixed(2) }}</span>
              </div>
            </template>
            <template v-else>
              <div v-for="d in deals" :key="d.id" class="item-row" @click="addItem('deal', d)">
                <span>{{ d.name }}</span><span>PKR {{ parseFloat(d.price).toFixed(2) }}</span>
              </div>
            </template>
          </div>
        </div>
      </div>
      <div class="right-panel">
        <div class="form-card">
          <h3>Bill Summary</h3>
          <div v-if="!form.items.length" class="empty">No items added yet.</div>
          <div v-else>
            <div v-for="(item, i) in form.items" :key="i" class="receipt-item">
              <span>{{ item.name }}</span>
              <div style="display:flex;gap:0.5rem;align-items:center">
                <span>PKR {{ parseFloat(item.price).toFixed(2) }}</span>
                <button @click="removeItem(i)" class="remove-btn">x</button>
              </div>
            </div>
            <div class="receipt-total"><strong>Total</strong><strong>PKR {{ total.toFixed(2) }}</strong></div>
          </div>
          <p v-if="offline.isOffline" class="offline-notice">Offline — bill will be queued for sync.</p>
          <button @click="submit" :disabled="submitting || !form.items.length || !form.customer_name || !form.staff_id" class="btn-submit">
            {{ submitting ? 'Saving...' : offline.isOffline ? 'Queue Bill' : 'Save Bill' }}
          </button>
          <p v-if="successMsg" class="success-msg">{{ successMsg }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { getServices, getDeals, getStaff } from '../api/index.js'
import { useBillsStore } from '../stores/bills.js'
import { useOfflineStore } from '../stores/offline.js'

const billsStore = useBillsStore()
const offline = useOfflineStore()
const services = ref([])
const deals = ref([])
const staffList = ref([])
const activeTab = ref('service')
const submitting = ref(false)
const successMsg = ref('')
const form = ref({ customer_name: '', customer_phone: '', staff_id: '', items: [] })
const total = computed(() => form.value.items.reduce((sum, i) => sum + parseFloat(i.price), 0))

function addItem(type, item) {
  form.value.items.push({ type, service_id: type === 'service' ? item.id : null, deal_id: type === 'deal' ? item.id : null, name: item.name, price: item.price })
}
function removeItem(i) { form.value.items.splice(i, 1) }

async function submit() {
  submitting.value = true; successMsg.value = ''
  try {
    const result = await billsStore.submitBill({ ...form.value })
    successMsg.value = result.offline ? 'Bill queued for sync.' : 'Bill saved!'
    form.value = { customer_name: '', customer_phone: '', staff_id: '', items: [] }
  } finally { submitting.value = false }
}

onMounted(async () => {
  const [s, d, st] = await Promise.all([getServices(), getDeals(), getStaff()])
  services.value = s.data.filter(x => x.is_active)
  deals.value = d.data.filter(x => x.is_active)
  staffList.value = st.data
})
</script>

<style scoped>
.page { max-width: 1100px; }
.page-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 1.5rem; color: #1a1a2e; }
.billing-grid { display: grid; grid-template-columns: 1fr 360px; gap: 1.5rem; }
.form-card { background: #fff; border-radius: 8px; padding: 1.25rem; margin-bottom: 1rem; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
.form-card h3 { margin: 0 0 1rem; font-size: 1rem; font-weight: 600; }
.form-group { display: flex; flex-direction: column; gap: 0.25rem; margin-bottom: 0.75rem; }
.form-group label { font-size: 0.875rem; font-weight: 500; color: #374151; }
.form-group input, .form-group select { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.95rem; }
.tabs { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
.tab { padding: 0.4rem 1rem; border: 1px solid #d1d5db; border-radius: 6px; background: #f9fafb; cursor: pointer; font-size: 0.875rem; }
.tab.active { background: #1a1a2e; color: #fff; border-color: #1a1a2e; }
.item-list { display: flex; flex-direction: column; gap: 0.5rem; max-height: 260px; overflow-y: auto; }
.item-row { display: flex; justify-content: space-between; padding: 0.5rem 0.75rem; border-radius: 6px; background: #f9fafb; cursor: pointer; font-size: 0.875rem; }
.item-row:hover { background: #e5e7eb; }
.empty { color: #9ca3af; font-size: 0.875rem; text-align: center; padding: 1.5rem 0; }
.receipt-item { display: flex; justify-content: space-between; padding: 0.4rem 0; border-bottom: 1px solid #f3f4f6; font-size: 0.875rem; }
.remove-btn { background: none; border: none; cursor: pointer; color: #ef4444; font-size: 0.75rem; }
.receipt-total { display: flex; justify-content: space-between; padding: 0.75rem 0 0; font-size: 1rem; }
.offline-notice { color: #f59e0b; font-size: 0.8rem; background: #fffbeb; padding: 0.5rem; border-radius: 4px; margin-top: 0.75rem; }
.btn-submit { width: 100%; margin-top: 1rem; padding: 0.75rem; background: #1a1a2e; color: #fff; border: none; border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer; }
.btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }
.success-msg { color: #22c55e; font-size: 0.875rem; text-align: center; margin-top: 0.5rem; }
</style>
