<?php

namespace App\Validator;

use App\DTO\ArticleDTO;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleValidator
{
    public function __construct(private readonly ValidatorInterface $validator)
    {}

    public function validateCreate(ArticleDTO $articleDTO): array
    {
        $constraints = new Assert\Collection([
            'title' => [
                new Assert\NotBlank(['message' => 'Title cannot be blank']),
                new Assert\Length([
                    'min' => 3,
                    'max' => 255,
                    'minMessage' => 'Title must be at least {{ limit }} characters long',
                    'maxMessage' => 'Title cannot be longer than {{ limit }} characters'
                ])
            ],
            'content' => [
                new Assert\NotBlank(['message' => 'Content cannot be blank']),
                new Assert\Length([
                    'min' => 10,
                    'minMessage' => 'Content must be at least {{ limit }} characters long'
                ])
            ]
        ]);

        $violations = $this->validator->validate([
            'title' => $articleDTO->title,
            'content' => $articleDTO->content
        ], $constraints);

        return $this->formatViolations($violations);
    }

    public function validateUpdate(ArticleDTO $articleDTO): array
    {
        return $this->validateCreate($articleDTO);
    }

    private function formatViolations($violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $propertyPath = $violation->getPropertyPath();
            $errors[trim($propertyPath, '[]')] = $violation->getMessage();
        }
        return $errors;
    }
}
