<?php

namespace App\Service;

use App\Document\Article;
use App\Document\User;
use App\DTO\ArticleDTO;
use App\Exception\ValidationException;
use App\Repository\ArticleRepository;
use App\Validator\ArticleValidator;
use DateTime;

class ArticleService
{
    public function __construct(
        private readonly ArticleRepository $articleRepository,
        private readonly ArticleValidator $articleValidator
    ) {}

    /**
     * Get articles for a specific user
     */
    public function getUserArticles(User $user): array
    {
        $articles = $this->articleRepository->findByAuthor($user->getId());
        return array_map(fn(Article $article) => ArticleDTO::fromEntity($article), $articles);
    }

    /**
     * Get a single article
     */
    public function getArticle(string $id, User $user): ?ArticleDTO
    {
        $article = $this->articleRepository->find($id);

        if (!$article || $article->getAuthorId() !== $user->getId()) {
            return null;
        }

        return ArticleDTO::fromEntity($article);
    }

    /**
     * @throws ValidationException
     */
    public function createArticle(ArticleDTO $articleDTO, User $user): ArticleDTO
    {
        $errors = $this->articleValidator->validateCreate($articleDTO);
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $article = new Article();
        $article->setTitle($articleDTO->title);
        $article->setContent($articleDTO->content);
        $article->setAuthorId($user->getId());
        $article->setPublishedAt(new DateTime());

        $this->articleRepository->save($article);

        return ArticleDTO::fromEntity($article);
    }

    /**
     * @throws ValidationException
     */
    public function updateArticle(string $id, ArticleDTO $articleDTO, User $user): ?ArticleDTO
    {
        $errors = $this->articleValidator->validateUpdate($articleDTO);
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $article = $this->articleRepository->find($id);

        if (!$article || $article->getAuthorId() !== $user->getId()) {
            return null;
        }

        $article->setTitle($articleDTO->title);
        $article->setContent($articleDTO->content);

        $this->articleRepository->save($article);

        return ArticleDTO::fromEntity($article);
    }

    /**
     * Delete an article
     */
    public function deleteArticle(string $id, User $user): bool
    {
        $article = $this->articleRepository->find($id);

        if (!$article || $article->getAuthorId() !== $user->getId()) {
            return false;
        }

        $this->articleRepository->delete($article);
        return true;
    }
}
