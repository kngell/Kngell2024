<?php

declare(strict_types=1);
class DeepArrayTransformer implements ToArrayTransformerInterface
{
    public function __construct(
        private EntityTraverser $entityTraverser,
    ) {
    }

    public function supports(string $format): bool
    {
        return $format === 'deep';
    }

    public function transform(Entity $entity, array $options = []): array
    {
        return $this->entityTraverser->traverse(
            $entity,
            $options['max_depth'] ?? 2,
            $options['include_relationships'] ?? true,
            $options['excluded_properties'] ?? [],
        );
    }
}