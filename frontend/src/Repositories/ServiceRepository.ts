import Client from './Clients/AxiosClient'
import type { Service } from '../types/index'

const resource = '/services'

export default {
  getAll() {
    return Client.get<Service[]>(resource)
  },
  create(data: Omit<Service, 'id'>) {
    return Client.post<Service>(resource, data)
  },
  update(id: number, data: Partial<Omit<Service, 'id'>>) {
    return Client.put<Service>(`${resource}/${id}`, data)
  },
  delete(id: number) {
    return Client.delete(`${resource}/${id}`)
  },
}
