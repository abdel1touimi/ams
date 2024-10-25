'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { toast } from 'react-hot-toast';
import { authApi } from '@/services/api';
import { AuthLayout } from '@/components/AuthLayout';
import { Input } from '@/components/ui/Input';
import { Button } from '@/components/ui/Button';

export default function RegisterPage() {
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setIsLoading(true);
    setErrors({});

    const formData = new FormData(e.currentTarget);
    const name = formData.get('name') as string;
    const email = formData.get('email') as string;
    const password = formData.get('password') as string;
    const passwordConfirm = formData.get('passwordConfirm') as string;

    if (password !== passwordConfirm) {
      setErrors({ passwordConfirm: 'Passwords do not match' });
      setIsLoading(false);
      return;
    }

    try {
      await authApi.register(name, email, password);
      toast.success('Registration successful! Please log in.');
      router.push('/login');
    } catch (error: any) {
      if (error.response?.data?.data) {
        setErrors(error.response.data.data);
      } else {
        toast.error('Registration failed. Please try again.');
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <AuthLayout>
      <div className="text-center">
        <h2 className="text-3xl font-bold">Create an account</h2>
        <p className="mt-2 text-gray-600">
          Already have an account?{' '}
          <Link href="/login" className="text-blue-600 hover:text-blue-500">
            Sign in
          </Link>
        </p>
      </div>

      <form onSubmit={handleSubmit} className="mt-8 space-y-6">
        <Input
          label="Name"
          name="name"
          type="text"
          required
          error={errors.name}
          autoComplete="name"
        />

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
          autoComplete="new-password"
        />

        <Input
          label="Confirm Password"
          name="passwordConfirm"
          type="password"
          required
          error={errors.passwordConfirm}
          autoComplete="new-password"
        />

        <Button type="submit" isLoading={isLoading} className="w-full">
          Create account
        </Button>
      </form>
    </AuthLayout>
  );
}
