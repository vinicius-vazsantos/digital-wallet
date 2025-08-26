import { api } from './api';
import { toast } from 'react-toastify';

export interface Account {
  id: number;
  name: string;
  balance: number;
}

export interface ResponseAccount {
    data: Account[];
    total: number;
    totalPages: number;
}

export const userService = {
  async getPaginated(
    page: number = 1,
    limit: number = 10,
    nome?: string
  ): Promise<ResponseAccount> {
    try {
      const response = await api.get('/accounts', {
        params: { page, limit, ...(nome && { nome }) },
      });
      return {
        data: response.data.data,
        total: response.data.total,
        totalPages: response.data.totalPages,
      };
    } catch (error) {
      throw this.handleError(error, 'Erro ao buscar pessoas');
    }
  },

  async getAccountById(uuid: string): Promise<Account> {
    const response = await api.get(`/accounts/${uuid}`);
    return response.data;
  },

  async createAccount(account: Omit<Account, 'id'>): Promise<Account> {
    const response = await api.post('/accounts', account);
    return response.data;
  },

  async delete(uuid: string): Promise<void> {
    try {
      await api.delete(`/accounts/${uuid}`);
    } catch (error) {
        console.log(error)
      throw this.handleError(error, 'Erro ao excluir pessoa');
    }
  },

  handleError(error: unknown, defaultMessage: string): Error {

    toast.error(defaultMessage, {
      position: 'top-right',
      autoClose: 5000,
    });

    return new Error(defaultMessage);
  }
};