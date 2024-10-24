<?php

namespace App\Tests\Unit\Service;

use App\Document\Article;
use App\Document\User;
use App\DTO\ArticleDTO;
use App\Exception\ValidationException;
use App\Repository\ArticleRepository;
use App\Service\ArticleService;
use App\Validator\ArticleValidator;
use DateTime;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ArticleServiceTest extends TestCase
{
    private ArticleService $articleService;
    private ArticleRepository|MockObject $articleRepository;
    private ArticleValidator|MockObject $validator;
    private User $user;

    protected function setUp(): void
    {
        $this->articleRepository = $this->getMockBuilder(ArticleRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = $this->getMockBuilder(ArticleValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->articleService = new ArticleService(
            $this->articleRepository,
            $this->validator
        );

        // Create a test user
        $this->user = new User();
        $reflection = new \ReflectionClass($this->user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->user, '507f1f77bcf86cd799439011');
    }

    public function testCreateArticle(): void
    {
        // Arrange
        $data = [
            'title' => 'Test Article',
            'content' => 'This is a test article content'
        ];
        $articleDTO = ArticleDTO::fromRequest($data);

        // Setup validator mock
        $this->validator->expects(self::once())
            ->method('validateCreate')
            ->with($articleDTO)
            ->willReturn([]);

        // Setup repository save behavior
        $this->articleRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function(Article $article) use ($data) {
                return $article->getTitle() === $data['title']
                    && $article->getContent() === $data['content']
                    && $article->getAuthorId() === $this->user->getId();
            }))
            ->willReturnCallback(function(Article $article) {
                $reflection = new \ReflectionClass($article);
                $idProperty = $reflection->getProperty('id');
                $idProperty->setAccessible(true);
                $idProperty->setValue($article, '507f1f77bcf86cd799439012');
                return;
            });

        // Act
        $result = $this->articleService->createArticle($articleDTO, $this->user);

        // Assert
        self::assertInstanceOf(ArticleDTO::class, $result);
        self::assertEquals($data['title'], $result->title);
        self::assertEquals($data['content'], $result->content);
        self::assertNotNull($result->id);
    }

    public function testCreateArticleValidationFailure(): void
    {
        // Arrange
        $data = [
            'title' => '', // Invalid title
            'content' => 'Content'
        ];
        $articleDTO = ArticleDTO::fromRequest($data);
        $validationErrors = ['title' => 'Title cannot be empty'];

        // Setup validator mock to return errors
        $this->validator->expects(self::once())
            ->method('validateCreate')
            ->with($articleDTO)
            ->willReturn($validationErrors);

        // Setup repository to never be called
        $this->articleRepository->expects(self::never())
            ->method('save');

        // Assert & Act
        $this->expectException(ValidationException::class);
        $this->articleService->createArticle($articleDTO, $this->user);
    }

    public function testUpdateArticle(): void
    {
        // Arrange
        $articleId = '507f1f77bcf86cd799439012';
        $data = [
            'title' => 'Updated Title',
            'content' => 'Updated content'
        ];
        $articleDTO = ArticleDTO::fromRequest($data);

        $existingArticle = new Article();
        $existingArticle->setTitle('Original Title');
        $existingArticle->setContent('Original content');
        $existingArticle->setAuthorId($this->user->getId());

        $reflection = new \ReflectionClass($existingArticle);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($existingArticle, $articleId);

        // Setup validator mock
        $this->validator->expects(self::once())
            ->method('validateUpdate')
            ->with($articleDTO)
            ->willReturn([]);

        // Setup repository find behavior
        $this->articleRepository->expects(self::once())
            ->method('find')
            ->with($articleId)
            ->willReturn($existingArticle);

        // Setup repository save behavior
        $this->articleRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function(Article $article) use ($data, $articleId) {
                return $article->getId() === $articleId
                    && $article->getTitle() === $data['title']
                    && $article->getContent() === $data['content']
                    && $article->getAuthorId() === $this->user->getId();
            }));

        // Act
        $result = $this->articleService->updateArticle($articleId, $articleDTO, $this->user);

        // Assert
        self::assertInstanceOf(ArticleDTO::class, $result);
        self::assertEquals($data['title'], $result->title);
        self::assertEquals($data['content'], $result->content);
        self::assertEquals($articleId, $result->id);
    }

    public function testUpdateNonExistentArticle(): void
    {
        // Arrange
        $articleId = '507f1f77bcf86cd799439012';
        $data = [
            'title' => 'Updated Title',
            'content' => 'Updated content'
        ];
        $articleDTO = ArticleDTO::fromRequest($data);

        // Setup validator mock
        $this->validator->expects(self::once())
            ->method('validateUpdate')
            ->with($articleDTO)
            ->willReturn([]);

        // Setup repository to return null
        $this->articleRepository->expects(self::once())
            ->method('find')
            ->with($articleId)
            ->willReturn(null);

        // Setup repository to never save
        $this->articleRepository->expects(self::never())
            ->method('save');

        // Act
        $result = $this->articleService->updateArticle($articleId, $articleDTO, $this->user);

        // Assert
        self::assertNull($result);
    }

    public function testUpdateArticleUnauthorized(): void
    {
        // Arrange
        $articleId = '507f1f77bcf86cd799439012';
        $data = [
            'title' => 'Updated Title',
            'content' => 'Updated content'
        ];
        $articleDTO = ArticleDTO::fromRequest($data);

        $existingArticle = new Article();
        $existingArticle->setTitle('Original Title');
        $existingArticle->setContent('Original content');
        $existingArticle->setAuthorId('different_user_id'); // Different user

        $reflection = new \ReflectionClass($existingArticle);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($existingArticle, $articleId);

        // Setup validator mock
        $this->validator->expects(self::once())
            ->method('validateUpdate')
            ->with($articleDTO)
            ->willReturn([]);

        // Setup repository find behavior
        $this->articleRepository->expects(self::once())
            ->method('find')
            ->with($articleId)
            ->willReturn($existingArticle);

        // Setup repository to never save
        $this->articleRepository->expects(self::never())
            ->method('save');

        // Act
        $result = $this->articleService->updateArticle($articleId, $articleDTO, $this->user);

        // Assert
        self::assertNull($result);
    }
}
