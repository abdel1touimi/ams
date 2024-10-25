'use client';

import { useState } from 'react';
import Link from 'next/link';
import { toast } from 'react-hot-toast';
import { useAuth } from '@/context/AuthContext';
import { AuthLayout } from '@/components/AuthLayout';
import { Input } from '@/components/ui/Input';
import { Button } from '@/components/ui/Button';

export default function LoginPage() {
  const { login } = useAuth();
  const [isLoading, setIsLoading] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setIsLoading(true);
    setErrors({});

    const formData = new FormData(e.currentTarget);
    const email = formData.get('email') as string;
    const password = formData.get('password') as string;

    try {
      await login(email, password);
      toast.success('Welcome back!');
    } catch (error: any) {
      if (error.response?.data?.data) {
        setErrors(error.response.data.data);
      } else {
        toast.error('Invalid email or password');
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <AuthLayout>
      <div className="text-center">
        <h2 className="text-3xl font-bold">Welcome back</h2>
        <p className="mt-2 text-gray-600">
          Don't have an account?{' '}
          <Link href="/register" className="text-blue-600 hover:text-blue-500">
            Sign up
          </Link>
        </p>
      </div>

      <form onSubmit={handleSubmit} className="mt-8 space-y-6">
        <Input
          label="Email"
          name="email"
          type="email"
          required
          error={errors.email}
          autoComplete="email"
        />

        <Input
          label="Password"
          name="password"
          type="password"
          required
          error={errors.password}
          autoComplete="current-password"
        />

        <Button type="submit" isLoading={isLoading} className="w-full">
          Sign in
        </Button>
      </form>
    </AuthLayout>
  );
}
