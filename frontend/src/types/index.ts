export interface Account {
  id: number;
  name: string;
  balance: number;
}

export interface AuthResponse {
  token: string;
  user: Account;
}

export interface LoginCredentials {
  email: string;
  password: string;
}