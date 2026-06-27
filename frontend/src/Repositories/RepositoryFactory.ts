import AuthRepository from './AuthRepository'
import ServiceRepository from './ServiceRepository'
import StaffRepository from './StaffRepository'
import DealRepository from './DealRepository'
import BillRepository from './BillRepository'
import ExpenseRepository from './ExpenseRepository'
import ReportRepository from './ReportRepository'

const repositories = {
  auth: AuthRepository,
  services: ServiceRepository,
  staff: StaffRepository,
  deals: DealRepository,
  bills: BillRepository,
  expenses: ExpenseRepository,
  reports: ReportRepository,
} as const

type Repositories = typeof repositories
type RepositoryName = keyof Repositories

export default {
  get<K extends RepositoryName>(name: K): Repositories[K] {
    return repositories[name]
  },
}
