'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { toast } from 'react-hot-toast';
import { authApi } from '@/services/api';
import { Input } from '@/components/ui/Input';
import { Button } from '@/components/ui/Button';
import { ArrowLeft } from 'lucide-react';

export default function ChangePasswordPage() {
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setIsLoading(true);
    setErrors({});

    const formData = new FormData(e.currentTarget);
    const currentPassword = formData.get('currentPassword') as string;
    const newPassword = formData.get('newPassword') as string;
    const confirmPassword = formData.get('confirmPassword') as string;

    if (newPassword !== confirmPassword) {
      setErrors({ confirmPassword: 'Passwords do not match' });
      setIsLoading(false);
      return;
    }

    try {
      await authApi.updatePassword({
        currentPassword,
        newPassword
      });
      toast.success('Password changed successfully');
      router.push('/profile');
    } catch (error: any) {
      if (error.response?.data?.data) {
        setErrors(error.response.data.data);
      } else {
        toast.error('Failed to change password');
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-md mx-auto">
        <div className="bg-white shadow rounded-lg">
          <div className="px-6 py-5 border-b border-gray-200">
            <div className="flex items-center">
              <button
                onClick={() => router.push('/profile')}
                className="mr-4 text-gray-400 hover:text-gray-600"
              >
                <ArrowLeft className="w-5 h-5" />
              </button>
              <h3 className="text-2xl font-bold text-gray-900">Change Password</h3>
            </div>
          </div>

          <form onSubmit={handleSubmit} className="px-6 py-6 space-y-6">
            <Input
              label="Current Password"
              name="currentPassword"
              type="password"
              required
              error={errors.currentPassword}
            />

            <Input
              label="New Password"
              name="newPassword"
              type="password"
              required
              error={errors.newPassword}
            />

            <Input
              label="Confirm New Password"
              name="confirmPassword"
              type="password"
              required
              error={errors.confirmPassword}
            />

            <div className="flex justify-end">
              <Button type="submit" isLoading={isLoading}>
                Change Password
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
