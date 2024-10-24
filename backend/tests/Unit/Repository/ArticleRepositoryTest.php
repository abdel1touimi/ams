<?php

namespace App\Tests\Unit\Repository;

use App\Document\Article;
use App\Repository\ArticleRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ArticleRepositoryTest extends TestCase
{
    private ArticleRepository $articleRepository;
    private DocumentManager|MockObject $documentManager;
    private DocumentRepository|MockObject $documentRepository;

    protected function setUp(): void
    {
        $this->documentManager = $this->getMockBuilder(DocumentManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->documentRepository = $this->getMockBuilder(DocumentRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Set up the document manager to return our mocked repository
        $this->documentManager->expects(self::any())
            ->method('getRepository')
            ->with(Article::class)
            ->willReturn($this->documentRepository);

        $this->articleRepository = new ArticleRepository($this->documentManager);
    }

    public function testFindByAuthor(): void
    {
        // Arrange
        $authorId = '507f1f77bcf86cd799439011';
        $articles = [
            new Article(),
            new Article()
        ];

        $this->documentRepository->expects(self::once())
            ->method('findBy')
            ->with(['authorId' => $authorId])
            ->willReturn($articles);

        // Act
        $result = $this->articleRepository->findByAuthor($authorId);

        // Assert
        self::assertSame($articles, $result);
    }

    public function testFind(): void
    {
        // Arrange
        $articleId = '507f1f77bcf86cd799439012';
        $article = new Article();

        $this->documentRepository->expects(self::once())
            ->method('find')
            ->with($articleId)
            ->willReturn($article);

        // Act
        $result = $this->articleRepository->find($articleId);

        // Assert
        self::assertSame($article, $result);
    }

    public function testFindReturnsNull(): void
    {
        // Arrange
        $articleId = '507f1f77bcf86cd799439012';

        $this->documentRepository->expects(self::once())
            ->method('find')
            ->with($articleId)
            ->willReturn(null);

        // Act
        $result = $this->articleRepository->find($articleId);

        // Assert
        self::assertNull($result);
    }

    public function testSave(): void
    {
        // Arrange
        $article = new Article();
        $article->setTitle('Test Article');

        $this->documentManager->expects(self::once())
            ->method('persist')
            ->with($article);

        $this->documentManager->expects(self::once())
            ->method('flush');

        // Act
        $this->articleRepository->save($article);
    }

    public function testDelete(): void
    {
        // Arrange
        $article = new Article();
        $article->setTitle('Test Article');

        $this->documentManager->expects(self::once())
            ->method('remove')
            ->with($article);

        $this->documentManager->expects(self::once())
            ->method('flush');

        // Act
        $this->articleRepository->delete($article);
    }
}
