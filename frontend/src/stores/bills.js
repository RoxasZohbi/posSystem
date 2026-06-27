import { defineStore } from 'pinia'
import { ref } from 'vue'
import { getBills, createBill } from '../api/index.js'
import { useOfflineStore } from './offline.js'
import { v4 as uuidv4 } from 'uuid'

export const useBillsStore = defineStore('bills', () => {
  const bills = ref([])
  const loading = ref(false)

  async function fetchBills() {
    loading.value = true
    try {
      const { data } = await getBills()
      bills.value = data.data || data
    } finally {
      loading.value = false
    }
  }

  async function submitBill(billData) {
    const offlineStore = useOfflineStore()
    const bill = { ...billData, uuid: uuidv4() }
    if (offlineStore.isOffline) {
      await offlineStore.queueBill(bill)
      return { offline: true, bill }
    }
    const { data } = await createBill(bill)
    return { offline: false, bill: data }
  }

  return { bills, loading, fetchBills, submitBill }
})
