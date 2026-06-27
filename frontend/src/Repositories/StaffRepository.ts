import Client from './Clients/AxiosClient'
import type { StaffMember } from '../types/index'

const resource = '/staff'

export default {
  getAll() {
    return Client.get<StaffMember[]>(resource)
  },
  create(data: Omit<StaffMember, 'id'>) {
    return Client.post<StaffMember>(resource, data)
  },
  update(id: number, data: Partial<Omit<StaffMember, 'id'>>) {
    return Client.put<StaffMember>(`${resource}/${id}`, data)
  },
  delete(id: number) {
    return Client.delete(`${resource}/${id}`)
  },
}
