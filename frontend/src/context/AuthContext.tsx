'use client';

import { createContext, useContext, useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { setCookie, deleteCookie } from 'cookies-next';
import { authApi } from '@/services/api';

interface User {
  id: string;
  name: string;
  email: string;
}

interface AuthContextType {
  user: User | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => void;
  updateUser: (data: Partial<User>) => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  const saveToken = (token: string) => {
    // Save to both localStorage and cookies
    if (typeof window !== 'undefined') {
      localStorage.setItem('token', token);
    }
    setCookie('token', token, {
      maxAge: 30 * 24 * 60 * 60, // 30 days
      path: '/',
    });
  };

  const removeToken = () => {
    if (typeof window !== 'undefined') {
      localStorage.removeItem('token');
    }
    deleteCookie('token');
  };

  useEffect(() => {
    const initAuth = async () => {
      // Check both localStorage and cookies for the token
      const token = typeof window !== 'undefined'
        ? localStorage.getItem('token')
        : null;

      if (token) {
        try {
          const userData = await authApi.getProfile();
          setUser(userData.data);
        } catch (error) {
          removeToken();
        }
      }
      setLoading(false);
    };

    initAuth();
  }, []);

  const login = async (email: string, password: string) => {
    try {
      const response = await authApi.login(email, password);
      saveToken(response.token);

      const userData = await authApi.getProfile();
      setUser(userData.data);

      // Use replace instead of push
      router.replace('/profile');
    } catch (error: any) {
      console.error('Login error:', error);
      throw error;
    }
  };

  const logout = () => {
    removeToken();
    setUser(null);
    router.replace('/login');
  };

  const updateUser = async (data: Partial<User>) => {
    const response = await authApi.updateProfile(data);
    setUser(response.data);
    return response.data;
  };

  return (
    <AuthContext.Provider value={{ user, loading, login, logout, updateUser }}>
      {!loading && children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
