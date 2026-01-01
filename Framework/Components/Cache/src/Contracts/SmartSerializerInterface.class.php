<?php

declare(strict_types=1);

interface SmartSerializerInterface
{
    public function serialize(mixed $value): string;

    public function unserialize(string $data): mixed;

    public function supportsCompression(): bool;

    public function compress(string $data): string;

    public function decompress(string $data): string;
}