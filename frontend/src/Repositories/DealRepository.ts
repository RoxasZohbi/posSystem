import Client from './Clients/AxiosClient'
import type { Deal } from '../types/index'

const resource = '/deals'

export default {
  getAll() {
    return Client.get<Deal[]>(resource)
  },
  create(data: Omit<Deal, 'id' | 'services'>) {
    return Client.post<Deal>(resource, data)
  },
  update(id: number, data: Partial<Omit<Deal, 'id' | 'services'>>) {
    return Client.put<Deal>(`${resource}/${id}`, data)
  },
  delete(id: number) {
    return Client.delete(`${resource}/${id}`)
  },
}
