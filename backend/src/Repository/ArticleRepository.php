<?php

namespace App\Repository;

use App\Document\Article;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class ArticleRepository
{
    private DocumentRepository $repository;

    public function __construct(private readonly DocumentManager $dm)
    {
        $this->repository = $dm->getRepository(Article::class);
    }

    /**
     * @return Article[]
     */
    public function findByAuthor(string $authorId): array
    {
        return $this->repository->findBy(['authorId' => $authorId]);
    }

    public function find(string $id): ?Article
    {
        return $this->repository->find($id);
    }

    public function save(Article $article): void
    {
        $this->dm->persist($article);
        $this->dm->flush();
    }

    public function delete(Article $article): void
    {
        $this->dm->remove($article);
        $this->dm->flush();
    }
}
