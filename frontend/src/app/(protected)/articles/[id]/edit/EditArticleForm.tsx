'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { toast } from 'react-hot-toast';
import { articlesApi } from '@/services/api';
import { ArticleForm } from '@/components/ArticleForm';
import { ArrowLeft } from 'lucide-react';
import type { Article } from '@/types/api';

interface EditArticleFormProps {
  id: string;
}

export function EditArticleForm({ id }: EditArticleFormProps) {
  const router = useRouter();
  const [article, setArticle] = useState<Article | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [isFetching, setIsFetching] = useState(true);

  useEffect(() => {
    let mounted = true;

    const fetchArticle = async () => {
      try {
        const response = await articlesApi.get(id);
        if (mounted) {
          if (response.success) {
            setArticle(response.data);
          } else {
            toast.error('Article not found');
            router.push('/articles');
          }
        }
      } catch (error) {
        if (mounted) {
          console.error('Failed to fetch article:', error);
          toast.error('Failed to fetch article');
          router.push('/articles');
        }
      } finally {
        if (mounted) {
          setIsFetching(false);
        }
      }
    };

    fetchArticle();

    return () => {
      mounted = false;
    };
  }, [id, router]);

  const handleSubmit = async (data: { title: string; content: string }) => {
    setIsLoading(true);
    try {
      await articlesApi.update(id, data);
      toast.success('Article updated successfully');
      router.push('/articles');
    } catch (error) {
      console.error('Failed to update article:', error);
      toast.error('Failed to update article');
      throw error;
    } finally {
      setIsLoading(false);
    }
  };

  if (isFetching) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900" />
      </div>
    );
  }

  if (!article) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-gray-500">Article not found</div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-3xl mx-auto">
        <div className="bg-white shadow rounded-lg">
          <div className="px-6 py-5 border-b border-gray-200">
            <div className="flex items-center">
              <button
                type="button"
                onClick={() => router.back()}
                className="mr-4 text-gray-400 hover:text-gray-600"
              >
                <ArrowLeft className="w-5 h-5" />
              </button>
              <h1 className="text-2xl font-bold text-gray-900">Edit Article</h1>
            </div>
          </div>

          <div className="px-6 py-6">
            <ArticleForm
              initialData={{
                title: article.title,
                content: article.content
              }}
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
