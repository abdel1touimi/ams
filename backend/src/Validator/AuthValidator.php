<?php

namespace App\Validator;

use App\Document\User;
use App\DTO\UserDTO;
use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthValidator
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly UserRepository $userRepository
    ) {}

    public function validateRegistration(UserDTO $userDTO): array
    {
        $constraints = new Assert\Collection([
            'email' => [
                new Assert\NotBlank(['message' => 'Email is required']),
                new Assert\Email(['message' => 'Invalid email address']),
                new Assert\Callback([$this, 'validateUniqueEmail'])
            ],
            'name' => [
                new Assert\NotBlank(['message' => 'Name is required']),
                new Assert\Length([
                    'min' => 2,
                    'max' => 50,
                    'minMessage' => 'Name must be at least {{ limit }} characters long',
                    'maxMessage' => 'Name cannot be longer than {{ limit }} characters'
                ]),
                new Assert\Regex([
                    'pattern' => '/^[a-zA-Z\s\'-]+$/',
                    'message' => 'Name can only contain letters, spaces, hyphens and apostrophes'
                ])
            ],
            'password' => [
                new Assert\NotBlank(['message' => 'Password is required']),
                new Assert\Length([
                    'min' => 8,
                    'minMessage' => 'Password must be at least {{ limit }} characters long'
                ]),
                new Assert\Regex([
                    'pattern' => '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d\W]{8,}$/',
                    'message' => 'Password must contain at least one letter and one number'
                ])
            ]
        ]);

        return $this->validate([
            'email' => $userDTO->email,
            'name' => $userDTO->name,
            'password' => $userDTO->password
        ], $constraints);
    }

    public function validateProfileUpdate(UserDTO $userDTO, User $currentUser): array
    {
        $constraints = new Assert\Collection([
            'email' => [
                new Assert\NotBlank(['message' => 'Email is required']),
                new Assert\Email(['message' => 'Invalid email address']),
                new Assert\Callback(function($email, $context) use ($currentUser) {
                    if ($email !== $currentUser->getEmail()) {
                        $existingUser = $this->userRepository->findByEmail($email);
                        if ($existingUser) {
                            $context->buildViolation('This email is already in use')
                                ->addViolation();
                        }
                    }
                })
            ],
            'name' => [
                new Assert\NotBlank(['message' => 'Name is required']),
                new Assert\Length([
                    'min' => 2,
                    'max' => 50,
                    'minMessage' => 'Name must be at least {{ limit }} characters long',
                    'maxMessage' => 'Name cannot be longer than {{ limit }} characters'
                ]),
                new Assert\Regex([
                    'pattern' => '/^[a-zA-Z\s\'-]+$/',
                    'message' => 'Name can only contain letters, spaces, hyphens and apostrophes'
                ])
            ]
        ]);

        return $this->validate([
            'email' => $userDTO->email,
            'name' => $userDTO->name
        ], $constraints);
    }

    public function validatePasswordChange(array $data): array
    {
        $constraints = new Assert\Collection([
            'current_password' => [
                new Assert\NotBlank(['message' => 'Current password is required'])
            ],
            'new_password' => [
                new Assert\NotBlank(['message' => 'New password is required']),
                new Assert\Length([
                    'min' => 8,
                    'minMessage' => 'Password must be at least {{ limit }} characters long'
                ]),
                new Assert\Regex([
                    'pattern' => '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d\W]{8,}$/',
                    'message' => 'Password must contain at least one letter and one number'
                ]),
                new Assert\NotEqualTo([
                    'value' => $data['current_password'] ?? null,
                    'message' => 'New password must be different from current password'
                ])
            ],
            'confirm_password' => [
                new Assert\NotBlank(['message' => 'Password confirmation is required']),
                new Assert\EqualTo([
                    'value' => $data['new_password'] ?? null,
                    'message' => 'Password confirmation must match new password'
                ])
            ]
        ]);

        return $this->validate($data, $constraints);
    }

    private function validate(array $data, Assert\Collection $constraints): array
    {
        $violations = $this->validator->validate($data, $constraints);

        $errors = [];
        foreach ($violations as $violation) {
            $propertyPath = trim($violation->getPropertyPath(), '[]');
            $errors[$propertyPath] = $violation->getMessage();
        }

        return $errors;
    }

    public function validateUniqueEmail($email, $context): void
    {
        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser) {
            $context->buildViolation('This email is already in use')
                ->addViolation();
        }
    }
}
