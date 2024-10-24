<?php

namespace App\Tests\Unit\Serializer;

use App\Document\User;
use App\Serializer\JsonSerializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class JsonSerializerTest extends TestCase
{
    private JsonSerializer $serializer;
    private SerializerNormalizerMock $symfonySerializer;

    protected function setUp(): void
    {
        $this->symfonySerializer = new SerializerNormalizerMock();
        $this->serializer = new JsonSerializer($this->symfonySerializer);
    }

    public function testSerializeUser(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setName('Test User');

        $reflection = new \ReflectionClass($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, '123');

        $expectedData = [
            'id' => '123',
            'email' => 'test@example.com',
            'name' => 'Test User'
        ];

        $this->symfonySerializer->setNormalizeResult($expectedData);

        // Act
        $result = $this->serializer->serialize($user);

        // Assert
        self::assertEquals($expectedData, $result);
        self::assertTrue($this->symfonySerializer->wasNormalizeCalled());
    }

    public function testDeserialize(): void
    {
        // Arrange
        $jsonData = '{"name":"Test User"}';
        $expectedObject = new User();
        $expectedObject->setName('Test User');

        $this->symfonySerializer->setDeserializeResult($expectedObject);

        // Act
        $result = $this->serializer->deserialize($jsonData, User::class);

        // Assert
        self::assertInstanceOf(User::class, $result);
        self::assertTrue($this->symfonySerializer->wasDeserializeCalled());
    }

    public function testSerializeNull(): void
    {
        // Act
        $result = $this->serializer->serialize(null);

        // Assert
        self::assertNull($result);
        self::assertTrue($this->symfonySerializer->wasNormalizeCalled());
    }
}

class SerializerNormalizerMock implements SerializerInterface, NormalizerInterface
{
    private mixed $normalizeResult = null;
    private mixed $deserializeResult = null;
    private bool $normalizeCalled = false;
    private bool $deserializeCalled = false;

    public function normalize(mixed $object, string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $this->normalizeCalled = true;
        return $this->normalizeResult;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return true;
    }

    public function serialize(mixed $data, string $format, array $context = []): string
    {
        return json_encode($data);
    }

    public function deserialize(mixed $data, string $type, string $format, array $context = []): mixed
    {
        $this->deserializeCalled = true;
        return $this->deserializeResult;
    }

    // Helper methods for testing
    public function setNormalizeResult(mixed $result): void
    {
        $this->normalizeResult = $result;
    }

    public function setDeserializeResult(mixed $result): void
    {
        $this->deserializeResult = $result;
    }

    public function wasNormalizeCalled(): bool
    {
        return $this->normalizeCalled;
    }

    public function wasDeserializeCalled(): bool
    {
        return $this->deserializeCalled;
    }
}
