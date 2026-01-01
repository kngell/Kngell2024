<?php

declare(strict_types=1);
interface ToArrayTransformerInterface
{
    public function supports(string $format): bool;

    public function transform(Entity $entity, array $options = []): array;
}