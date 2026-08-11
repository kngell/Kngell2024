<?php

declare(strict_types=1);

final class ProductCardComponent implements StandAloneComponentInterface
{
    private const string DEFAULT_BUTTON_TEXT = 'View Details';
    private const string ADD_TO_CART_BUTTON_TEXT = 'Add to Cart';
    private const string CART_ICON = 'icon-cart';
    private const string LIKE_ICON = 'icon-like';

    private ?AddToCartConfig $addToCartConfig = null;

    public function __construct(
        private readonly IconBuilder $iconBuilder,
        private readonly HtmlBuilder $htmlBuilder,
        private readonly ButtonBuilder $buttonBuilder,
        private readonly ?AddToCartConfig $defaultCartConfig = null,
    ) {
        // Use default config if none provided
        $this->addToCartConfig = $defaultCartConfig ?? AddToCartConfig::default();
    }

    public function withCartConfig(AddToCartConfig $config): self
    {
        $this->addToCartConfig = $config;
        return $this;
    }

    #[Override]
    public function build(mixed $params = null): null|AbstractHtmlComponent
    {
        if ($params === null) {
            return null;
        }

        if ($params instanceof ProductCardResponse && $params->isDefault()) {
            return $this->buildEmptyCard();
        }

        if (is_array($params)) {
            return $this->buildFromArray($params);
        }

        if ($params instanceof ProductCardResponse) {
            return $this->buildFromResponse($params);
        }

        return null;
    }

    private function buildFromResponse(ProductCardResponse $product): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $image = $product->getImage();
        $imageSrc = $image['fallback']['src'] ?? '/public/assets/img/image 8.png';
        $imageAlt = $product->getImageAlt() ?? $product->getName() ?? 'Product';

        $currentPrice = $product->getCurrentPrice() ?? $product->getBasePrice() ?? '0';
        $originalPrice = $product->getOriginalPrice();
        $isOnSale = $product->isOnSale();
        $discountPercent = $product->getDiscountPercent();

