<?php

declare(strict_types=1);

abstract class BaseFieldSection extends AbstractBaseHtmlSection implements FieldSectionInterface
{
    protected array $formValues = [];

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
    ) {
        parent::__construct($builder, $iconBuilder);
    }

    public function getFieldMapping(): array
    {
        $mapping = [];
        $config = $this->getConfig();
        if (is_array($config)) {
            foreach ($this->getConfig() as $field) {
                if (($field['type'] ?? '') === 'field-group' && isset($field['content'])) {
                    foreach ($field['content'] as $subField) {
                        $this->extractFieldToMapping($subField, $mapping);
                    }
                    continue;
                }
                $this->extractFieldToMapping($field, $mapping);
            }
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