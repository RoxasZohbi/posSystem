import Client from './Clients/AxiosClient'
import type { Customer } from '../types/index'

export default {
  search(phone: string) {
    return Client.get<Customer | null>('/customers/search', { params: { phone } })
  },
}
