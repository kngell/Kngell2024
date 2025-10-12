<?php

declare(strict_types=1);

final readonly class ProductFormBackupCopy implements FormTemplateInterface
{
    private const array SPAN_ALL_SECTIONS = ['pricing'];
    private const string FRM_ID = 'product-frm';
    private const string FRM_NAME = 'product-frm';
    private const string FRM_CLASS = 'product__body-frm';
    private const string INPUT_BOX = 'input-box';
    private const string INPUT_CLASS = 'input-box__input';
    private const string TEXTAREA_CLASS = 'input-box__textarea';
    private const string PREFIX_CLASS = 'input-box__prefix';
    private const string SUFFIX_CLASS = 'input-box__suffix';
    private const string INPUT_CONTAINER = 'input-box__container';
    private const string INPUT_SELECT = 'input-box__select';
    private const string ICON_SPRITE = 'icons-sprite.svg';
    private const string LABEL_CLASS = 'input-box__label';
    private const string HINT_CLASS = 'input-box__hint-text';
    private const array FIELDS = [
        'general-information' => [
            [
                'key'         => 'name',
                'name'        => 'name',
                'label'       => 'Product Name',
                'placeholder' => 'Type product name here...',
                'type'        => 'text',
            ],
            [
                'key'         => 'short_description',
                'name'        => 'short_description',
                'label'       => 'Product Short Description',
                'placeholder' => 'Type short description here...',
                'type'        => 'text',
            ],
            [
                'key'         => 'description',
                'name'        => 'description',
                'label'       => 'Product Description',
                'placeholder' => 'Type product description here...',
                'type'        => 'textarea',
            ],
        ],
        'media' => [
            [
                'key'         => 'photo',
                'name'        => 'product-images',
                'label'       => 'Photo',
                'type'        => 'dropzone',
                'accept'      => 'image/*',
                'icon'        => 'icon-mediaphoto',
                'buttonLabel' => 'Add Image',
                'dragText'    => 'Drag and drop image here, or click to browse',
                'multiple'    => true,
                'class'       => 'media-photo',
            ],
            [
                'key'         => 'video',
                'name'        => 'product-video',
                'label'       => 'Video',
                'type'        => 'dropzone',
                'accept'      => 'video/*',
                'icon'        => 'icon-mediavideo',
                'buttonLabel' => 'Add Video',
                'dragText'    => 'Drag and drop video here, or click to add video',
                'multiple'    => true,
                'class'       => 'media-video',
            ],
        ],
        'pricing' => [
            [
                'key'         => 'base_price',
                'name'        => 'base-price',
                'class'       => 'span-all',
                'label'       => 'Base Price',
                'placeholder' => 'Type base price here...',
                'type'        => 'text',
                'prefixIcon'  => 'icon-dollar',
            ],
            [
                'key'         => 'discount_type',
                'name'        => 'discount-type',
                'label'       => 'Discount Type',
                'type'        => 'select',
                'options'     => [
                    ''            => '-- Select discount type --',
                    'electronics' => 'Electronics',
                    'clothing'    => 'Clothing',
                    'books'       => 'Books',
                    'furniture'   => 'Furniture',
                ],
                'suffixIcon'  => 'icon-arrow-down',
                'hint'        => 'Choose the discount type',
            ],
            [
                'key'         => 'discount_percentage',
                'name'        => 'discount-percentage',
                'label'       => 'Discount Percentage (%)',
                'placeholder' => 'Type discount percentage...',
                'type'        => 'text',
            ],
            [
                'key'         => 'tax_class',
                'name'        => 'tax-class',
                'label'       => 'Tax Class',
                'type'        => 'select',
                'options'     => [
                    ''            => '-- Select a tax class --',
                    'electronics' => 'Electronics',
                    'clothing'    => 'Clothing',
                    'books'       => 'Books',
                    'furniture'   => 'Furniture',
                ],
                'suffixIcon'  => 'icon-arrow-down',
            ],
            [
                'key'         => 'vat_amount',
                'name'        => 'vat-amount',
                'label'       => 'VAT Amount',
                'placeholder' => 'Type VAT amount here...',
                'type'        => 'text',
            ],
        ],
        // 'inventory' => [
        //     [
        //         'key' => 'sku',
        //         'name' => 'sku',
        //         'label' => 'SKU',
        //         'placeholder' => 'Enter SKU here...',
        //         'type' => 'text',
        //     ],
        //     [
        //         'key' => 'stockQuantity',
        //         'name' => 'stock-quantity',
        //         'label' => 'Stock Quantity',
        //         'placeholder' => 'Enter stock quantity',
        //         'type' => 'number',
        //     ],
        // ],
        // 'variant' => [],
        // 'shipping' => [],
        // 'category' => [
        //     [
        //         'key' => 'category',
        //         'name' => 'product-category',
        //         'label' => 'Category',
        //         'type' => 'select',
        //         'options' => [],
        //     ],
        // ],
        // 'status' => [
        //     [
        //         'key' => 'status',
        //         'name' => 'product-status',
        //         'label' => 'Product Status',
        //         'type' => 'select',
        //         'options' => [],
        //     ],
        // ],
    ];

    public function __construct(private HtmlBuilder $builder)
    {
    }

    public function make(string $action = '', array|Entity|bool $formValues = [], array $formErrors = []): string
    {
        $form = $this->builder->form()
            ->formValues($formValues)
            ->formErrors($formErrors);

        return $form->name(self::FRM_NAME)
            ->method('post')
            ->id(self::FRM_ID)
            ->class(self::FRM_CLASS)
            ->enctype(Enctype::FORM_DATA->value)
            ->add(
                $this->formLeft($form),
                $this->formRight($form),
            )
            ->generate();
    }

    private function formLeft(FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')->class('product__body-frm--left')->add(
            $this->renderSection('general-information', $form),
            $this->renderSection('media', $form),
            $this->renderSection('pricing', $form),
            // $this->renderSection('inventory', $form),
            // $this->renderSection('variant', $form),
            // $this->renderSection('shipping', $form),
        );
    }

    private function formRight(FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')->class('product__body-frm--right')->add(
            // $this->renderSection('category', $form),
            // $this->renderSection('status', $form),
        );
    }

    private function renderSection(string $sectionKey, $form): AbstractHtmlComponent
    {
        if (!isset(self::FIELDS[$sectionKey])) {
            throw new InvalidArgumentException("Section $sectionKey not defined.");
        }

        $fields = [];
        foreach (self::FIELDS[$sectionKey] as $field) {
            $fields[] = $this->renderField($field, $form);
        }

        return $form->tag('div')->class('frm-section ' . $sectionKey)->add(
            $form->tag('h4')
                ->class('frm-section__title' . $this->extraClass($sectionKey))
                ->content(ucwords(str_replace('-', ' ', $sectionKey))),
            $form->tag('div')->class('frm-section__body' . $this->extraClass($sectionKey))->add(...$fields),
        );
    }

    private function extraClass(string $sectionKey): string
    {
        if (in_array($sectionKey, ['pricing'])) {
            return ' span-all';
        }
        return '';
    }

    private function renderField(array $field, FormBuilder $form): AbstractHtmlComponent
    {
        $id = 'product-' . $field['name'];

        // Determine input element
        $inputElement = match ($field['type'] ?? 'text') {
            'textarea' => $form->textarea()
                ->name($field['name'])
                ->id($id)
                ->placeholder($field['placeholder'] ?? '')
                ->class(self::TEXTAREA_CLASS),

            'select' => $this->renderSelectField($field, $form),

            'dropzone' => $this->renderDropzone($field, $form),

            default => $form->input($field['type'] ?? 'text')
                ->name($field['name'])
                ->id($id)
                ->placeholder($field['placeholder'] ?? '')
                ->class(self::INPUT_CLASS),
        };

        // Handle prefix/suffix icons for normal inputs/selects
        if (!empty($field['prefixIcon']) || !empty($field['suffixIcon'])) {
            $container = $form->tag('div')->class(self::INPUT_CONTAINER);

            if (!empty($field['prefixIcon'])) {
                $container->add(
                    $form->tag('span')->class(self::PREFIX_CLASS)->add(
                        $form->tag('svg')->class('icon')->ariaLabel('Prefix')->role('img')->add(
                            $form->tag('use')->href($this->mediaIconUrl($field['prefixIcon'])),
                        ),
                    ),
                );
            }

            $container->add($inputElement);

            if (!empty($field['suffixIcon'])) {
                $container->add(
                    $form->tag('span')->class(self::SUFFIX_CLASS)->add(
                        $form->tag('svg')->class('icon')->ariaLabel('Suffix')->role('img')->add(
                            $form->tag('use')->href($this->mediaIconUrl($field['suffixIcon'])),
                        ),
                    ),
                );
            }

            $inputElement = $container;
        }

        // Wrap in input box
        return $form->tag('div')->class(self::INPUT_BOX . $this->fieldExtraclass($field))->add(
            $inputElement,
            $form->label($field['label'] ?? ucfirst($field['name']))
                ->for($id)
                ->class(self::LABEL_CLASS),
            $form->tag('span')->class(self::HINT_CLASS)->content($field['hint'] ?? ''),
        );
    }

    private function renderSelectField(array $field, FormBuilder $form): AbstractHtmlComponent
    {
        $select = $form->select()
            ->id('product-' . $field['name'])
            ->class(self::INPUT_SELECT)
            ->name($field['name']);

        foreach ($field['options'] as $value => $label) {
            $select->add($form->option($value, $label)
                ->disabled($value === '' ? true : false)
                ->selected($value === '' ? true : false));
        }

        return $select;
    }

    private function fieldExtraclass(array $field): string
    {
        if (array_key_exists('class', $field)) {
            return ' ' . $field['class'];
        }
        return '';
    }

    private function renderDropzone(array $field, FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')->class(self::INPUT_BOX)->add(
            $form->tag('h6')->class('input-box__media-title')->content($field['label']),
            $form->tag('div')->class('input-box__media-upload')->add(
                ...$this->dropZoneMedia($field, $form),
            ),
        );
    }

    private function dropZoneMedia(array $field, FormBuilder $form): array
    {
        $items = [];

        $items[] = $this->mediaPreview($form);

        $items[] = $form->input('file')
            ->class('media-file')
            ->id('product-' . $field['name'])
            ->name($field['name'])
            ->accept($field['accept'] ?? '')
            ->multiple($field['multiple'] ?? false);

        $items[] = $this->mediaAvatar($field, $form);

        $items[] = $form->tag('span')->class('media-text')->content($field['dragText'] ?? '');

        $items[] = $form->label()
            ->for('product-' . $field['name'])
            ->class('btn', 'btn--secondary', 'btn--md-compact')
            ->add(
                $form->tag('span')->class('btn__label')->content($field['buttonLabel'] ?? 'Add File'),
            );

        return $items;
    }

    private function mediaAvatar(array $field, FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')->class('media-avatar')->add(
            $form->tag('svg')->class('icon', $field['icon'] ?? '')->ariaLabel("Media {$field['label']} Avatar")->role('img')->add(
                $form->tag('use')->href($this->mediaIconUrl($field['icon'] ?? '')),
            ),
        );
    }

    private function mediaPreview(FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')->class('media-preview empty')->add(
            $form->tag('div')->class('media-preview__item')->add(
                $form->tag('div')->class('media-preview__item--img-container')->add(
                    $form->tag('img')->src('#')->alt('Product Image Camera')->class('image'),
                ),
                $form->tag('div')->class('media-preview__item--icon-container')->add(
                    $form->tag('svg')->class('icon', 'success')->ariaLabel('Success')->role('img')->add(
                        $form->tag('use')->href($this->mediaIconUrl('icon-success')),
                    ),
                ),
                $form->button('button')->class('media-preview__item--remove')->add(
                    $form->tag('span')->class('btn__icon')->add(
                        $form->tag('svg')->class('icon', 'cancel')->ariaLabel('Cancel')->role('img')->add(
                            $form->tag('use')->href($this->mediaIconUrl('icon-cancel')),
                        ),
                    ),
                ),
            ),
        );
    }

    private function mediaIconUrl(string $icon): string
    {
        return '/public/assets/img/' . self::ICON_SPRITE . '#' . $icon;
    }
}
