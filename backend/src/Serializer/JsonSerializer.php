<?php

namespace App\Serializer;

use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class JsonSerializer implements SerializerInterface
{
    public function __construct(
        private readonly SymfonySerializerInterface $serializer
    ) {}

    public function serialize(mixed $data): mixed
    {
        return $this->serializer->normalize($data, 'json', [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object) {
                return $object->getId();
            },
            AbstractNormalizer::IGNORED_ATTRIBUTES => ['password']
        ]);
    }

    public function deserialize(mixed $data, string $type): mixed
    {
        return $this->serializer->deserialize($data, $type, 'json');
    }
}