        return $html->div()
            ->class('product-card')
            ->custom([
                'data-product-id' => $product->getId(),
                'data-product-public-id' => $product->getPublicId(),
                'data-product-name' => $product->getName(),
                'data-product-price' => $currentPrice,
                'data-product-slug' => $product->getSlug(),
            ])
            ->add(
                // Top section with badges and like button
                $this->buildTopSection(
                    isOnSale: $isOnSale,
                    discountPercent: $discountPercent,
                    isNew: $product->isNew(),
                    isInStock: $product->isInStock(),
                ),

                // Image container
                $this->buildImageSection(
                    imageSrc: $imageSrc,
                    imageAlt: $imageAlt,
                    slug: $product->getSlug() ?? 'product',
                ),

                // Info section (without actions)
                $this->buildInfoSection(
                    name: $product->getName() ?? 'Product',
                    slug: $product->getSlug() ?? 'product',
                    priceFormatted: $currentPrice,
                    originalPriceFormatted: $originalPrice,
                    rating: 5.2,//$product->getRating(),
                    reviewCount: $product->getReviewCount(),
                ),

                // Actions section (sibling of info)
                $this->buildActionsSection(
                    productId: $product->getId(),
                    name: $product->getName() ?? 'Product',
                    slug: $product->getSlug() ?? 'product',
                    priceFormatted: $currentPrice,
                    buttonText: $product->getButtonText() ?? self::DEFAULT_BUTTON_TEXT,
                    showAddToCart: true,
                ),
            );
    }

    private function buildFromArray(array $product): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $currentPrice = $product['price'] ?? '0';
        $originalPrice = $product['originalPrice'] ?? null;
        $isOnSale = $product['isOnSale'] ?? false;
        $discountPercent = $product['discountPercent'] ?? null;

        return $html->div()
            ->class('product-card')
            ->custom([
                'data-product-id' => $product['id'] ?? 0,
                'data-product-name' => $product['name'] ?? 'Product',
                'data-product-price' => $currentPrice,
                'data-product-slug' => $product['slug'] ?? 'product',
            ])
            ->add(
                $this->buildTopSection(
                    isOnSale: $isOnSale,
                    discountPercent: $discountPercent,
                    isNew: $product['isNew'] ?? false,
                    isInStock: $product['isInStock'] ?? true,
                ),
                $this->buildImageSection(
                    imageSrc: $product['imageSrc'] ?? '/public/assets/img/image 8.png',
                    imageAlt: $product['imageAlt'] ?? $product['name'] ?? 'Product',
                    slug: $product['slug'] ?? 'product',
                ),
                $this->buildInfoSection(
                    name: $product['name'] ?? 'Product',
                    slug: $product['slug'] ?? 'product',
                    priceFormatted: $currentPrice,
                    originalPriceFormatted: $originalPrice,
                    rating: $product['rating'] ?? null,
                    reviewCount: $product['reviewCount'] ?? 0,
                ),
                $this->buildActionsSection(
                    productId: $product['id'] ?? 0,
                    name: $product['name'] ?? 'Product',
                    slug: $product['slug'] ?? 'product',
                    priceFormatted: $currentPrice,
                    buttonText: $product['buttonText'] ?? self::DEFAULT_BUTTON_TEXT,
                    showAddToCart: true,
                ),
            );
    }

    private function buildTopSection(
        bool $isOnSale = false,
        ?int $discountPercent = null,
        bool $isNew = false,
        bool $isInStock = true,
    ): AbstractHtmlComponent {
        $html = $this->htmlBuilder;
        $topSection = $html->div()
            ->class('product-card__top');

        // Build badges container
        $badges = $html->div()->class('product-card__badges');
        $hasBadges = false;

        if ($isNew) {
            $badges->add(
                $html->tag('span')
                    ->class('badge', 'badge--new')
                    ->content('New'),
            );
            $hasBadges = true;
        }

        if ($isOnSale && $discountPercent !== null && $discountPercent > 0) {
            $badges->add(
                $html->tag('span')
                    ->class('badge', 'badge--sale')
                    ->content('-' . $discountPercent . '%'),
            );
            $hasBadges = true;
        }

        if (!$isInStock) {
            $badges->add(
                $html->tag('span')
                    ->class('badge', 'badge--out-of-stock')
                    ->content('Out of Stock'),
            );
            $hasBadges = true;
        }

        if ($hasBadges) {
            $topSection->add($badges);
        }

        $topSection->add(
            $html->tag('span')
                ->class('product-card__top--like')
                ->add(
                    $this->iconBuilder->createIcon(self::LIKE_ICON, 'LIKE', ['like']),
                ),
        );

        return $topSection;
    }

    private function buildImageSection(string $imageSrc, string $imageAlt, string $slug): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->div()
            ->class('product-card__image-container')
            ->add(
                $html->tag('a')
                    ->href("/product/{$slug}")
                    ->class('product-card__image-link')
                    ->add(
                        $html->tag('img')
                            ->src($imageSrc)
                            ->alt($imageAlt)
                            ->class('image')
                            ->loading('lazy'),
                    ),
            );
    }

    private function buildInfoSection(
        string $name,
        string $slug,
        string $priceFormatted,
        ?string $originalPriceFormatted = null,
        ?float $rating = null,
        int $reviewCount = 0,
    ): AbstractHtmlComponent {
        $html = $this->htmlBuilder;

        $infoSection = $html->div()
            ->class('product-card__info')
            ->add(
                // Product name
                $html->tag('a')
                    ->href("/product/{$slug}")
                    ->class('product-card__name-link')
                    ->add(
                        $html->tag('p')
                            ->class('description')
                            ->content($name),
                    ),
            );

        // Add rating if available
        if ($rating !== null && $rating > 0) {
            $infoSection->add(
                $this->buildRatingSection($rating, $reviewCount),
            );
        }

        // Price section
        $priceSection = $html->div()->class('product-card__prices');

        // Original price (if on sale)
        if ($originalPriceFormatted !== null && $originalPriceFormatted !== $priceFormatted) {
            $priceSection->add(
                $html->tag('span')
                    ->class('price--original')
                    ->content($originalPriceFormatted),
            );
        }

        // Current price
        $priceSection->add(
            $html->tag('h5')
                ->class('price')
                ->content($priceFormatted),
        );

        $infoSection->add($priceSection);

        return $infoSection;
    }

    private function buildActionsSection(
        null|int|string $productId,
        string $name,
        string $slug,
        string $priceFormatted,
        string $buttonText = self::DEFAULT_BUTTON_TEXT,
        bool $showAddToCart = true,
    ): AbstractHtmlComponent {
        $html = $this->htmlBuilder;
        $actionsSection = $html->div()->class('product-card__actions');

        if ($showAddToCart && $productId !== null) {
            $actionsSection->add(
                $this->buildAddToCartButton(
                    productId: $productId,
                    name: $name,
                    slug: $slug,
                    priceFormatted: $priceFormatted,
                ),
            );
        }

        // View Details button - also using ButtonBuilder
        $actionsSection->add(
            $this->buttonBuilder
                ->add(
                    type: 'link',
                    label: $buttonText,
                    buttonSize: 'md',
                    buttonStyle: 'view-details',
                    attributes: [
                        'href' => "/product/{$slug}",
                    ],
                )
                ->build(),
        );

        return $actionsSection;
    }

    private function buildAddToCartButton(
        int|string $productId,
        string $name,
        string $slug,
        string $priceFormatted,
    ): AbstractHtmlComponent {
        $config = $this->addToCartConfig;
        $hiddenFields = [
            $config->itemIdField => (string) $productId,
            'quantity' => '1',
        ];

        if ($config->redirectUrl !== null) {
            $hiddenFields['redirect'] = $config->redirectUrl;
        }

        $hiddenFields = array_merge($hiddenFields, $config->additionalHiddenFields);

        return $this->buttonBuilder
            ->add(
                type: 'submit',
                label: self::ADD_TO_CART_BUTTON_TEXT,
                buttonSize: 'md',
                ariaLabel: 'Add to cart',
                buttonStyle: 'add-to-cart',
                icon: self::CART_ICON,
                iconPosition: 'left',
                attributes: [
                    'data-product-id' => $productId,
                    'data-product-name' => $name,
                    'data-product-price' => $priceFormatted,
                    'data-product-slug' => $slug,
                ],
                buttonClass: ['add-to-cart-btn'],
            )
            ->withForm(
                action: $config->action,
                method: $config->method,
                includeCsrf: $config->includeCsrf,
                hiddenFields: $hiddenFields,
                classes: $config->formClasses,
                attributes: array_merge(
                    [
                        'data-product-id' => $productId,
                        'data-product-name' => $name,
                        'data-product-slug' => $slug,
                    ],
                    $config->formAttributes,
                ),
            )
            ->build();
    }

    private function buildRatingSection(float $rating, int $reviewCount): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->div()
            ->class('product-card__rating')
            ->add(
                $html->div()
                    ->class('stars')
                    ->content($this->renderStars($rating)),
                $html->tag('span')
                    ->class('review-count')
                    ->content("({$reviewCount})"),
            );
    }

    private function renderStars(float $rating): string
    {
        $fullStars = floor($rating);
        $halfStar = ($rating - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

        $stars = '';

        for ($i = 0; $i < $fullStars; $i++) {
            $stars .= '★';
        }

        if ($halfStar) {
            $stars .= '½';
        }

        for ($i = 0; $i < $emptyStars; $i++) {
            $stars .= '☆';
        }

        return $stars;
    }

    private function buildEmptyCard(): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->div()
            ->class('product-card', 'product-card--empty')
            ->add(
                $html->div()
                    ->class('product-card__empty-content')
                    ->add(
                        $html->tag('p')
                            ->class('empty-message')
                            ->content('Product not available'),
                    ),
            );
    }
}