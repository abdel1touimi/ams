<?php

namespace App\Service;

use App\Response\ApiResponse;
use App\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

class ResponseHandler
{
    public function __construct(
        private readonly SerializerInterface $serializer
    ) {}

    public function success(mixed $data = null, string $message = null, int $status = Response::HTTP_OK): ApiResponse
    {
        $serializedData = $data ? $this->serializer->serialize($data) : null;
        return ApiResponse::success($serializedData, $message, $status);
    }

    public function error(string $message, mixed $errors = null, int $status = Response::HTTP_BAD_REQUEST): ApiResponse
    {
        $serializedErrors = $errors ? $this->serializer->serialize($errors) : null;
        return ApiResponse::error($message, $serializedErrors, $status);
    }

    public function created(mixed $data = null, string $message = 'Resource created successfully'): ApiResponse
    {
        $serializedData = $data ? $this->serializer->serialize($data) : null;
        return ApiResponse::created($serializedData, $message);
    }

    public function noContent(string $message = 'Resource deleted successfully'): ApiResponse
    {
        return ApiResponse::noContent($message);
    }

    public function validationError(array $errors): ApiResponse
    {
        return ApiResponse::validationError($this->serializer->serialize($errors));
    }

    public function notFound(string $message = 'Resource not found'): ApiResponse
    {
        return ApiResponse::notFound($message);
    }

    public function unauthorized(string $message = 'Unauthorized access'): ApiResponse
    {
        return ApiResponse::unauthorized($message);
    }
}
