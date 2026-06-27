import Client from './Clients/AxiosClient'
import type { DailyReport } from '../types/index'

export default {
  daily(date: string) {
    return Client.get<DailyReport>('/reports/daily', { params: { date } })
  },
}
