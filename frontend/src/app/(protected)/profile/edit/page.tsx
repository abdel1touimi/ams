'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { toast } from 'react-hot-toast';
import { useAuth } from '@/context/AuthContext';
import { Input } from '@/components/ui/Input';
import { Button } from '@/components/ui/Button';
import { ArrowLeft } from 'lucide-react';

export default function EditProfilePage() {
  const { user, updateUser } = useAuth();
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(false);
  const [errors, setErrors] = useState<Record<string, string>>({});

  if (!user) {
    return null;
  }

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setIsLoading(true);
    setErrors({});

    const formData = new FormData(e.currentTarget);
    const name = formData.get('name') as string;
    const email = formData.get('email') as string;

    try {
      await updateUser({ name, email });
      toast.success('Profile updated successfully');
      router.push('/profile');
    } catch (error: any) {
      if (error.response?.data?.data) {
        setErrors(error.response.data.data);
      } else {
        toast.error('Failed to update profile');
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
              <h3 className="text-2xl font-bold text-gray-900">Edit Profile</h3>
            </div>
          </div>

          <form onSubmit={handleSubmit} className="px-6 py-6 space-y-6">
            <Input
              label="Name"
              name="name"
              type="text"
              defaultValue={user.name}
              required
              error={errors.name}
            />

            <Input
              label="Email"
              name="email"
              type="email"
              defaultValue={user.email}
              required
              error={errors.email}
            />

            <div className="flex justify-end">
              <Button type="submit" isLoading={isLoading}>
                Save Changes
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
