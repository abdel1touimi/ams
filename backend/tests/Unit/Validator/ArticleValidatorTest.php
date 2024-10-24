<?php

namespace App\Tests\Unit\Validator;

use App\DTO\ArticleDTO;
use App\Validator\ArticleValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleValidatorTest extends TestCase
{
    private ArticleValidator $validator;
    private ValidatorInterface $symfonyValidator;

    protected function setUp(): void
    {
        $this->symfonyValidator = Validation::createValidator();
        $this->validator = new ArticleValidator($this->symfonyValidator);
    }

    public function testValidateCreateWithValidData(): void
    {
        // Arrange
        $data = [
            'title' => 'Valid Title',
            'content' => 'This is valid content that is longer than 10 characters'
        ];
        $articleDTO = ArticleDTO::fromRequest($data);

        // Act
        $errors = $this->validator->validateCreate($articleDTO);

        // Assert
        self::assertEmpty($errors);
    }

    public function testValidateCreateWithEmptyTitle(): void
    {
        // Arrange
        $data = [
            'title' => '',
            'content' => 'This is valid content'
        ];
        $articleDTO = ArticleDTO::fromRequest($data);

        // Act
        $errors = $this->validator->validateCreate($articleDTO);

        // Assert
        self::assertArrayHasKey('title', $errors);
        self::assertStringContainsString('must be at least', $errors['title']);
    }

    public function testValidateCreateWithShortTitle(): void
    {
        // Arrange
        $data = [
            'title' => 'Ab',
            'content' => 'This is valid content'
        ];
        $articleDTO = ArticleDTO::fromRequest($data);

        // Act
        $errors = $this->validator->validateCreate($articleDTO);

        // Assert
        self::assertArrayHasKey('title', $errors);
        self::assertStringContainsString('must be at least', $errors['title']);
    }

    public function testValidateCreateWithLongTitle(): void
    {
        // Arrange
        $data = [
            'title' => str_repeat('a', 256),
            'content' => 'This is valid content'
        ];
        $articleDTO = ArticleDTO::fromRequest($data);

        // Act
        $errors = $this->validator->validateCreate($articleDTO);

        // Assert
        self::assertArrayHasKey('title', $errors);
        self::assertStringContainsString('cannot be longer than', $errors['title']);
    }

    public function testValidateCreateWithEmptyContent(): void
    {
        // Arrange
        $data = [
            'title' => 'Valid Title',
            'content' => ''
        ];
        $articleDTO = ArticleDTO::fromRequest($data);

        // Act
        $errors = $this->validator->validateCreate($articleDTO);

        // Assert
        self::assertArrayHasKey('content', $errors);
        self::assertStringContainsString('must be at least', $errors['content']);
    }

    public function testValidateCreateWithShortContent(): void
    {
        // Arrange
        $data = [
            'title' => 'Valid Title',
            'content' => 'Short'
        ];
        $articleDTO = ArticleDTO::fromRequest($data);

        // Act
        $errors = $this->validator->validateCreate($articleDTO);

        // Assert
        self::assertArrayHasKey('content', $errors);
        self::assertStringContainsString('must be at least', $errors['content']);
    }

    public function testValidateUpdateWithValidData(): void
    {
        // Arrange
        $data = [
            'title' => 'Updated Title',
            'content' => 'This is updated content that is longer than 10 characters'
        ];
        $articleDTO = ArticleDTO::fromRequest($data);

        // Act
        $errors = $this->validator->validateUpdate($articleDTO);

        // Assert
        self::assertEmpty($errors);
    }
}
