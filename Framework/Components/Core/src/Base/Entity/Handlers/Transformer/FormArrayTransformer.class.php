<?php

declare(strict_types=1);
class FormArrayTransformer implements ToArrayTransformerInterface
{
    public function __construct(
        private FieldMapper $fieldMapper,
        private ArrayFlattener $arrayFlattener,
    ) {
    }

    public function supports(string $format): bool
    {
        return $format === 'form';
    }

    public function transform(Entity $entity, array $options = []): array
    {
        $fieldMapping = $options['field_mapping'] ?? [];
        $formatValues = $options['format_values'] ?? true;
        if (!$fieldMapping->isEmpty()) {
            $mappedData = $this->fieldMapper->applyMapping($entity, $fieldMapping, $formatValues);
            return $this->arrayFlattener->flatten($mappedData);
        }

        $allData = $entity->toDeepArray(true, 2);

        return $this->arrayFlattener->flatten($allData);
    }
}