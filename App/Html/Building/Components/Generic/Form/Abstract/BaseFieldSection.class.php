<?php

declare(strict_types=1);

abstract class BaseFieldSection extends AbstractBaseHtmlSection implements FieldSectionInterface
{
    use MapperExtractorTrait;

    protected array $formValues = [];
    protected MediaSectionConfig|RegularSectionConfig $config;

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
    ) {
        parent::__construct($builder, $iconBuilder);
    }

    public function getFieldMapping(array $formValues = []): array
    {
        $mapping = [];
        $config = $this->getConfig();

        if (is_array($config)) {
            foreach ($config as $field) {
                if ($field instanceof FormFieldConfig) {
                    $field = $field->toArray();
                }
                if (($field['type'] ?? '') === 'field-group' && isset($field['content'])) {
                    foreach ($field['content'] as $subField) {
                        $this->extractFieldToMapping($subField, $mapping);
                    }
                    continue;
                }
                $this->extractFieldToMapping($field, $mapping);
            }
        }

        if (isset($this->config)) {
            $fieldMapping = $this->config->getFieldMapping();
            return $fieldMapping ?: $mapping;
        }

        return $mapping;
    }

    /**
     * @param MediaSectionConfig|RegularSectionConfig $config
     *
     * @return BaseFieldSection
     */
    public function setConfig(MediaSectionConfig|RegularSectionConfig $config): BaseFieldSection
    {
        $this->config = $config;

        return $this;
    }

    protected function mapRelation(string $relationName, string $targetName, Entity $instance): array
    {
        $pkField = $instance->getEntityKeyField();

        return [
            "{$relationName}.{$pkField}" => $targetName,
        ];
    }
}