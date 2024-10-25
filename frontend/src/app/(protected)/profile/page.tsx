'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/context/AuthContext';
import { articlesApi } from '@/services/api';
import { Button } from '@/components/ui/Button';
import { UserIcon, PencilIcon, BookOpenIcon, LogOutIcon, ClockIcon } from 'lucide-react';
import { formatDate } from '@/utils/date';
import type { Article } from '@/types/api';

export default function ProfilePage() {
  const { user, logout } = useAuth();
  const router = useRouter();
  const [articles, setArticles] = useState<Article[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchArticles = async () => {
      try {
        const response = await articlesApi.getAll();
        if (response.success && Array.isArray(response.data)) {
          setArticles(response.data);
        }
      } catch (error) {
        console.error('Error fetching articles:', error);
        setError('Failed to fetch articles');
      } finally {
        setLoading(false);
      }
    };

    fetchArticles();
  }, []);

  if (!user) {
    return null;
  }

  const handleEditProfile = () => {
    router.push('/profile/edit');
  };

  const handleEditPassword = () => {
    router.push('/profile/password');
  };

  const handleViewArticles = () => {
    router.push('/articles');
  };

  const latestArticles = articles.slice(0, 3); // Show only the 3 most recent articles

  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-3xl mx-auto">
        <div className="bg-white shadow rounded-lg">
          {/* Header */}
          <div className="px-6 py-5 border-b border-gray-200">
            <div className="flex items-center justify-between">
              <h3 className="text-2xl font-bold text-gray-900">Profile Overview</h3>
              <Button
                onClick={logout}
                className="bg-red-600 hover:bg-red-700 inline-flex items-center"
              >
                <LogOutIcon className="w-4 h-4 mr-2" />
                Sign Out
              </Button>
            </div>
          </div>

          {/* Profile Info */}
          <div className="px-6 py-6">
            <div className="grid grid-cols-1 gap-6">
              {/* User Info Card */}
              <div className="bg-gray-50 p-6 rounded-lg">
                <div className="flex items-center space-x-4 mb-4">
                  <div className="bg-blue-100 p-3 rounded-full">
                    <UserIcon className="w-6 h-6 text-blue-600" />
                  </div>
                  <div>
                    <h4 className="text-lg font-medium">{user.name}</h4>
                    <p className="text-gray-600">{user.email}</p>
                  </div>
                </div>

                {/* Articles Count */}
                <div className="mt-4 p-4 bg-white rounded-md shadow-sm">
                  <div className="flex items-center space-x-2 mb-4">
                    <BookOpenIcon className="w-5 h-5 text-blue-600" />
                    <span className="text-gray-600">
                      {loading ? (
                        'Loading...'
                      ) : error ? (
                        error
                      ) : (
                        `${articles.length} Articles Published`
                      )}
                    </span>
                  </div>

                  {/* Latest Articles */}
                  {!loading && !error && latestArticles.length > 0 && (
                    <div className="mt-4">
                      <h5 className="text-sm font-medium text-gray-700 mb-2">Latest Articles</h5>
                      <div className="space-y-3">
                        {latestArticles.map(article => (
                          <div key={article.id} className="bg-gray-50 p-3 rounded-md">
                            <h6 className="text-sm font-medium">{article.title}</h6>
                            <div className="flex items-center mt-1 text-xs text-gray-500">
                              <ClockIcon className="w-3 h-3 mr-1" />
                              {formatDate(article.publishedAt)}
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}
                </div>
              </div>

              {/* Actions */}
              <div className="flex flex-col space-y-4">
                <Button onClick={handleEditProfile} className="inline-flex items-center justify-center">
                  <PencilIcon className="w-4 h-4 mr-2" />
                  Edit Profile
                </Button>
                <Button
                  onClick={handleEditPassword}
                  className="bg-gray-600 hover:bg-gray-700 inline-flex items-center justify-center"
                >
                  <PencilIcon className="w-4 h-4 mr-2" />
                  Change Password
                </Button>
                <Button
                  onClick={handleViewArticles}
                  className="bg-green-600 hover:bg-green-700 inline-flex items-center justify-center"
                >
                  <BookOpenIcon className="w-4 h-4 mr-2" />
                  View Articles
                </Button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
