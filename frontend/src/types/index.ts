export interface User {
  id: number
  name: string
  email: string
  roles: string[]
  permissions: string[]
}

export interface Service {
  id: number
  name: string
  description?: string
  price: number | string
  is_active: boolean
}

export interface StaffMember {
  id: number
  name: string
  email?: string
  phone: string
  nic: string
}

export interface Deal {
  id: number
  name: string
  price: number | string
  is_active: boolean
  services?: Pick<Service, 'id' | 'name'>[]
}

export interface BillItem {
  id?: number
  name: string
  price: number | string
  type: 'service' | 'deal'
  service_id?: number | null
  deal_id?: number | null
}

export interface Bill {
  id?: number
  uuid: string
  customer_name: string
  customer_phone: string
  staff_id: number | string
  staff?: StaffMember
  staff_name?: string
  items: BillItem[]
  total?: number | string
  created_at?: string
  status?: string
}

export interface Expense {
  id: number
  date: string
  amount: number | string
  note?: string
  logged_by?: { name: string }
}

export interface StaffSummary {
  staff_id: number
  staff_name: string
  bill_count: number
  total: number
}

export interface DailyReport {
  date: string
  total_revenue: number
  total_expenses: number
  net: number
  bill_count: number
  staff_summary?: StaffSummary[]
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface AuthResponse {
  token: string
  user: User
}
