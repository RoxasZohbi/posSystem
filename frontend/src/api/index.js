import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
  withCredentials: true,
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export const login = (credentials) => api.post('/login', credentials)
export const logout = () => api.post('/logout')
export const me = () => api.get('/me')

export const getServices = () => api.get('/services')
export const createService = (data) => api.post('/services', data)
export const updateService = (id, data) => api.put(`/services/${id}`, data)
export const deleteService = (id) => api.delete(`/services/${id}`)

export const getStaff = () => api.get('/staff')
export const createStaff = (data) => api.post('/staff', data)
export const updateStaff = (id, data) => api.put(`/staff/${id}`, data)
export const deleteStaff = (id) => api.delete(`/staff/${id}`)

export const getDeals = () => api.get('/deals')
export const createDeal = (data) => api.post('/deals', data)
export const updateDeal = (id, data) => api.put(`/deals/${id}`, data)
export const deleteDeal = (id) => api.delete(`/deals/${id}`)

export const getBills = () => api.get('/bills')
export const createBill = (data) => api.post('/bills', data)
export const syncBills = (bills) => api.post('/bills/sync', { bills })

export const getExpenses = (params) => api.get('/expenses', { params })
export const createExpense = (data) => api.post('/expenses', data)
export const deleteExpense = (id) => api.delete(`/expenses/${id}`)

export const getDailyReport = (date) => api.get('/reports/daily', { params: { date } })

export default api
