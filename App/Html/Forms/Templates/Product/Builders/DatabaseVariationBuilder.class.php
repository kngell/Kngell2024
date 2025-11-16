<?php

declare(strict_types=1);

final class DatabaseVariationBuilder implements VariationBuilderInterface
{
    public function __construct(
        private readonly ProductVariationModel $variationModel,
        private readonly VariationTypeModel $variationTypeModel,
        private readonly StockStatusModel $stockStatusModel,
    ) {
    }

    public function buildVariationGroups(bool $isEdit, $formValues): array
    {
        if (!$isEdit) {
            return $this->buildVariationGroup(0, []);
        }

        $productId = $this->extractProductId($formValues);
        if (!$productId) {
            return $this->buildVariationGroup(0, []);
        }

        $variations = $this->variationModel->find($productId)->asArray();
        if (empty($variations)) {
            return $this->buildVariationGroup(0, []);
        }

        $groups = [];
        foreach ($variations as $index => $variation) {
            $groups = array_merge($groups, $this->buildVariationGroup($index, $variation));
        }

        return $groups;
    }

    public function getBaseVariationGroup(): array
    {
        return [
            [
                'key' => 'variant-type',
                'name' => 'variations[{i}][variation_type_id]',
                'label' => 'Variation Type',
                'type' => 'select',
                'class' => 'span-all',
                'options' => $this->getVariationTypeOptions(),
                'suffixIcon' => 'icon-arrow-down',
            ],
            [
                'key' => 'variation-name',
                'name' => 'variations[0][name]',
                'label' => 'Variation Name',
                'placeholder' => 'Large, Red, Cotton...',
                'type' => 'text',
                'class' => 'span-all',
            ],
            [
                'key' => 'variation-sku',
                'name' => 'variations[0][sku]',
                'label' => 'Variation SKU',
                'placeholder' => 'TSHIRT-RED-L',
                'type' => 'text',
            ],
            [
                'key' => 'price-modifier',
                'name' => 'variations[0][price_modifier]',
                'label' => 'Price Modifier',
                'placeholder' => '+5.00 or -2.50',
                'type' => 'number',
                'step' => '0.01',
            ],
            [
                'key' => 'variation-stock-quantity',
                'name' => 'variations[0][stock_quantity]',
                'label' => 'Variation Stock quantity',
                'placeholder' => '0',
                'type' => 'number',
                'step' => '1',
            ],
            [
                'key' => 'variation-status',
                'name' => 'variations[0][status]',
                'label' => 'Variation Status',
                'type' => 'select',
                'options' => [
                    '' => '-- Select Status --',
                    'active' => 'Active',
                    'inactive' => 'Inactive',
                ],
                'suffixIcon' => 'icon-arrow-down',
            ],
            [
                'type' => 'field-group',
                'wrapperClass' => 'variation-attributes',
                'content' => [
                    // [
                    //     'type' => 'html',
                    //     'content' => 'Variation Attributes',
                    //     'tag' => 'h5',
                    // ],
                    [
                        'key' => 'attribute-name',
                        'name' => 'variations[0][attributes][0][attribute_name]',
                        'label' => 'Attribute Name',
                        'placeholder' => 'color...',
                        'type' => 'text',
                    ],
                    [
                        'key' => 'attribute-value',
                        'name' => 'variations[0][attributes][0][attribute_value]',
                        'label' => 'Attribute Value',
                        'placeholder' => 'red...',
                        'type' => 'text',
                    ],
                ],
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
                        'ariaLabel' => 'Remove Variation 1',
                        'attributes' => [
                            'data-action' => 'remove-group',
                            'data-group-id' => '1',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function buildVariationGroup(int $index, array $data): array
    {
        $fields = $this->getBaseVariationGroup();
        $this->processFieldGroup($fields, $index, $data);
        return $fields;
    }

    private function processFieldGroup(array &$fields, int $index, array $data): void
    {
        foreach ($fields as &$field) {
            if (isset($field['name'])) {
                $field['name'] = str_replace('{i}', (string) $index, $field['name']);
            }
            if (isset($field['ariaLabel'])) {
                $field['ariaLabel'] = str_replace('{i}', (string) ($index + 1), $field['ariaLabel']);
            }
            if (isset($field['attributes']['data-group-id'])) {
                $field['attributes']['data-group-id'] = str_replace('{i}', (string) ($index + 1), $field['attributes']['data-group-id']);
            }

            if (!empty($field['key']) && isset($data[$field['key']])) {
                $field['value'] = $data[$field['key']];
            }

            if (isset($field['content']) && is_array($field['content'])) {
                $this->processFieldGroup($field['content'], $index, $data);
            }
        }
    }

    private function getVariationTypeOptions(): array
    {
        try {
            $entities = $this->variationTypeModel->all()->asClass();
            return $this->buildOptions($entities, '-- Select Variation Type --');
        } catch (QueryResultException $e) {
            error_log('VariationType options load failed: ' . $e->getMessage());
            return ['' => '-- Error Loading Variation Types --'];
        }
    }

    private function extractProductId($formValues): ?int
    {
        if ($formValues instanceof Product) {
            return $formValues->getId();
        }
        return $formValues['id'] ?? null;
    }

    private function buildOptions(array $entities, string $defaultLabel): array
    {
        $options = ['' => $defaultLabel];
        foreach ($entities as $entity) {
            if ($entity instanceof VariationType) {
                $options[$entity->getId()] = ucwords($entity->getName());
            } elseif (is_object($entity)) {
                $options[$entity->id ?? ''] = $entity->name ?? 'Unknown';
            }
        }
        return $options;
    }
}