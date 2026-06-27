import Client from './Clients/AxiosClient'
import type { Bill } from '../types/index'

const resource = '/bills'

export default {
  getAll() {
    return Client.get<Bill[]>(resource)
  },
  create(data: Omit<Bill, 'id'>) {
    return Client.post<Bill>(resource, data)
  },
  sync(bills: Bill[]) {
    return Client.post<{ synced: string[] }>(`${resource}/sync`, { bills })
  },
}
