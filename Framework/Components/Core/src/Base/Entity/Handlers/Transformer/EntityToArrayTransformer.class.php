<?php

declare(strict_types=1);

class EntityToArrayTransformer implements EntityToArrayTransformerInterface
{
    private array $strategies = [];

    public function __construct(
        TypePresenterFactory $typePresenterFactory,
        TypeNormalizerInterface $normalizer,
    ) {
        $entityTraverser = new EntityTraverser($typePresenterFactory);
        $arrayFlattener = new ArrayFlattener();
        $fieldMapper = new FieldMapper($typePresenterFactory);
        $relationshipExtractor = new RelationshipExtractor();

        $this->strategies = [
            new SimpleArrayTransformer($normalizer),
            new OriginalArrayTransformer(),
            new DeepArrayTransformer($entityTraverser),
            new FormArrayTransformer(
                $fieldMapper,
                $arrayFlattener,
            ),
            new DatabaseArrayTransformer(
                new SimpleArrayTransformer($normalizer),
                $normalizer,
                $relationshipExtractor,
            ),
        ];
    }

    public function toArray(Entity $entity): array
    {
        return $this->getStrategy('simple')->transform($entity);
    }

    public function toOriginalArray(Entity $entity): array
    {
        return $this->getStrategy('original')->transform($entity);
    }

    public function toDeepArray(
        Entity $entity,
        bool $includeRelationships = true,
        int $maxDepth = 2,
        array $excludedProperties = [],
    ): array {
        return $this->getStrategy('deep')->transform($entity, [
            'include_relationships' => $includeRelationships,
            'max_depth' => $maxDepth,
            'excluded_properties' => $excludedProperties,
        ]);
    }

    public function toFlattenedArray(
        Entity $entity,
        string $separator = '.',
        bool $includeRelationships = true,
        array $excludedProperties = [],
    ): array {
        $deepArray = $this->toDeepArray($entity, $includeRelationships, 2, $excludedProperties);
        return (new ArrayFlattener())->flattenWithSeparator(
            $deepArray,
            $separator,
            '', // Prefix is empty string
        );
    }

    public function toFormArray(
        Entity $entity,
        array $fieldMapping = [],
        bool $flattenNested = true,
        bool $formatValues = true,
    ): array {
        return $this->getStrategy('form')->transform($entity, [
            'field_mapping' => $fieldMapping,
            'flatten_nested' => $flattenNested,
            'format_values' => $formatValues,
        ]);
    }

    public function toDatabaseArray(
        Entity $entity,
        bool $includeRelationships = false,
    ): array {
        return $this->getStrategy('database')->transform($entity, [
            'include_relationships' => $includeRelationships,
        ]);
    }

    public function extractRelationshipIds(
        Entity $entity,
        array $relationshipNames = [],
    ): array {
        return (new RelationshipExtractor())->extractIds($entity, $relationshipNames);
    }

    private function getStrategy(string $format): ToArrayTransformerInterface
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($format)) {
                return $strategy;
            }
        }

        throw new InvalidArgumentException("No strategy found for format: {$format}");
    }
}