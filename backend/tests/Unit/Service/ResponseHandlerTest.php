<?php

namespace App\Tests\Unit\Service;

use App\Response\ApiResponse;
use App\Service\ResponseHandler;
use App\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;

class ResponseHandlerTest extends TestCase
{
    private ResponseHandler $responseHandler;
    private SerializerInterface|MockObject $serializer;

    protected function setUp(): void
    {
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)
            ->getMock();

        $this->responseHandler = new ResponseHandler($this->serializer);
    }

    public function testSuccess(): void
    {
        // Arrange
        $data = ['key' => 'value'];
        $message = 'Success message';
        $serializedData = ['serialized' => 'data'];

        $this->serializer->expects(self::once())
            ->method('serialize')
            ->with($data)
            ->willReturn($serializedData);

        // Act
        $response = $this->responseHandler->success($data, $message);

        // Assert
        self::assertInstanceOf(ApiResponse::class, $response);
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        self::assertTrue($content['success']);
        self::assertEquals($message, $content['message']);
        self::assertEquals($serializedData, $content['data']);
    }

    public function testSuccessWithNullData(): void
    {
        // Arrange
        $message = 'Success with no data';

        $this->serializer->expects(self::never())
            ->method('serialize');

        // Act
        $response = $this->responseHandler->success(null, $message);

        // Assert
        self::assertInstanceOf(ApiResponse::class, $response);
        $content = json_decode($response->getContent(), true);
        self::assertTrue($content['success']);
        self::assertEquals($message, $content['message']);
        self::assertArrayNotHasKey('data', $content);
    }

    public function testError(): void
    {
        // Arrange
        $message = 'Error message';
        $errors = ['field' => 'error description'];
        $serializedErrors = ['serialized' => 'errors'];

        $this->serializer->expects(self::once())
            ->method('serialize')
            ->with($errors)
            ->willReturn($serializedErrors);

        // Act
        $response = $this->responseHandler->error($message, $errors);

        // Assert
        self::assertInstanceOf(ApiResponse::class, $response);
        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        self::assertFalse($content['success']);
        self::assertEquals($message, $content['message']);
        self::assertEquals($serializedErrors, $content['data']);
    }

    public function testCreated(): void
    {
        // Arrange
        $data = ['id' => 1];
        $message = 'Resource created';
        $serializedData = ['serialized' => 'data'];

        $this->serializer->expects(self::once())
            ->method('serialize')
            ->with($data)
            ->willReturn($serializedData);

        // Act
        $response = $this->responseHandler->created($data, $message);

        // Assert
        self::assertInstanceOf(ApiResponse::class, $response);
        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        self::assertTrue($content['success']);
        self::assertEquals($message, $content['message']);
        self::assertEquals($serializedData, $content['data']);
    }

    public function testNoContent(): void
    {
        // Arrange
        $message = 'Resource deleted';

        // Act
        $response = $this->responseHandler->noContent($message);

        // Assert
        self::assertInstanceOf(ApiResponse::class, $response);
        self::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        self::assertTrue($content['success']);
        self::assertEquals($message, $content['message']);
    }

    public function testValidationError(): void
    {
        // Arrange
        $errors = ['field' => 'validation error'];
        $serializedErrors = ['serialized' => 'validation errors'];

        $this->serializer->expects(self::once())
            ->method('serialize')
            ->with($errors)
            ->willReturn($serializedErrors);

        // Act
        $response = $this->responseHandler->validationError($errors);

        // Assert
        self::assertInstanceOf(ApiResponse::class, $response);
        self::assertEquals(ApiResponse::HTTP_VALIDATION_ERROR, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        self::assertFalse($content['success']);
        self::assertEquals('Validation failed', $content['message']);
        self::assertEquals($serializedErrors, $content['data']);
    }

    public function testNotFound(): void
    {
        // Arrange
        $message = 'Resource not found';

        // Act
        $response = $this->responseHandler->notFound($message);

        // Assert
        self::assertInstanceOf(ApiResponse::class, $response);
        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        self::assertFalse($content['success']);
        self::assertEquals($message, $content['message']);
    }

    public function testUnauthorized(): void
    {
        // Arrange
        $message = 'Unauthorized access';

        // Act
        $response = $this->responseHandler->unauthorized($message);

        // Assert
        self::assertInstanceOf(ApiResponse::class, $response);
        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        self::assertFalse($content['success']);
        self::assertEquals($message, $content['message']);
    }
}
