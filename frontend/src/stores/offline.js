import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'
import { useOnline } from '@vueuse/core'
import db from '../db/dexie.js'
import { syncBills } from '../api/index.js'

export const useOfflineStore = defineStore('offline', () => {
  const online = useOnline()
  const syncing = ref(false)
  const lastSyncAt = ref(null)
  const queuedBillCount = ref(0)

  const isOffline = computed(() => !online.value)

  async function refreshQueueCount() {
    queuedBillCount.value = await db.bills.where('status').equals('pending').count()
  }

  async function queueBill(bill) {
    await db.bills.add({ ...bill, status: 'pending', createdAt: new Date().toISOString() })
    await refreshQueueCount()
  }

  async function sync() {
    if (!online.value || syncing.value) return
    syncing.value = true
    try {
      const pendingBills = await db.bills.where('status').equals('pending').toArray()
      if (pendingBills.length === 0) return
      const { data } = await syncBills(pendingBills)
      for (const uuid of data.synced) {
        await db.bills.where('uuid').equals(uuid).modify({ status: 'synced' })
      }
      lastSyncAt.value = new Date().toISOString()
      await refreshQueueCount()
    } finally {
      syncing.value = false
    }
  }

  watch(online, (isOnline) => { if (isOnline) sync() })
  refreshQueueCount()

  return { online, isOffline, syncing, lastSyncAt, queuedBillCount, queueBill, sync, refreshQueueCount }
})
