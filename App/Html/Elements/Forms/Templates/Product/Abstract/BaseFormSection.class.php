<?php

declare(strict_types=1);

abstract class BaseFormSection implements FormSectionInterface
{
    public function __construct(
        protected readonly HtmlBuilder $formBuilder,
    ) {
    }

    public function shouldRender(array $formValues = []): bool
    {
        return true;
    }

    public function getFieldMapping(): array
    {
        $mapping = [];
        foreach ($this->getConfig() as $field) {
            if (($field['type'] ?? '') === 'field-group' && isset($field['content'])) {
                foreach ($field['content'] as $subField) {
                    $this->extractFieldToMapping($subField, $mapping);
                }
                continue;
            }
            $this->extractFieldToMapping($field, $mapping);
        }
        return $mapping;
    }

    protected function mapRelation(string $relationName, string $targetName, Entity $instance): array
    {
        $pkField = $instance->getEntityKeyField();

        return [
            "{$relationName}.{$pkField}" => $targetName,
        ];
    }

    private function extractFieldToMapping(array $field, array &$mapping): void
    {
        $name = $field['name'] ?? null;
        if (!$name) {
            return;
        }

        $sourcePath = $field['map'] ?? $name;
        $mapping[$sourcePath] = $name;
    }
}