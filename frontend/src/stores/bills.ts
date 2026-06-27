import { defineStore } from 'pinia'
import { ref } from 'vue'
import BillRepository from '../Repositories/BillRepository'
import { useOfflineStore } from './offline'
import { v4 as uuidv4 } from 'uuid'
import type { Bill } from '../types/index'

export const useBillsStore = defineStore('bills', () => {
  const bills = ref<Bill[]>([])
  const loading = ref(false)

  async function fetchBills() {
    loading.value = true
    try {
      const { data } = await BillRepository.getAll()
      bills.value = Array.isArray(data) ? data : (data as any).data
    } finally {
      loading.value = false
    }
  }

  async function submitBill(billData: Omit<Bill, 'uuid'>) {
    const offlineStore = useOfflineStore()
    const bill: Bill = { ...billData, uuid: uuidv4() }
    if (offlineStore.isOffline) {
      await offlineStore.queueBill(bill)
      return { offline: true, bill }
    }
    const { data } = await BillRepository.create(bill)
    return { offline: false, bill: data }
  }

  return { bills, loading, fetchBills, submitBill }
})
