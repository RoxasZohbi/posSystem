import Dexie, { type Table } from 'dexie'
import type { Bill } from '../types/index'

type QueuedBill = Bill & { status: string; createdAt: string }

class SalonDatabase extends Dexie {
  bills!: Table<QueuedBill>

  constructor() {
    super('SalonPOS')
    this.version(1).stores({
      bills: '++id, uuid, status, createdAt',
    })
  }
}

const db = new SalonDatabase()
export default db
