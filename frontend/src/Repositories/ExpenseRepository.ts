import Client from './Clients/AxiosClient'
import type { Expense } from '../types/index'

const resource = '/expenses'

export default {
  getAll(params?: Record<string, string>) {
    return Client.get<Expense[]>(resource, { params })
  },
  create(data: Omit<Expense, 'id' | 'logged_by'>) {
    return Client.post<Expense>(resource, data)
  },
  delete(id: number) {
    return Client.delete(`${resource}/${id}`)
  },
}
