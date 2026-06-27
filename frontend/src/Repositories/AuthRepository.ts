import Client from './Clients/AxiosClient'
import type { LoginCredentials, AuthResponse, User } from '../types/index'

export default {
  login(credentials: LoginCredentials) {
    return Client.post<AuthResponse>('/login', credentials)
  },
  logout() {
    return Client.post('/logout')
  },
  me() {
    return Client.get<User>('/me')
  },
}
