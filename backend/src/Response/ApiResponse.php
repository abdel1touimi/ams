<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse extends JsonResponse
{
    public const HTTP_VALIDATION_ERROR = 422;

    private function __construct(
        mixed $data = null,
        int $status = Response::HTTP_OK,
        array $headers = [],
        private readonly bool $success = true,
        private readonly ?string $message = null
    ) {
        parent::__construct(self::format($data, $success, $message), $status, $headers);
    }

    private static function format(mixed $data, bool $success, ?string $message): array
    {
        return array_filter([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], fn($value) => !is_null($value));
    }

    public static function success(
        mixed $data = null,
        string $message = null,
        int $status = Response::HTTP_OK
    ): self {
        return new self($data, $status, [], true, $message);
    }

    public static function error(
        string $message,
        mixed $errors = null,
        int $status = Response::HTTP_BAD_REQUEST
    ): self {
        return new self($errors, $status, [], false, $message);
    }

    public static function created(mixed $data = null, string $message = 'Resource created successfully'): self
    {
        return new self($data, Response::HTTP_CREATED, [], true, $message);
    }

    public static function noContent(string $message = 'Resource deleted successfully'): self
    {
        return new self(null, Response::HTTP_NO_CONTENT, [], true, $message);
    }

    public static function validationError(array $errors): self
    {
        return new self($errors, self::HTTP_VALIDATION_ERROR, [], false, 'Validation failed');
    }

    public static function notFound(string $message = 'Resource not found'): self
    {
        return new self(null, Response::HTTP_NOT_FOUND, [], false, $message);
    }

    public static function unauthorized(string $message = 'Unauthorized access'): self
    {
        return new self(null, Response::HTTP_UNAUTHORIZED, [], false, $message);
    }
}
