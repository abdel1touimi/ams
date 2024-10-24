<?php

namespace App\DTO;

use App\Document\User;

class UserDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $email,
        public readonly string $name,
        public readonly ?string $password = null
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            $user->getId(),
            $user->getEmail(),
            $user->getName()
        );
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            null,
            $data['email'],
            $data['name'],
            $data['password'] ?? null
        );
    }
}
