<?php

namespace App\Tests\Unit\Service;

use App\Document\User;
use App\DTO\UserDTO;
use App\Exception\ValidationException;
use App\Repository\UserRepository;
use App\Service\AuthService;
use App\Validator\AuthValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private UserRepository|MockObject $userRepository;
    private UserPasswordHasherInterface|MockObject $passwordHasher;
    private AuthValidator|MockObject $validator;

    protected function setUp(): void
    {
        $this->userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->passwordHasher = $this->getMockBuilder(UserPasswordHasherInterface::class)
            ->getMock();

        $this->validator = $this->getMockBuilder(AuthValidator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->authService = new AuthService(
            $this->userRepository,
            $this->passwordHasher,
            $this->validator
        );
    }

    public function testRegisterUser(): void
    {
        // Arrange
        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'name' => 'Test User'
        ];

        $userDTO = UserDTO::fromRequest($data);
        $hashedPassword = 'hashed_password';

        $this->validator->expects(self::once())
            ->method('validateRegistration')
            ->with($userDTO)
            ->willReturn([]);

        $this->passwordHasher->expects(self::once())
            ->method('hashPassword')
            ->willReturn($hashedPassword);

        $this->userRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function(User $user) use ($data, $hashedPassword) {
                return $user->getEmail() === $data['email']
                    && $user->getName() === $data['name']
                    && $user->getPassword() === $hashedPassword;
            }))
            ->willReturnCallback(function(User $user) {
                $reflection = new \ReflectionClass($user);
                $idProperty = $reflection->getProperty('id');
                $idProperty->setAccessible(true);
                $idProperty->setValue($user, '507f1f77bcf86cd799439011');
            });

        // Act
        $result = $this->authService->register($userDTO);

        // Assert
        self::assertInstanceOf(UserDTO::class, $result);
        self::assertEquals($data['email'], $result->email);
        self::assertEquals($data['name'], $result->name);
        self::assertNull($result->password);
        self::assertNotNull($result->id);
    }

    public function testUpdateProfile(): void
    {
        // Arrange
        $existingUser = new User();
        $existingUser->setEmail('old@example.com');
        $existingUser->setName('Old Name');

        $reflection = new \ReflectionClass($existingUser);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($existingUser, '507f1f77bcf86cd799439011');

        $data = [
            'email' => 'new@example.com',
            'name' => 'New Name'
        ];
        $userDTO = UserDTO::fromRequest($data);

        $this->validator->expects(self::once())
            ->method('validateProfileUpdate')
            ->with($userDTO, $existingUser)
            ->willReturn([]);

        $this->userRepository->expects(self::once())
            ->method('save')
            ->with(self::callback(function(User $user) use ($data) {
                return $user->getEmail() === $data['email']
                    && $user->getName() === $data['name'];
            }));

        // Act
        $result = $this->authService->updateProfile($existingUser, $userDTO);

        // Assert
        self::assertInstanceOf(UserDTO::class, $result);
        self::assertEquals($data['email'], $result->email);
        self::assertEquals($data['name'], $result->name);
    }

    public function testChangePasswordWithSuccessfulValidation(): void
    {
        // Arrange
        $user = new User();
        $user->setPassword('old_hashed_password');

        $data = [
            'current_password' => 'CurrentPass123',
            'new_password' => 'NewPass123',
            'confirm_password' => 'NewPass123'
        ];

        $this->validator->expects(self::once())
            ->method('validatePasswordChange')
            ->with($data)
            ->willReturn([]);

        $this->passwordHasher->expects(self::once())
            ->method('isPasswordValid')
            ->with($user, $data['current_password'])
            ->willReturn(true);

        $this->passwordHasher->expects(self::once())
            ->method('hashPassword')
            ->with($user, $data['new_password'])
            ->willReturn('new_hashed_password');

        $this->userRepository->expects(self::once())
            ->method('save')
            ->with($user);

        // Act
        $result = $this->authService->changePassword($user, $data);

        // Assert
        self::assertTrue($result);
    }

    public function testChangePasswordWithValidationErrors(): void
    {
        // Arrange
        $user = new User();
        $data = [
            'current_password' => 'CurrentPass123',
            'new_password' => 'weak',
            'confirm_password' => 'different'
        ];
        $validationErrors = ['new_password' => 'Password must be at least 8 characters'];

        $this->validator->expects(self::once())
            ->method('validatePasswordChange')
            ->with($data)
            ->willReturn($validationErrors);

        $this->passwordHasher->expects(self::never())
            ->method('isPasswordValid');

        $this->userRepository->expects(self::never())
            ->method('save');

        // Assert & Act
        $this->expectException(ValidationException::class);
        $this->authService->changePassword($user, $data);
    }

    public function testChangePasswordWithIncorrectCurrentPassword(): void
    {
        // Arrange
        $user = new User();
        $user->setPassword('old_hashed_password');

        $data = [
            'current_password' => 'WrongPass123',
            'new_password' => 'NewPass123',
            'confirm_password' => 'NewPass123'
        ];

        $this->validator->expects(self::once())
            ->method('validatePasswordChange')
            ->with($data)
            ->willReturn([]);

        $this->passwordHasher->expects(self::once())
            ->method('isPasswordValid')
            ->with($user, $data['current_password'])
            ->willReturn(false);

        $this->userRepository->expects(self::never())
            ->method('save');

        // Assert & Act
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Current password is incorrect');
        $this->authService->changePassword($user, $data);
    }
}
