import Dexie from 'dexie'

const db = new Dexie('SalonPOS')

db.version(1).stores({
  bills: '++id, uuid, status, createdAt',
})

export default db
