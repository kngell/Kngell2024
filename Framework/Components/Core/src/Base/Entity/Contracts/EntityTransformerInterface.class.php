<?php

declare(strict_types=1);

interface EntityTransformerInterface
{
    public function transform(Entity $entity, TransformationContext $context): array;
}