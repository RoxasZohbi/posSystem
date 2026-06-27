<template>
  <div class="pos-page">
    <div class="pos-layout">
      <!-- Left: Item Selector -->
      <div class="items-panel">
        <div class="panel-header">
          <div class="tabs">
            <button :class="['tab', activeTab === 'service' && 'active']" @click="activeTab = 'service'">Services</button>
            <button :class="['tab', activeTab === 'deal' && 'active']" @click="activeTab = 'deal'">Deals</button>
          </div>
        </div>
        <div class="item-grid">
          <template v-if="activeTab === 'service'">
            <button v-for="s in services" :key="s.id" @click="addItem('service', s)" class="item-card">
              <div class="item-name">{{ s.name }}</div>
              <div class="item-price">PKR {{ parseFloat(String(s.price)).toFixed(0) }}</div>
            </button>
          </template>
          <template v-else>
            <button v-for="d in deals" :key="d.id" @click="addItem('deal', d)" class="item-card deal">
              <div class="item-badge">Deal</div>
              <div class="item-name">{{ d.name }}</div>
              <div class="item-price">PKR {{ parseFloat(String(d.price)).toFixed(0) }}</div>
            </button>
          </template>
          <div v-if="(activeTab === 'service' ? services : deals).length === 0" class="empty-items">
            No items available
          </div>
        </div>
      </div>

      <!-- Right: Order Panel -->
      <div class="order-panel">
        <!-- Customer Info -->
        <div class="order-section">
          <div class="section-label">Customer</div>
          <div class="field">
            <input v-model="form.customer_phone" @input="onPhoneInput" class="field-input" placeholder="📞 Phone number" />
            <span v-if="customerFound" class="returning-tag">↩ Returning</span>
          </div>
          <input v-model="form.customer_name" class="field-input mt4" placeholder="👤 Customer name" />
        </div>

        <!-- Staff & Payment -->
        <div class="order-section">
          <div class="section-label">Staff &amp; Payment</div>
          <select v-model="form.staff_id" class="field-input">
            <option value="">Select staff...</option>
            <option v-for="s in staffList" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
          <div class="payment-tabs mt4">
            <button v-for="pt in paymentTypes" :key="pt.value"
              :class="['pay-tab', form.payment_type === pt.value && 'active-' + pt.value]"
              @click="form.payment_type = pt.value">
              {{ pt.label }}
            </button>
          </div>
        </div>

        <!-- Bill Items -->
        <div class="order-section order-items">
          <div class="section-label">Order ({{ form.items.length }})</div>
          <div v-if="!form.items.length" class="empty-order">Tap items to add →</div>
          <transition-group name="item-list" tag="div" class="bill-items">
            <div v-for="(item, i) in form.items" :key="i" class="bill-item">
              <div class="bill-item-info">
                <div class="bill-item-name">{{ item.name }}</div>
                <div class="bill-item-price">PKR {{ parseFloat(String(item.price)).toFixed(0) }}</div>
              </div>
              <button @click="removeItem(i)" class="remove-btn">✕</button>
            </div>
          </transition-group>
        </div>

        <!-- Total & Submit -->
        <div class="order-footer">
          <div class="total-line">
            <span>Total</span>
            <span class="total-amount">PKR {{ total.toFixed(0) }}</span>
          </div>
          <p v-if="offline.isOffline" class="offline-notice">⚠️ Offline — will queue for sync</p>
          <button
            @click="submit"
            :disabled="submitting || !form.items.length || !form.customer_name || !form.staff_id"
            class="submit-btn">
            {{ submitting ? 'Processing...' : offline.isOffline ? '📥 Queue Bill' : '✅ Confirm Bill' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import ServiceRepository from '../Repositories/ServiceRepository'
import DealRepository from '../Repositories/DealRepository'
import StaffRepository from '../Repositories/StaffRepository'
import CustomerRepository from '../Repositories/CustomerRepository'
import { useBillsStore } from '../stores/bills'
import { useOfflineStore } from '../stores/offline'
import type { Service, Deal, StaffMember, BillItem } from '../types/index'

interface BillForm {
  customer_name: string
  customer_phone: string
  staff_id: number | string
  payment_type: 'cash' | 'card' | 'online'
  items: BillItem[]
}

const paymentTypes = [
  { value: 'cash' as const, label: '💵 Cash' },
  { value: 'card' as const, label: '💳 Card' },
  { value: 'online' as const, label: '📲 Online' },
]

const router = useRouter()
const billsStore = useBillsStore()
const offline = useOfflineStore()
const services = ref<Service[]>([])
const deals = ref<Deal[]>([])
const staffList = ref<StaffMember[]>([])
const activeTab = ref<'service' | 'deal'>('service')
const submitting = ref(false)
const customerFound = ref(false)
const form = ref<BillForm>({ customer_name: '', customer_phone: '', staff_id: '', payment_type: 'cash', items: [] })
const total = computed(() => form.value.items.reduce((sum, i) => sum + parseFloat(String(i.price)), 0))

let searchTimeout: ReturnType<typeof setTimeout> | null = null

function onPhoneInput() {
  customerFound.value = false
  if (searchTimeout) clearTimeout(searchTimeout)
  if (form.value.customer_phone.length < 3) return
  searchTimeout = setTimeout(async () => {
    try {
      const { data } = await CustomerRepository.search(form.value.customer_phone)
      if (data) { form.value.customer_name = data.name; customerFound.value = true }
    } catch { /* not found */ }
  }, 400)
}

function addItem(type: 'service' | 'deal', item: Service | Deal) {
  form.value.items.push({
    type,
    service_id: type === 'service' ? item.id : null,
    deal_id: type === 'deal' ? item.id : null,
    name: item.name,
    price: item.price,
  })
}

function removeItem(i: number) { form.value.items.splice(i, 1) }

async function submit() {
  submitting.value = true
  try {
    const result = await billsStore.submitBill({ ...form.value })
    if (result.offline) {
      form.value = { customer_name: '', customer_phone: '', staff_id: '', payment_type: 'cash', items: [] }
      customerFound.value = false
    } else {
      router.push({ name: 'Receipt', params: { uuid: result.bill.uuid } })
    }
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  const [s, d, st] = await Promise.all([ServiceRepository.getAll(), DealRepository.getAll(), StaffRepository.getAll()])
  services.value = s.data.filter(x => x.is_active)
  deals.value = d.data.filter(x => x.is_active)
  staffList.value = st.data
})
</script>

<style scoped>
.pos-page { height: calc(100vh - 56px - 3.5rem); display: flex; flex-direction: column; }
.pos-layout { display: grid; grid-template-columns: 1fr 340px; gap: 1rem; flex: 1; min-height: 0; }

/* Items Panel */
.items-panel { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.07); display: flex; flex-direction: column; overflow: hidden; }
.panel-header { padding: 1rem 1rem 0; border-bottom: 1px solid #f1f5f9; }
.tabs { display: flex; gap: 0.5rem; padding-bottom: 0; }
.tab { padding: 0.5rem 1.25rem; border: none; background: none; cursor: pointer; font-size: 0.875rem; font-weight: 500; color: #94a3b8; border-bottom: 2px solid transparent; margin-bottom: -1px; transition: all 0.15s; }
.tab.active { color: #7c3aed; border-bottom-color: #7c3aed; }
.item-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px,1fr)); gap: 0.75rem; padding: 1rem; overflow-y: auto; flex: 1; align-content: start; }
.item-card {
  background: #f8fafc; border: 1.5px solid #e2e8f0;
  border-radius: 10px; padding: 1rem 0.75rem;
  cursor: pointer; text-align: left;
  transition: all 0.15s; position: relative;
  display: flex; flex-direction: column; gap: 0.5rem;
}
.item-card:hover { background: #ede9fe; border-color: #a78bfa; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(124,58,237,0.12); }
.item-card:active { transform: scale(0.97); }
.item-card.deal { background: #fff7ed; border-color: #fed7aa; }
.item-card.deal:hover { background: #ffedd5; border-color: #fb923c; }
.item-badge { font-size: 0.65rem; font-weight: 700; background: #fb923c; color: #fff; padding: 0.1rem 0.4rem; border-radius: 4px; align-self: flex-start; }
.item-name { font-size: 0.85rem; font-weight: 600; color: #0f172a; line-height: 1.3; }
.item-price { font-size: 0.9rem; font-weight: 700; color: #7c3aed; }
.empty-items { grid-column: 1/-1; text-align: center; color: #cbd5e1; padding: 3rem; font-size: 0.875rem; }

/* Order Panel */
.order-panel { background: #0f172a; border-radius: 12px; display: flex; flex-direction: column; overflow: hidden; color: #e2e8f0; }
.order-section { padding: 1rem 1rem 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.06); }
.order-section.order-items { flex: 1; min-height: 0; display: flex; flex-direction: column; overflow: hidden; }
.section-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #64748b; margin-bottom: 0.6rem; }
.field { position: relative; }
.field-input {
  width: 100%; padding: 0.6rem 0.875rem;
  background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px; color: #e2e8f0; font-size: 0.875rem;
  outline: none; transition: border 0.15s;
}
.field-input::placeholder { color: #475569; }
.field-input:focus { border-color: #8b5cf6; background: rgba(139,92,246,0.1); }
select.field-input option { background: #1e293b; color: #e2e8f0; }
.mt4 { margin-top: 0.5rem; }
.returning-tag { position: absolute; right: 0.6rem; top: 50%; transform: translateY(-50%); font-size: 0.7rem; background: #22c55e; color: #fff; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 600; }

.payment-tabs { display: grid; grid-template-columns: repeat(3,1fr); gap: 0.4rem; }
.pay-tab { padding: 0.5rem; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; background: rgba(255,255,255,0.05); color: #94a3b8; cursor: pointer; font-size: 0.75rem; font-weight: 500; transition: all 0.15s; }
.pay-tab:hover { background: rgba(255,255,255,0.1); color: #e2e8f0; }
.pay-tab.active-cash { background: rgba(34,197,94,0.2); border-color: #22c55e; color: #4ade80; }
.pay-tab.active-card { background: rgba(59,130,246,0.2); border-color: #3b82f6; color: #60a5fa; }
.pay-tab.active-online { background: rgba(139,92,246,0.2); border-color: #8b5cf6; color: #a78bfa; }

.bill-items { flex: 1; overflow-y: auto; padding: 0 0; }
.bill-item { display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
.bill-item-name { font-size: 0.825rem; color: #e2e8f0; }
.bill-item-price { font-size: 0.8rem; color: #a78bfa; font-weight: 600; }
.remove-btn { background: none; border: none; color: #475569; cursor: pointer; font-size: 0.75rem; padding: 0.2rem 0.4rem; border-radius: 4px; transition: all 0.15s; flex-shrink: 0; }
.remove-btn:hover { background: rgba(239,68,68,0.2); color: #f87171; }
.empty-order { color: #334155; font-size: 0.825rem; text-align: center; padding: 1.5rem 0; }

.order-footer { padding: 1rem; }
.total-line { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
.total-line span:first-child { font-size: 0.875rem; color: #94a3b8; }
.total-amount { font-size: 1.5rem; font-weight: 700; color: #fff; }
.offline-notice { font-size: 0.75rem; color: #fbbf24; background: rgba(251,191,36,0.1); padding: 0.4rem 0.6rem; border-radius: 6px; margin-bottom: 0.75rem; }
.submit-btn {
  width: 100%; padding: 0.875rem;
  background: linear-gradient(135deg, #7c3aed, #6d28d9);
  color: #fff; border: none; border-radius: 10px;
  font-size: 1rem; font-weight: 700; cursor: pointer;
  transition: all 0.15s; letter-spacing: -0.01em;
}
.submit-btn:hover:not(:disabled) { background: linear-gradient(135deg, #6d28d9, #5b21b6); transform: translateY(-1px); }
.submit-btn:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }

/* Animations */
.item-list-enter-active { transition: all 0.2s ease; }
.item-list-enter-from { opacity: 0; transform: translateX(20px); }
.item-list-leave-active { transition: all 0.15s ease; }
.item-list-leave-to { opacity: 0; transform: translateX(20px); }
</style>
