<?php

namespace App\Tests\Unit\Validator;

use App\Document\User;
use App\DTO\UserDTO;
use App\Repository\UserRepository;
use App\Validator\AuthValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthValidatorTest extends TestCase
{
    private AuthValidator $validator;
    private ValidatorInterface $symfonyValidator;
    private UserRepository|MockObject $userRepository;

    protected function setUp(): void
    {
        $this->symfonyValidator = Validation::createValidator();
        $this->userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator = new AuthValidator($this->symfonyValidator, $this->userRepository);
    }

    public function testValidateRegistrationWithValidData(): void
    {
        // Arrange
        $data = [
            'email' => 'test@example.com',
            'password' => 'Password123',
            'name' => 'John Doe'
        ];
        $userDTO = UserDTO::fromRequest($data);

        $this->userRepository->expects(self::once())
            ->method('findByEmail')
            ->with($data['email'])
            ->willReturn(null);

        // Act
        $errors = $this->validator->validateRegistration($userDTO);

        // Assert
        self::assertEmpty($errors);
    }

    public function testValidateRegistrationWithExistingEmail(): void
    {
        // Arrange
        $data = [
            'email' => 'existing@example.com',
            'password' => 'Password123',
            'name' => 'John Doe'
        ];
        $userDTO = UserDTO::fromRequest($data);

        $existingUser = new User();
        $this->userRepository->expects(self::once())
            ->method('findByEmail')
            ->with($data['email'])
            ->willReturn($existingUser);

        // Act
        $errors = $this->validator->validateRegistration($userDTO);

        // Assert
        self::assertArrayHasKey('email', $errors);
        self::assertStringContainsString('already in use', $errors['email']);
    }

    public function testValidateRegistrationWithInvalidEmail(): void
    {
        // Arrange
        $data = [
            'email' => 'invalid-email',
            'password' => 'Password123',
            'name' => 'John Doe'
        ];
        $userDTO = UserDTO::fromRequest($data);

        // Act
        $errors = $this->validator->validateRegistration($userDTO);

        // Assert
        self::assertArrayHasKey('email', $errors);
        self::assertStringContainsString('Invalid email', $errors['email']);
    }

    public function testValidateRegistrationWithWeakPassword(): void
    {
        // Arrange
        $data = [
            'email' => 'test@example.com',
            'password' => 'weak',  // Too short and missing number
            'name' => 'John Doe'
        ];
        $userDTO = UserDTO::fromRequest($data);

        // Act
        $errors = $this->validator->validateRegistration($userDTO);

        // Assert
        self::assertArrayHasKey('password', $errors);
        self::assertStringContainsString('at least', $errors['password']);
    }

    public function testValidateRegistrationWithInvalidName(): void
    {
        // Arrange
        $data = [
            'email' => 'test@example.com',
            'password' => 'Password123',
            'name' => 'J'  // Too short
        ];
        $userDTO = UserDTO::fromRequest($data);

        // Act
        $errors = $this->validator->validateRegistration($userDTO);

        // Assert
        self::assertArrayHasKey('name', $errors);
        self::assertStringContainsString('at least', $errors['name']);
    }

    public function testValidateProfileUpdateWithValidData(): void
    {
        // Arrange
        $currentUser = new User();
        $currentUser->setEmail('current@example.com');
        $currentUser->setName('Current User');

        $data = [
            'email' => 'new@example.com',
            'name' => 'New Name'
        ];
        $userDTO = UserDTO::fromRequest($data);

        $this->userRepository->expects(self::once())
            ->method('findByEmail')
            ->with($data['email'])
            ->willReturn(null);

        // Act
        $errors = $this->validator->validateProfileUpdate($userDTO, $currentUser);

        // Assert
        self::assertEmpty($errors);
    }

    public function testValidateProfileUpdateWithSameEmail(): void
    {
        // Arrange
        $currentUser = new User();
        $currentUser->setEmail('current@example.com');
        $currentUser->setName('Current User');

        $data = [
            'email' => 'current@example.com',  // Same email as current user
            'name' => 'New Name'
        ];
        $userDTO = UserDTO::fromRequest($data);

        // Email check should not happen when email hasn't changed
        $this->userRepository->expects(self::never())
            ->method('findByEmail');

        // Act
        $errors = $this->validator->validateProfileUpdate($userDTO, $currentUser);

        // Assert
        self::assertEmpty($errors);
    }

    public function testValidateProfileUpdateWithExistingEmail(): void
    {
        // Arrange
        $currentUser = new User();
        $currentUser->setEmail('current@example.com');
        $currentUser->setName('Current User');

        $data = [
            'email' => 'existing@example.com',
            'name' => 'New Name'
        ];
        $userDTO = UserDTO::fromRequest($data);

        $existingUser = new User();
        $this->userRepository->expects(self::once())
            ->method('findByEmail')
            ->with($data['email'])
            ->willReturn($existingUser);

        // Act
        $errors = $this->validator->validateProfileUpdate($userDTO, $currentUser);

        // Assert
        self::assertArrayHasKey('email', $errors);
        self::assertStringContainsString('already in use', $errors['email']);
    }

    public function testValidatePasswordChangeWithValidData(): void
    {
        // Arrange
        $data = [
            'current_password' => 'CurrentPass123',
            'new_password' => 'NewPass123',
            'confirm_password' => 'NewPass123'
        ];

        // Act
        $errors = $this->validator->validatePasswordChange($data);

        // Assert
        self::assertEmpty($errors);
    }

    public function testValidatePasswordChangeWithMismatchedPasswords(): void
    {
        // Arrange
        $data = [
            'current_password' => 'CurrentPass123',
            'new_password' => 'NewPass123',
            'confirm_password' => 'DifferentPass123'
        ];

        // Act
        $errors = $this->validator->validatePasswordChange($data);

        // Assert
        self::assertArrayHasKey('confirm_password', $errors);
        self::assertStringContainsString('must match', $errors['confirm_password']);
    }

    public function testValidatePasswordChangeWithSameNewPassword(): void
    {
        // Arrange
        $data = [
            'current_password' => 'Password123',
            'new_password' => 'Password123',  // Same as current password
            'confirm_password' => 'Password123'
        ];

        // Act
        $errors = $this->validator->validatePasswordChange($data);

        // Assert
        self::assertArrayHasKey('new_password', $errors);
        self::assertStringContainsString('must be different', $errors['new_password']);
    }

    public function testValidatePasswordChangeWithWeakNewPassword(): void
    {
        // Arrange
        $data = [
            'current_password' => 'CurrentPass123',
            'new_password' => 'weak',  // Too short and missing required characters
            'confirm_password' => 'weak'
        ];

        // Act
        $errors = $this->validator->validatePasswordChange($data);

        // Assert
        self::assertArrayHasKey('new_password', $errors);
        self::assertStringContainsString('at least', $errors['new_password']);
    }

    public function testValidatePasswordChangeWithMissingConfirmation(): void
    {
        // Arrange
        $data = [
            'current_password' => 'CurrentPass123',
            'new_password' => 'NewPass123',
            'confirm_password' => ''  // Empty confirmation
        ];

        // Act
        $errors = $this->validator->validatePasswordChange($data);

        // Assert
        self::assertArrayHasKey('confirm_password', $errors);
        self::assertStringContainsString('must match new password', $errors['confirm_password']);
    }
}
