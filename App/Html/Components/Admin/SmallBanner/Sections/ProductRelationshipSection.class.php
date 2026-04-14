<?php

declare(strict_types=1);

class ProductRelationshipSection extends BaseFieldSection
{
    

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $iconBuilder,
        private readonly FormSectionHeader $header,
    ) {
        parent::__construct($builder, $iconBuilder);
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $this->formValues = $formValues;

        $hasValue = !empty($formValues['product_id'] ?? null);

        return [
            [
                'key' => 'product',
                'name' => 'product_id',
                'map' => 'product.id',
                'type' => 'custom-select',
                'options' => [],
                'label' => 'Product',
                'placeholder' => 'Search Product by name or SKU...',
                'searchPlaceholder' => 'Search products...',
                'rightIcon' => ['icon' => 'icon-arrow-down', 'aria' => 'Dropdown arrow'],
                'searchable' => true,
                'hint' => 'Please select an option',
                'inputLayout' => 'custom-select',
                'has-value' => $hasValue ? 'true' : 'false',
                'footer' => [
                    'error' => 'Please select a product',
                ],
            ],
            [
                'name' => 'product_name',
                'map' => 'product.name',
            ],
            [
                'name' => 'main_image',
                'map' => 'product.mainImage',
            ],
            [
                'name' => 'slug',
                'map' => 'product.slug',
            ],
            [
                'name' => 'short_description',
                'map' => 'product.shortDescription',
            ],
            [
                'name' => 'description',
                'map' => 'product.description',
            ],
        ];
    }

    public function getKey(): string
    {
        return 'product-relationship';
    }

    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): ?AbstractHtmlComponent
    {
        $sectionClass = 'product-relationship';

        return $form->tag('div')
            ->class($sectionClass)
            ->add(
                $this->header->getComponent(
                    title: 'Product Relationship',
                    wrapperClass: $sectionClass . '__header',
                    icon: 'icon-link',
                    showRequired: false,
                ),
                $this->buildBody($sectionClass, $form, $fields),
            );
    }

    private function buildBody(string $sectionClass, HtmlBuilder $form, array $fields): AbstractHtmlComponent
    {
        $productId = $this->formValues['product_id'] ?? null;
        $productData = $this->extractProductDataFromFormValues();

        // Add has-value class to the first field (custom select) wrapper
        if (!empty($fields) && isset($fields[0]) && is_array($fields[0])) {
            $hasValue = !empty($productId);
            if ($hasValue) {
                $fields[0]['wrapperClass'] = 'has-value';
            }
        }

        return $form->tag('div')
            ->class($sectionClass . '__body')
            ->add(
                $form->tag('div')->class('product-row')->add(...$fields),
                $this->buildProductCard($form, $productData, $productId),
            );
    }

    private function extractProductDataFromFormValues(): ?array
    {
        $productId = $this->formValues['product_id'] ?? null;

        if (!$productId) {
            return null;
        }

        $productData = [];

        $productName = $this->formValues['custom_title'] ?? $this->formValues['product_name'] ?? null;
        if ($productName) {
            $productData['name'] = $productName;
            $productData['title'] = $productName;
        }

        $productImage = $this->formValues['main_image'] ?? null;
        if ($productImage) {
            $productData['main_image'] = $productImage;
            $productData['image'] = $productImage;
            $productData['image_url'] = $productImage;
        }

        $description = $this->formValues['custom_description'] ??
                      $this->formValues['short_description'] ??
                      $this->formValues['description'] ??
                      null;
        if ($description) {
            $productData['short_description'] = $description;
            $productData['description'] = $description;
        }

        $sku = $this->formValues['slug'] ?? 'PROD-' . $productId;
        if ($sku) {
            $productData['sku'] = $sku;
            $productData['product_sku'] = $sku;
        }

        return !empty($productData) ? $productData : null;
    }

    private function buildProductCard(HtmlBuilder $form, ?array $productData, ?string $productId): AbstractHtmlComponent
    {
        $hasData = $productData !== null && !empty($productData);
        $cardClass = $hasData ? ['product-card', 'is-visible'] : ['product-card', 'is-empty'];

        return $form->tag('div')
            ->class(...$cardClass)
            ->attribute('data-product-id', $productId ?? '')
            ->add(
                $this->buildCardLeft($form, $productData),
                $this->buildCardRight($form),
            );
    }

    private function buildCardLeft(HtmlBuilder $form, ?array $productData): AbstractHtmlComponent
    {
        $left = $form->tag('div')->class('product-card__left');

        if (!$productData || empty($productData)) {
            return $left->add(
                ...$this->buildEmptyState($form),
            );
        }

        return $left->add(
            $this->buildImageContainer($form, $productData),
            $this->buildProductInfo($form, $productData),
        );
    }

    /**
     * @param HtmlBuilder $form
     *
     * @return AbstractHtmlComponent[]
     */
    private function buildEmptyState(HtmlBuilder $form): array
    {
        $cardElements = [];
        $cardElements[] = $form->tag('div')->class('img-container')->add(
            $this->iconBuilder->createIcon($form, 'icon-image', 'No Product Selected', ['image', 'placeholder']),
        );
        $cardElements[] = $form->tag('div')->class('product-info')->add(
            $form->tag('h6')->class('product-info__title')->content('No product selected'),
            $form->tag('span')->class('product-info__sku')->content('Select a product to view details'),
        );

        return $cardElements;
    }

    private function buildImageContainer(HtmlBuilder $form, array $productData): AbstractHtmlComponent
    {
        // Try multiple possible image field names
        $imageUrl = $productData['main_image'] ??
                   $productData['image'] ??
                   $productData['image_url'] ??
                   null;

        $imageHtml = $this->isValidImageUrl($imageUrl)
            ? $form->tag('img')
                ->class('image')
                ->src($imageUrl)
                ->alt($productData['name'] ?? $productData['title'] ?? 'Product Image')
            : $this->iconBuilder->createIcon($form, 'icon-image', 'No image available', ['image', 'fallback']);

        return $form->tag('div')->class('img-container')->add($imageHtml);
    }

    private function buildProductInfo(HtmlBuilder $form, array $productData): AbstractHtmlComponent
    {
        $title = $productData['name'] ?? $productData['title'] ?? 'Product Name';
        $sku = $productData['sku'] ?? $productData['product_sku'] ?? 'SKU not available';
        $shortDescription = $productData['short_description'] ?? $productData['description'] ?? 'No description available';

        return $form->tag('div')->class('product-info')->add(
            $form->tag('h6')->class('product-info__title')->content($this->escape($title)),
            $form->tag('span')->class('product-info__sku')->content($this->escape($sku)),
            $form->tag('span')->class('product-info__short-description')->content($this->escape($shortDescription)),
        );
    }

    private function buildCardRight(HtmlBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')
            ->class('product-card__right')
            ->add(
                $form->button('button')
                    ->class('btn btn--icon-only')
                    ->attribute('data-remove-product', 'true')
                    ->attribute('type', 'button')
                    ->add(
                        $this->iconBuilder->createIcon($form, 'icon-close', 'Remove product', ['close']),
                    ),
            );
    }

    private function isValidImageUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false ||
               str_starts_with($url, '/') ||
               str_starts_with($url, './') ||
               str_starts_with($url, '../') ||
               str_starts_with($url, 'data:image');
    }
}