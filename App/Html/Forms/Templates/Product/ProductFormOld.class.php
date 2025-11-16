<?php

declare(strict_types=1);

final readonly class ProductFormOld extends AbstractForm
{
    protected const array CUSTOM_ATTRBUTES = [
        'data-validate' => 'true',
        'data-validation-rules' => 'productRules',
    ];
    private const array SPAN_ALL_SECTIONS = ['pricing', 'inventory', 'variation', 'shipping'];
    private const string FRM_ID = 'product-frm';
    private const string FRM_NAME = 'product-frm';
    private const string FRM_CLASS = 'product__body-frm';

    private const array FORM_SECTIONS = [
        'general-information' => [
            [
                'key' => 'id',
                'name' => 'public_id',
                'id' => 'public-id',
                'type' => 'hidden',
            ],
            [
                'key' => 'name',
                'name' => 'name',
                'id' => 'product-name',
                'label' => 'Product Name',
                'placeholder' => 'Type product name here...',
                'type' => 'text',
            ],
            [
                'key' => 'short-description',
                'name' => 'short_description',
                'label' => 'Product Short Description',
                'placeholder' => 'Type short description here...',
                'type' => 'text',
            ],
            [
                'key' => 'description',
                'name' => 'description',
                'label' => 'Product Description',
                'placeholder' => 'Type product description here...',
                'type' => 'textarea',
            ],
        ],
        'media' => [
            [
                'key' => 'image-gallery',
                'name' => 'img_gallery',
                'title' => 'Photo',
                'type' => 'dropzone',
                'accept' => 'image/*',
                'icon' => 'icon-mediaphoto',
                'icon-aria' => 'Media Photo Avatar',
                'buttonLabel' => 'Add Image',
                'dragText' => 'Drag and drop image here, or click to browse',
                'multiple' => true,
                'class' => 'media-photo',
            ],
            [
                'key' => 'video',
                'name' => 'product-video',
                'title' => 'Video',
                'type' => 'dropzone',
                'accept' => 'video/*',
                'icon' => 'icon-mediavideo',
                'icon-aria' => 'Media Video Avatar',
                'buttonLabel' => 'Add Video',
                'dragText' => 'Drag and drop video here, or click to add video',
                'multiple' => true,
                'class' => 'media-video',
            ],
        ],
        'pricing' => [
            [
                'key' => 'base-price',
                'name' => 'price',
                'label' => 'Base Price',
                'type' => 'currency', // Use currency type
                'placeholder' => '0.00',
                'step' => '0.01',
                'class' => 'span-all',
                'defaultCurrency' => 'EUR',
                'currencies' => [
                    'USD' => 'USD',
                    'EUR' => 'EUR',
                    'GBP' => 'GBP',
                ],
            ],
            [
                'key' => 'compare-price',
                'name' => 'compare_price',
                'label' => 'Compare Price',
                'type' => 'currency', // Use currency type
                'placeholder' => '0.00',
                'class' => 'span-all',
                'step' => '0.01',
                'defaultCurrency' => 'EUR',
                'currencies' => [
                    'USD' => 'USD',
                    'EUR' => 'EUR',
                    'GBP' => 'GBP',
                ],
            ],
            [
                'key' => 'cost-price',
                'name' => 'cost_price',
                'label' => 'Cost Price',
                'type' => 'currency', // Use currency type
                'placeholder' => '0.00',
                'step' => '0.01',
                'defaultCurrency' => 'EUR',
                'currencies' => [
                    'USD' => 'USD',
                    'EUR' => 'EUR',
                    'GBP' => 'GBP',
                ],
            ],
            [
                'key' => 'tax-class',
                'name' => 'tax_class',
                'label' => 'VAT class',
                'type' => 'select',
                'default' => 'standard',
                'options' => [
                    '' => '-- Select a VAT class --',
                    '1' => 'Standard',
                    '2' => 'Reduced',
                    '3' => 'Super-reduced',
                    '4' => 'Zero',
                    '5' => 'Digital',
                    '6' => 'Services',
                    '7' => 'Custom',
                ],
                'suffixIcon' => 'icon-arrow-down',
            ],
            [
                'key' => 'price-includes-tax',
                'name' => 'price_includes_tax',
                'label' => 'Price includes tax',
                'class' => 'span-all',
                'type' => 'checkbox',
            ],
        ],
        'inventory' => [
            [
                'key' => 'sku',
                'name' => 'sku',
                'label' => 'SKU',
                'placeholder' => 'Enter SKU here...',
                'type' => 'text',
            ],
            [
                'key' => 'stock-quantity',
                'name' => 'stock_quantity',
                'label' => 'Stock Quantity',
                'placeholder' => 'Enter stock quantity',
                'type' => 'number',
            ],
            [
                'key' => 'stock-status',
                'name' => 'stock_status',
                'label' => 'Stock Status',
                'type' => 'select',
                'options' => [
                    '' => '-- Select stock status --',
                    'in_stock' => 'In Stock',
                    'out_of_stock' => 'Out of Stock',
                    'preorder' => 'Preorder',
                    'backorder' => 'Backorder',
                    'discontinued' => 'Discontinued',
                    'limited_stock' => 'Limited Stock',
                ],
                'suffixIcon' => 'icon-arrow-down',
                'aria' => 'Arrow Down',
                'hint' => '',
            ],

            [
                'key' => 'barcode',
                'name' => 'barcode',
                'label' => 'Barcode',
                'placeholder' => 'Enter Product barcode...',
                'type' => 'text',
            ],
            [
                'key' => 'allow_backorders',
                'name' => 'allow-backorders',
                'label' => 'Allow Backorders',
                'class' => 'span-all',
                'type' => 'checkbox',
            ],
        ],
        'variation' => [
            [
                'type' => 'field-group',
                'wrapperClass' => 'variation-group span-all',
                'content' => [
                    [
                        'key' => 'variant-type',
                        'name' => 'variations[0][variant_type]',
                        'label' => 'Variation Type',
                        'type' => 'select',
                        'class' => 'span-all',
                        'options' => [],
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
                ],
            ],
        ],
        'shipping' => [
            [
                'key' => 'is-physical-product',
                'name' => 'is-physical-product',
                'label' => 'This is a physical
                                         product',
                'type' => 'checkbox',
                'class' => ['span-all', 'blue-check'],
            ],
            [
                'key' => 'weight',
                'name' => 'weight',
                'label' => 'Weight',
                'type' => 'text',
                'placeholder' => 'Product weight...',
            ],
            [
                'key' => 'height',
                'name' => 'height',
                'label' => 'Height',
                'type' => 'text',
                'placeholder' => 'Height (cm)...',
            ],
            [
                'key' => 'length',
                'name' => 'length',
                'label' => 'Length',
                'type' => 'text',
                'placeholder' => 'Length (cm)...',
            ],
            [
                'key' => 'width',
                'name' => 'width',
                'label' => 'width',
                'type' => 'text',
                'placeholder' => 'Width (cm)...',
            ],
        ],
        'category' => [
            [
                'key' => 'category',
                'name' => 'category',
                'label' => 'Product Category',
                'type' => 'select',
                'options' => [
                    '' => 'Select a category',
                    'active' => 'Watch',
                    'clothing' => 'Clothing',
                    'books' => 'Books',
                    'furniture' => 'Furniture',
                ],
                'suffixIcon' => 'icon-arrow-down',
                'aria' => 'Arrow Down',
                'hint' => '',
            ],
            [
                'key' => 'subcategory',
                'name' => 'subcategory',
                'label' => 'Product Sub Category',
                'type' => 'select',
                'options' => [
                    '' => 'Select a subcategory',
                    'active' => 'Watch',
                    'clothing' => 'Clothing',
                    'books' => 'Books',
                    'furniture' => 'Furniture',
                ],
                'suffixIcon' => 'icon-arrow-down',
                'aria' => 'Arrow Down',
                'hint' => '',
            ],
            [
                'key' => 'product-tag',
                'name' => 'product-tag',
                'label' => 'ProductTags',
                'type' => 'select',
                'options' => [
                    '' => 'Select product tag',
                    'active' => 'Watch',
                    'clothing' => 'Gadget',
                    'books' => 'Books',
                    'furniture' => 'Furniture',
                ],
                'suffixIcon' => 'icon-arrow-down',
                'aria' => 'Arrow Down',
                'hint' => '',
                'customComponent' => 'tagPreview',
                // 'customElements' => [
                //     [
                //         'tag' => 'div',
                //         'class' => 'tag-preview',
                //         'attributes' => [
                //             'data-role' => 'tag-preview',
                //         ],
                //     ],
                // ],
            ],
        ],
    ];

    public function make(string $action = '', array|Entity|bool $formValues = [], array $formErrors = []): string
    {
        $form = $this->builder;
        $formHtml = $form->tag('div')->class('product__body')->add(
            $form->htmlBlock(
                parent::make($action, $formValues, $formErrors),
            ),
        )->generate();

        $footerHtml = $this->renderFooter($this->calculateCompletion($formValues));
        return $formHtml . $footerHtml;
    }

    protected function getFormCustomAttributes(): array
    {
        return self::CUSTOM_ATTRBUTES;
    }

    protected function calculateCompletion(array|Entity|bool $formValues): int
    {
        if (empty($formValues)) {
            return 0;
        }

        // Convert to array using the new toArray() method
        $values = [];

        if (is_array($formValues)) {
            $values = $formValues;
        } elseif ($formValues instanceof Entity) {
            $values = $formValues->toArray();
        } elseif (is_bool($formValues)) {
            $values = [];
        }

        $filledFields = 0;
        $totalFields = 0;

        foreach (self::FORM_SECTIONS as $sectionFields) {
            foreach ($sectionFields as $field) {
                // Only count actual form fields (not buttons, field-groups, etc.)
                if ($this->isCountableField($field)) {
                    $totalFields++;
                    $fieldKey = $field['key'] ?? $field['name'] ?? null;

                    if ($fieldKey && !empty($values[$fieldKey])) {
                        $filledFields++;
                    }
                }
            }
        }

        return $totalFields > 0 ? (int) (($filledFields / $totalFields) * 100) : 0;
    }

    protected function isCountableField(array $field): bool
    {
        $nonCountableTypes = ['button', 'field-group'];
        return !in_array($field['type'] ?? 'text', $nonCountableTypes);
    }

    protected function renderFooter(int $completionPercentage = 0): string
    {
        $form = $this->builder->form();

        $footer = $form->tag('div')
            ->class('product__footer', 'buttons-group')
            ->add(
                $this->renderCompletionProgress($completionPercentage, $form),
                $this->renderActionButtons($form),
            );

        return $footer->generate();
    }

    protected function renderCompletionProgress(int $percentage, FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')
            ->class('completeness')
            ->add(
                $form->tag('span')
                    ->class('completeness__text')
                    ->content('Product completion:'),
                $form->tag('div')
                    ->class('completeness__progress-container')
                    ->add(
                        $form->tag('div')
                            ->class('completeness-progress')
                            ->add(
                                $form->tag('div')
                                    ->class('completeness-progress--bar')
                                    ->custom(['style' => "width: {$percentage}%;"]),
                            ),
                        $form->tag('span')
                            ->class('completeness-percentage')
                            ->content("{$percentage}%"),
                    ),
            );
    }

    protected function renderActionButtons(FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')
            ->class('buttons')
            ->add(
                $this->createCancelButton($form),
                $this->createSubmitButton($form),
            );
    }

    protected function createCancelButton(FormBuilder $form): AbstractHtmlComponent
    {
        return $form->button()
            ->type('button')
            ->class('btn', 'btn--outlined', 'btn--md-compact', 'btn--icon-left')
            ->add(
                $form->tag('span')
                    ->class('btn__icon')
                    ->add(
                        $this->createIcon($form, 'icon-cancel', 'Cancel'),
                    ),
                $form->tag('span')
                    ->class('btn__label')
                    ->content('Cancel'),
            );
    }

    protected function createSubmitButton(FormBuilder $form): AbstractHtmlComponent
    {
        return $form->button()
            ->type('submit')
            ->class('btn', 'btn--primary', 'btn--md-compact', 'btn--icon-left')
            ->custom(['form' => self::FRM_ID]) // Reference the form ID
            ->add(
                $form->tag('span')
                    ->class('btn__icon')
                    ->add(
                        $this->createIcon($form, 'icon-plus', 'Add Product'),
                    ),
                $form->tag('span')
                    ->class('btn__label')
                    ->content('Add product'),
            );
    }

    protected function createTagPreviewComponent(FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')
            ->class('tag-preview')
            ->add(
                $this->createTagButton($form, 'Watch'),
                $this->createTagButton($form, 'Gadget'),
                // Add more tags as needed
            );
    }

    protected function createTagButton(FormBuilder $form, string $tagName): AbstractHtmlComponent
    {
        return $form->button()
            ->type('button')
            ->class('btn', 'btn--secondary', 'btn--md-tags', 'btn--icon-right')
             ->custom([
                 'data-tag' => $tagName,
                 'data-action' => 'remove-tag',
             ])->add(
                 $form->tag('span')
                     ->class('btn__icon')
                     ->add(
                         $this->createIcon($form, 'icon-cancel', "Remove $tagName"),
                     ),
                 $form->tag('span')
                     ->class('btn__label')
                     ->content($tagName),
             );
    }

    protected function getFormSections(array|Entity|bool $formValues = []): array
    {
        $sections = self::FORM_SECTIONS;

        // Detect mode
        $isEdit = $this->isEditMode($formValues);

        // Rebuild variation section dynamically - content should be direct array, not wrapped
        $sections['variation'] = [
            [
                'type' => 'field-group',
                'wrapperClass' => 'variation-group span-all',
                'content' => $this->buildVariationGroups($isEdit, $formValues),
            ],
        ];

        return $sections;
    }

    protected function getFormId(): string
    {
        return self::FRM_ID;
    }

    protected function getFormName(): string
    {
        return self::FRM_NAME;
    }

    protected function getFormClass(): string
    {
        return self::FRM_CLASS;
    }

    protected function getSpanAllSections(): array
    {
        return self::SPAN_ALL_SECTIONS;
    }

    protected function buildFormLayout(FormBuilder $form): array
    {
        $sectionsConfig = $this->getFormSections();
        $leftSections = [];
        $rightSections = [];

        foreach (array_keys($sectionsConfig) as $sectionKey) {
            $section = $this->sectionRenderer->render($sectionKey, $form, $sectionsConfig, $this);

            // Distribute sections between left and right columns
            if (in_array($sectionKey, ['general-information', 'media', 'pricing', 'inventory', 'variation', 'shipping'])) {
                $leftSections[] = $section;
            } else {
                $rightSections[] = $section;
            }
        }

        return [
            $form->tag('div')->class('product__body-frm--left')->add(...$leftSections),
            $form->tag('div')->class('product__body-frm--right')->add(...$rightSections),
        ];
    }

    private function getVariationTypeOptions(): array
    {
        try {
            /** @var VariationTypeModel $model */
            $model = App::diget(VariationTypeModel::class);

            $entities = $model->all()->asClass();

            if (empty($entities)) {
                return ['' => '-- No Variation Types Available --'];
            }
            // Build dropdown options
            $options = ['' => '-- Select Variation Type --'];
            foreach ($entities as $entity) {
                if ($entity instanceof VariationType) {
                    $options[$entity->getId()] = ucwords($entity->getName());
                } elseif (is_object($entity)) {
                    $options[$entity->id ?? ''] = $entity->name ?? 'Unknown';
                }
            }
            return $options;
        } catch (QueryResultException $e) {
            error_log('VariationType options load failed: ' . $e->getMessage());
            return ['' => '-- Error Loading Variation Types --'];
        }
    }

    private function getStockStatusOptions(): array
    {
        try {
            /** @var StockStatusModel $model */
            $model = App::diget(StockStatusModel::class);

            $entities = $model->all()->asClass();

            if (empty($entities)) {
                return ['' => '-- No Stock Status Available --'];
            }

            // Build dropdown options
            $options = ['' => '-- Select Status --'];
            foreach ($entities as $entity) {
                if ($entity instanceof StockStatus) {
                    $options[$entity->getId()] = ucwords($entity->getLabel());
                } elseif (is_object($entity)) {
                    $options[$entity->id ?? ''] = $entity->label ?? $entity->name ?? 'Unknown';
                }
            }
            return $options;
        } catch (QueryResultException $e) {
            error_log('StockStatus options load failed: ' . $e->getMessage());
            return ['' => '-- Error Loading Stock Status --'];
        }
    }

    private function isEditMode(array|Product|bool $formValues): bool
    {
        if ($formValues instanceof Product) {
            return !empty($formValues->getId());
        }

        return is_array($formValues) && !empty($formValues['id']);
    }

    private function buildVariationGroups(bool $isEdit, array|Product|bool $formValues): array
    {
        if (!$isEdit) {
            // New product → show one empty variation with proper structure
            return $this->buildVariationGroup(0, []);
        }

        // Existing product → load variations from DB
        $productId = $formValues instanceof Product
            ? $formValues->getId()
            : ($formValues['id'] ?? null);

        if (!$productId) {
            return $this->buildVariationGroup(0, []);
        }

        /** @var ProductVariationModel $model */
        $model = App::diget(ProductVariationModel::class);
        $variations = $model->find($productId)->asArray();

        if (empty($variations)) {
            return $this->buildVariationGroup(0, []);
        }

        $groups = [];
        foreach ($variations as $index => $variation) {
            $groups = array_merge($groups, $this->buildVariationGroup($index, $variation));
        }

        return $groups;
    }

    private function getBaseVariationGroup(): array
    {
        return [
            [
                'key' => 'variant-type',
                'name' => 'variations[{i}][variant_type]',
                'label' => 'Variation Type',
                'type' => 'select',
                'class' => 'span-all',
                'options' => $this->getVariationTypeOptions(),
                'suffixIcon' => 'icon-arrow-down',
            ],
            [
                'key' => 'variation-name',
                'name' => 'variations[{i}][name]',
                'label' => 'Variation Name',
                'placeholder' => 'Large, Red, Cotton...',
                'type' => 'text',
                'class' => 'span-all',
            ],
            [
                'key' => 'variation-sku',
                'name' => 'variations[{i}][sku]',
                'label' => 'Variation SKU',
                'placeholder' => 'TSHIRT-RED-L',
                'type' => 'text',
            ],
            [
                'key' => 'price-modifier',
                'name' => 'variations[{i}][price_modifier]',
                'label' => 'Price Modifier',
                'placeholder' => '+5.00 or -2.50',
                'type' => 'number',
                'step' => '0.01',
            ],
            [
                'key' => 'variation-stock-quantity',
                'name' => 'variations[{i}][stock_quantity]',
                'label' => 'Variation Stock quantity',
                'placeholder' => '0',
                'type' => 'number',
                'step' => '1',
            ],
            [
                'key' => 'variation-status',
                'name' => 'variations[{i}][status]',
                'label' => 'Variation Status',
                'type' => 'select',
                'options' => $this->getStockStatusOptions(),
                'suffixIcon' => 'icon-arrow-down',
            ],
            [
                'type' => 'field-group',
                'wrapperClass' => 'variation-attributes',
                'content' => [
                    [
                        'key' => 'attribute-name',
                        'name' => 'variations[{i}][attributes][0][attribute_name]',
                        'label' => 'Attribute Name',
                        'placeholder' => 'color...',
                        'type' => 'text',
                    ],
                    [
                        'key' => 'attribute-value',
                        'name' => 'variations[{i}][attributes][0][attribute_value]',
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
                        'ariaLabel' => 'Remove Variation {i}',
                        'attributes' => [
                            'data-action' => 'remove-group',
                            'data-group-id' => '{i}',
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
            // Replace placeholder index in name and ariaLabel
            if (isset($field['name'])) {
                $field['name'] = str_replace('{i}', (string) $index, $field['name']);
            }
            if (isset($field['ariaLabel'])) {
                $field['ariaLabel'] = str_replace('{i}', (string) ($index + 1), $field['ariaLabel']);
            }
            if (isset($field['attributes']['data-group-id'])) {
                $field['attributes']['data-group-id'] = str_replace('{i}', (string) ($index + 1), $field['attributes']['data-group-id']);
            }

            // Fill existing data if available
            if (!empty($field['key']) && isset($data[$field['key']])) {
                $field['value'] = $data[$field['key']];
            }

            // Recursively process nested content (for field-groups and button-groups)
            if (isset($field['content']) && is_array($field['content'])) {
                $this->processFieldGroup($field['content'], $index, $data);
            }
        }
    }
}