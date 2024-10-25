import { EditArticleForm } from './EditArticleForm';
import { Suspense } from 'react';

interface PageProps {
  params: {
    id: string;
  };
}

export default function EditArticlePage({ params }: PageProps) {
  return (
    <Suspense fallback={
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900" />
      </div>
    }>
      <EditArticleForm id={params.id} />
    </Suspense>
  );
}
