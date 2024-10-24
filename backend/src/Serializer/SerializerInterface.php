<?php

namespace App\Serializer;

interface SerializerInterface
{
    public function serialize(mixed $data): mixed;
    public function deserialize(mixed $data, string $type): mixed;
}
