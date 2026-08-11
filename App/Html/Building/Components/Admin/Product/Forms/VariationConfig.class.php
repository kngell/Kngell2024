<?php

declare(strict_types=1);

final class VariationConfig
{
    public function __construct(
        private readonly VariationTypeModel $variationTypeModel,
    ) {
    }

    /**
     * Get the base configuration array for variation fields.
     */
    public function getBaseConfig(int $index = 0, array $data = [], bool $includeValues = true): array
    {
        $search = '{i}';
        $replace = (string) $index;

        return [
            [
                'key' => 'variant-type',
                'name' => str_replace($search, $replace, 'variations[{i}][variation_type_id]'),
                'label' => 'Variation Type',
                'type' => 'select',
                'class' => 'span-all',
                'options' => $this->getVariationTypeOptions(),
                'suffixIcon' => 'icon-arrow-down',
                'disabled' => false,
            ],
            [
                'key' => 'variation-id',
                'name' => str_replace($search, $replace, 'variations[{i}][id]'),
                'type' => 'hidden',
            ],
            [
                'key' => 'variation-name',
                'name' => str_replace($search, $replace, 'variations[{i}][name]'),
                'label' => 'Variation Name',
                'placeholder' => 'Large, Red, Cotton...',
                'type' => 'text',
                'class' => 'span-all',
            ],
            [
                'key' => 'variation-sku',
                'name' => str_replace($search, $replace, 'variations[{i}][variation_sku]'),
                'label' => 'Variation SKU',
                'placeholder' => 'TSHIRT-RED-L',
                'type' => 'text',
            ],
            [
                'key' => 'price-modifier',
                'name' => str_replace($search, $replace, 'variations[{i}][price_modifier]'),
                'label' => 'Price Modifier',
                'placeholder' => '+5.00 or -2.50',
                'type' => 'number',
                'step' => '0.01',
            ],
            [
                'key' => 'variation-stock-quantity',
                'name' => str_replace($search, $replace, 'variations[{i}][stock_quantity]'),
                'label' => 'Variation Stock quantity',
                'placeholder' => '0',
                'type' => 'number',
                'step' => '1',
            ],
            [
                'key' => 'variation-status',
                'name' => str_replace($search, $replace, 'variations[{i}][variation_status]'),
                'label' => 'Variation Status',
                'type' => 'select',
                'options' => $this->getVariationStatusOptions(),
                'suffixIcon' => 'icon-arrow-down',
            ],
            [
                'type' => 'variation-attribute-group',
                'wrapperClass' => 'variation-attributes',
                'content' => $this->buildAttributesConfig($index, $data['attributes'] ?? []),
            ],
            [
                'type' => 'button-group',
                'wrapperClass' => 'button-container span-all',
                'content' => [
                    [
                        'type' => 'button',
                        'style' => 'secondary',
                        'size' => 'md',
                        'icon' => 'icon-plus',
                        'label' => 'Add Variation',
                        'attributes' => [
                            'dataAction' => 'add-variation-group',
                        ],
                    ],
                    [
                        'type' => 'button',
                        'style' => 'danger-light',
                        'size' => 'md',
                        'class' => ['btn--icon-only'],
                        'icon' => 'icon-cancel',
                        'ariaLabel' => 'Remove Variation ' . ($index + 1),
                        'attributes' => [
                            'data-action' => 'remove-group',
                            'data-group-id' => (string) ($index + 1),
                        ],
                    ],
                ],
            ],
        ];
    }

    public function buildAttributesConfig(int $variationIndex, array $attributes = []): array
    {
        $search = '{i}';
        $replace = (string) $variationIndex;
        $attributeGroups = [];
        if (empty($attributes)) {
            return $this->createAttributeGroup($search, $replace, 0);
        }

        $attrIndex = 0;
        foreach ($attributes as $attribute) {
            $attributeGroup = $this->createAttributeGroup($search, $replace, $attrIndex);
            $attributeGroups = array_merge($attributeGroups, $attributeGroup);
            $attrIndex++;
        }

        return $attributeGroups;
    }

