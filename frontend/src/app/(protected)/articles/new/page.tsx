'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { toast } from 'react-hot-toast';
import { articlesApi } from '@/services/api';
import { ArticleForm } from '@/components/ArticleForm';
import { ArrowLeft } from 'lucide-react';

export default function NewArticlePage() {
  const router = useRouter();
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = async (data: { title: string; content: string }) => {
    setIsLoading(true);
    try {
      await articlesApi.create(data);
      toast.success('Article created successfully');
      router.push('/articles');
    } catch (error) {
      toast.error('Failed to create article');
      throw error; // Propagate to form for error handling
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-3xl mx-auto">
        <div className="bg-white shadow rounded-lg">
          <div className="px-6 py-5 border-b border-gray-200">
            <div className="flex items-center">
              <button
                onClick={() => router.back()}
                className="mr-4 text-gray-400 hover:text-gray-600"
              >
                <ArrowLeft className="w-5 h-5" />
              </button>
              <h1 className="text-2xl font-bold text-gray-900">Create New Article</h1>
            </div>
          </div>

          <div className="px-6 py-6">
            <ArticleForm
              onSubmit={handleSubmit}
              onCancel={() => router.back()}
              isLoading={isLoading}
            />
          </div>
        </div>
      </div>
    </div>
  );
}
