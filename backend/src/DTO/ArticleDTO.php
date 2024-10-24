<?php

namespace App\DTO;

use App\Document\Article;

class ArticleDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $title,
        public readonly string $content,
        public readonly ?string $publishedAt
    ) {}

    public static function fromEntity(Article $article): self
    {
        return new self(
            $article->getId(),
            $article->getTitle(),
            $article->getContent(),
            $article->getPublishedAt()->format('c')
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            null,
            $data['title'],
            $data['content'],
            null
        );
    }
}
