'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { toast } from 'react-hot-toast';
import { articlesApi } from '@/services/api';
import { Button } from '@/components/ui/Button';
import { PlusIcon, PencilIcon, TrashIcon, CalendarIcon } from 'lucide-react';
import { formatDate } from '@/utils/date';
import type { Article } from '@/types/api';

export default function ArticlesPage() {
  const router = useRouter();
  const [articles, setArticles] = useState<Article[]>([]);
  const [loading, setLoading] = useState(true);
  const [deletingId, setDeletingId] = useState<string | null>(null);

  const fetchArticles = async () => {
    try {
      const response = await articlesApi.getAll();
      if (response.success && Array.isArray(response.data)) {
        setArticles(response.data);
      } else {
        setArticles([]);
      }
    } catch (error) {
      console.error('Failed to fetch articles:', error);
      toast.error('Failed to fetch articles');
      setArticles([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchArticles();
  }, []);

  const handleEdit = (articleId: string) => {
    router.push(`/articles/${encodeURIComponent(articleId)}/edit`);
  };

  const handleNew = () => {
    router.push('/articles/new');
  };


  const handleDelete = async (id: string) => {
    if (!confirm('Are you sure you want to delete this article?')) {
      return;
    }

    setDeletingId(id);
    try {
      await articlesApi.delete(id);
      toast.success('Article deleted successfully');
      fetchArticles(); // Refresh the list
    } catch (error) {
      toast.error('Failed to delete article');
    } finally {
      setDeletingId(null);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900" />
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-5xl mx-auto">
        <div className="bg-white shadow rounded-lg">
          <div className="px-6 py-5 border-b border-gray-200">
            <div className="flex items-center justify-between">
              <h1 className="text-2xl font-bold text-gray-900">My Articles</h1>
                <Button
                  onClick={handleNew}
                  className="inline-flex items-center"
                >
                  <PlusIcon className="w-4 h-4 mr-2" />
                  New Article
                </Button>
            </div>
          </div>

          <div className="px-6 py-6">
            <div className="space-y-6">
              {articles.length === 0 ? (
                <div className="text-center py-12">
                  <p className="text-gray-500">No articles yet. Create your first one!</p>
                </div>
              ) : (
                articles.map((article) => (
                  <div
                    key={article.id}
                    className="bg-gray-50 rounded-lg p-6 shadow-sm"
                  >
                    <div className="flex items-start justify-between">
                      <div className="flex-1 mr-4">
                        <h3 className="text-lg font-medium text-gray-900">
                          {article.title}
                        </h3>
                        <div className="mt-1 flex items-center text-sm text-gray-500">
                          <CalendarIcon className="w-4 h-4 mr-1" />
                          {formatDate(article.publishedAt)}
                        </div>
                        <p className="mt-3 text-gray-600 line-clamp-3">
                          {article.content}
                        </p>
                      </div>
                      <div className="flex space-x-2 flex-shrink-0">
                        <Button
                          onClick={() => handleEdit(article.id)}
                          className="bg-gray-600 hover:bg-gray-700"
                        >
                          <PencilIcon className="w-4 h-4" />
                        </Button>
                        <Button
                          onClick={() => handleDelete(article.id)}
                          isLoading={deletingId === article.id}
                          className="bg-red-600 hover:bg-red-700"
                        >
                          <TrashIcon className="w-4 h-4" />
                        </Button>
                      </div>
                    </div>
                  </div>
                ))
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
