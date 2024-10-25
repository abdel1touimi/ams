import axios from 'axios';
import type { ApiResponse, Article, ArticleCreate, ArticleUpdate } from '@/types/api';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:80/api';

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Request interceptor for API calls
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor for API calls
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // Handle token expiration
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export const authApi = {
  login: async (email: string, password: string) => {
    const response = await api.post('/login_check', { username: email, password });
    return response.data;
  },
  register: async (name: string, email: string, password: string) => {
    const response = await api.post('/register', { name, email, password });
    return response.data;
  },
  getProfile: async () => {
    const response = await api.get('/me');
    return response.data;
  },
  updateProfile: async (data: { name?: string; email?: string }) => {
    const response = await api.put('/me', data);
    return response.data;
  },
  updatePassword: async (data: { currentPassword: string; newPassword: string }) => {
    const response = await api.put('/me/password', data);
    return response.data;
  },
};

export const articlesApi = {
  getAll: async () => {
    const response = await api.get<ApiResponse<Article[]>>('/articles');
    return response.data;
  },

  get: async (id: string) => {
    const response = await api.get<ApiResponse<Article>>(`/articles/${id}`);
    return response.data;
  },

  create: async (data: ArticleCreate) => {
    const response = await api.post<ApiResponse<Article>>('/articles', data);
    return response.data;
  },

  update: async (id: string, data: ArticleCreate) => {
    const response = await api.put<ApiResponse<Article>>(`/articles/${id}`, data);
    return response.data;
  },

  delete: async (id: string) => {
    const response = await api.delete<ApiResponse<void>>(`/articles/${id}`);
    return response.data;
  }
};

export default api;
