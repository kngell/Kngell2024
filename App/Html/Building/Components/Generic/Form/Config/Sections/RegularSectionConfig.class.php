<?php

declare(strict_types=1);
class RegularSectionConfig extends FormSectionConfig
{
    /** @var FormFieldConfig[] */
    private array $fields = [];

    public function __construct(
        string $key,
        string $title,
        ?string $sectionId,
        array $fields = [],
        string $icon = 'icon-edit',
        bool $showRequired = false,
        array $wrapperClass = [],
        ?string $customRenderer = null,
        array $rowIndicesConfig = [],
        array $fieldIndicesMapping = [],
        ?string $layoutType = null,
        array $sectionClass = ['form-section'],
        ?string $sectionKey = null,
        array $customAttributes = [],
        ?string $sectionBodyId = null,
        array $fieldMapping = [],
    ) {
        parent::__construct(
            key: $key,
            title: $title,
            sectionId: $sectionId,
            icon: $icon,
            showRequired: $showRequired,
            wrapperClass: $wrapperClass,
            customRenderer: $customRenderer,
            rowIndicesConfig: $rowIndicesConfig,
            fieldIndicesMapping: $fieldIndicesMapping,
            layoutType: $layoutType,
            sectionClass: $sectionClass,
            sectionKey: $sectionKey,
            customAttributes: $customAttributes,
            fieldMapping: $fieldMapping,
            sectionBodyId: $sectionBodyId,
        );

        $this->fields = $fields;
    }

    /** @return FormFieldConfig[] */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function addField(?FormFieldConfig $field): self
    {
        if (!is_null($field)) {
            $this->fields[] = $field;
        }

        return $this;
    }

    public function setFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function addFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function getFieldsConfig(): array
    {
        return array_map(fn (FormFieldConfig $field) => $field->toArray(), $this->fields);
    }

    public static function create(string $key, string $title, ?string $sectionId = null): self
    {
        return new self($key, $title, $sectionId);
    }
}