export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
}

export interface Article {
  id: string;
  title: string;
  content: string;
  authorId: string;
  publishedAt: string;
}

export interface ArticleCreate {
  title: string;
  content: string;
}

export interface ArticleUpdate extends ArticleCreate {
  id: string;
}
