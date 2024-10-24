<?php

namespace App\Service;

use App\Document\User;
use App\DTO\UserDTO;
use App\Exception\ValidationException;
use App\Repository\UserRepository;
use App\Validator\AuthValidator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly AuthValidator $authValidator
    ) {}

    public function register(UserDTO $userDTO): UserDTO
    {
        $errors = $this->authValidator->validateRegistration($userDTO);
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $user = new User();
        $user->setEmail($userDTO->email);
        $user->setName($userDTO->name);
        $user->setPassword($this->passwordHasher->hashPassword($user, $userDTO->password));

        $this->userRepository->save($user);

        return UserDTO::fromEntity($user);
    }

    public function updateProfile(User $user, UserDTO $userDTO): UserDTO
    {
        $errors = $this->authValidator->validateProfileUpdate($userDTO, $user);
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        $user->setName($userDTO->name);
        if ($userDTO->email !== $user->getEmail()) {
            $user->setEmail($userDTO->email);
        }

        $this->userRepository->save($user);

        return UserDTO::fromEntity($user);
    }

    public function changePassword(User $user, array $data): bool
    {
        $errors = $this->authValidator->validatePasswordChange($data);
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $data['current_password'])) {
            throw new \InvalidArgumentException('Current password is incorrect');
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $data['new_password']));
        $this->userRepository->save($user);

        return true;
    }
}