    public function getStaticConfig(): array
    {
        return [
            [
                'type' => 'variation-group',
                'wrapperClass' => 'variation-group span-all',
                'content' => $this->getBaseConfig(0, [], false),
            ],
        ];
    }

    public function buildVariationGroup(int $index, array $data = []): array
    {
        $fields = $this->getBaseConfig($index, $data, !empty($data));
        $this->addDataPaths($fields);
        return [$fields];
    }

    public function getFieldMapping(): array
    {
        $sourcePrefix = 'product_variation_show';

        return [
            "{$sourcePrefix}.*.variation_type.id" => 'variations.*.variation_type_id',
            "{$sourcePrefix}.*.id" => 'variations.*.id',
            "{$sourcePrefix}.*.name" => 'variations.*.name',
            "{$sourcePrefix}.*.variation_sku" => 'variations.*.variation_sku',
            "{$sourcePrefix}.*.price_modifier" => 'variations.*.price_modifier',
            "{$sourcePrefix}.*.stock_quantity" => 'variations.*.stock_quantity',
            "{$sourcePrefix}.*.variation_status" => 'variations.*.variation_status',
            "{$sourcePrefix}.*.variation_attribute.*.id" => 'variations.*.attributes.*.id',
            "{$sourcePrefix}.*.variation_attribute.*.attribute_name" => 'variations.*.attributes.*.attribute_name',
            "{$sourcePrefix}.*.variation_attribute.*.attribute_value" => 'variations.*.attributes.*.attribute_value',
        ];
    }

    private function createAttributeGroup(string $search, string $replace, int $attributeIndex): array
    {
        $attrIndex = (string) $attributeIndex;
        $namePattern = "variations[{i}][attributes][{$attrIndex}][id]";

        return [
            [
                'key' => 'attribute-id',
                'name' => str_replace($search, $replace, $namePattern),
                'type' => 'hidden',
            ],
            [
                'key' => 'attribute-name',
                'name' => str_replace($search, $replace, str_replace('[id]', '[attribute_name]', $namePattern)),
                'label' => 'Attribute Name',
                'placeholder' => 'color...',
                'type' => 'text',
            ],
            [
                'key' => 'attribute-value',
                'name' => str_replace($search, $replace, str_replace('[id]', '[attribute_value]', $namePattern)),
                'label' => 'Attribute Value',
                'placeholder' => 'red...',
                'type' => 'text',
            ],
        ];
    }

    private function addDataPaths(array &$fields): void
    {
        foreach ($fields as &$field) {
            if (isset($field['name'])) {
                $field['data_path'] = str_replace(['[', ']'], ['.', ''], rtrim($field['name'], ']'));
            }
        }
    }

    private function getVariationStatusOptions(): array
    {
        $options = ['' => '-- Select Status --'];
        foreach (ProductVariationStatus::cases() as $case) {
            $options[ucfirst($case->value)] = ucfirst($case->value);
        }
        return $options;
    }

    private function getVariationTypeOptions(): array
    {
        try {
            $entities = $this->variationTypeModel->all()->asClass();
            return $this->buildOptions($entities, '-- Select Type --');
        } catch (QueryResultException $e) {
            error_log('VariationType options load failed: ' . $e->getMessage());
            return ['' => '-- Error Loading Variation Types --'];
        }
    }

    private function buildOptions(array $entities, string $defaultLabel): array
    {
        $options = ['' => $defaultLabel];
        foreach ($entities as $entity) {
            if ($entity instanceof VariationType) {
                $options[$entity->getId()] = ucwords($entity->getName());
            } elseif (is_object($entity) && property_exists($entity, 'id')) {
                $options[$entity->id] = $entity->name ?? 'Unknown';
            }
        }
        return $options;
    }
}