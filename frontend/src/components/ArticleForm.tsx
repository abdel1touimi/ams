'use client';

import { useState } from 'react';
import { Button } from './ui/Button';
import { XIcon } from 'lucide-react';

interface ArticleFormProps {
  initialData?: {
    title: string;
    content: string;
  };
  onSubmit: (data: { title: string; content: string }) => Promise<void>;
  onCancel: () => void;
  isLoading: boolean;
}

export function ArticleForm({ initialData, onSubmit, onCancel, isLoading }: ArticleFormProps) {
  const [errors, setErrors] = useState<Record<string, string>>({});

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setErrors({});

    const formData = new FormData(e.currentTarget);
    const title = formData.get('title') as string;
    const content = formData.get('content') as string;

    try {
      await onSubmit({ title, content });
    } catch (error: any) {
      if (error.response?.data?.data) {
        setErrors(error.response.data.data);
      }
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div>
        <label htmlFor="title" className="block text-sm font-medium text-gray-700">
          Title
        </label>
        <input
          type="text"
          name="title"
          id="title"
          defaultValue={initialData?.title}
          className={`mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm ${
            errors.title ? 'border-red-500' : ''
          }`}
          placeholder="Enter article title"
        />
        {errors.title && (
          <p className="mt-1 text-sm text-red-600">{errors.title}</p>
        )}
      </div>

      <div>
        <label htmlFor="content" className="block text-sm font-medium text-gray-700">
          Content
        </label>
        <textarea
          name="content"
          id="content"
          rows={8}
          defaultValue={initialData?.content}
          className={`mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm ${
            errors.content ? 'border-red-500' : ''
          }`}
          placeholder="Write your article content here..."
        />
        {errors.content && (
          <p className="mt-1 text-sm text-red-600">{errors.content}</p>
        )}
      </div>

      <div className="flex justify-end space-x-3">
        <Button
          type="button"
          onClick={onCancel}
          className="bg-gray-600 hover:bg-gray-700"
        >
          Cancel
        </Button>
        <Button type="submit" isLoading={isLoading}>
          {initialData ? 'Update Article' : 'Create Article'}
        </Button>
      </div>
    </form>
  );
}
