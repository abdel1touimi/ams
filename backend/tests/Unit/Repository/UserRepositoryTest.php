<?php

namespace App\Tests\Unit\Repository;

use App\Document\User;
use App\Repository\UserRepository;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class UserRepositoryTest extends TestCase
{
    private UserRepository $userRepository;
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
            ->with(User::class)
            ->willReturn($this->documentRepository);

        $this->userRepository = new UserRepository($this->documentManager);
    }

    public function testFindByEmail(): void
    {
        // Arrange
        $email = 'test@example.com';
        $user = new User();
        $user->setEmail($email);

        $this->documentRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn($user);

        // Act
        $result = $this->userRepository->findByEmail($email);

        // Assert
        self::assertSame($user, $result);
    }

    public function testFindByEmailReturnsNull(): void
    {
        // Arrange
        $email = 'nonexistent@example.com';

        $this->documentRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => $email])
            ->willReturn(null);

        // Act
        $result = $this->userRepository->findByEmail($email);

        // Assert
        self::assertNull($result);
    }

    public function testSave(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('test@example.com');

        $this->documentManager->expects(self::once())
            ->method('persist')
            ->with($user);

        $this->documentManager->expects(self::once())
            ->method('flush');

        // Act
        $this->userRepository->save($user);
    }
}
