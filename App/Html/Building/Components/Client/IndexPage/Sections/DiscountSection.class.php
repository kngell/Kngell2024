<?php

declare(strict_types=1);

class DiscountSection extends AbstractBaseHtmlSection
{
    private const int DEFAULT_LIMIT = 8;

    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private readonly ProductService $productService,
        private readonly ?string $pageTarget = null,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    #[Override]
    public function getKey(): string
    {
        return IndexPageSection::DISCOUNT->value;
    }

    #[Override]
    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        try {
            $products = $this->productService->getDiscountedProducts(self::DEFAULT_LIMIT, $this->pageTarget);

            if (empty($products)) {
                return $this->renderEmptyState();
            }

            return $this->renderDiscountSection($products);
        } catch (Throwable $e) {
            error_log("Discount section error: {$e->getMessage()}");
            return $this->renderErrorState();
        }
    }

    private function renderDiscountSection(array $products): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->div()
            ->class('container', 'discount')
            ->add(
                $this->renderHeader(),
                $this->renderProductsGrid($products),
            );
    }

    private function renderHeader(): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->tag('h2')
            ->class('discount__title')
            ->content('Discounts up to -50%');
    }

    private function renderProductsGrid(array $products): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $productCards = [];

        foreach ($products as $product) {
            $card = $this->renderProductCard($product);
            if ($card !== null) {
                $productCards[] = $card;
            }
        }

        if (empty($productCards)) {
            return $html->tag('div')->class('discount__row empty');
        }

        return $html->div()
            ->class('discount__row')
            ->add(...$productCards);
    }

    private function renderProductCard(ProductCardResponse $response): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $imageData = $response->getImage();
        $imageUrl = $imageData['fallback']['src'] ?? null;

        if (empty($imageUrl)) {
            return null;
        }

        $name = $response->getName();
        $description = $response->getShortDescription() ?? $name;
        $currentPrice = $response->getCurrentPrice();
        $originalPrice = $response->getOriginalPrice();
        $discountPercent = $response->getDiscountPercent();
        $productId = $response->getId();

        return $html->div()
            ->class('product-card')
            ->attribute('data-product-id', $productId)
            ->add(
                $this->renderProductTop($discountPercent),
                $this->renderProductImage($imageUrl, $name),
                $this->renderProductInfo($description, $currentPrice, $originalPrice, $productId),
            );
    }

    private function renderProductTop(?int $discountPercent): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $topDiv = $html->div()->class('product-card__top');

        $topDiv->add(
            $html->tag('span')
                ->class('product-card__top--like')
                ->add(
                    $this->iconBuilder->createIcon('icon-like', 'Like', ['like']),
                ),
        );

        if ($discountPercent !== null && $discountPercent > 0) {
            $topDiv->add(
                $html->tag('span')
                    ->class('discount-badge')
                    ->content("-{$discountPercent}%"),
            );
        }

        return $topDiv;
    }

    private function renderProductImage(string $imageUrl, string $alt): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->div()
            ->class('product-card__image-container')
            ->add(
                $html->tag('img')
                    ->src($imageUrl)
                    ->alt($alt)
                    ->class('image')
                    ->attribute('loading', 'lazy'),
            );
    }

    private function renderProductInfo(
        string $description,
        ?string $currentPrice,
        ?string $originalPrice,
        int $productId,
    ): AbstractHtmlComponent {
        $html = $this->htmlBuilder;

        $infoDiv = $html->div()->class('product-card__info');
        $textContainer = $html->div()->class('product-card__info--text');

        $textContainer->add(
            $html->tag('p')
                ->class('description')
                ->content($description),
        );

        $priceContainer = $html->div()->class('price-container');

        if ($originalPrice !== null && $currentPrice !== null && $originalPrice !== $currentPrice) {
            $priceContainer->add(
                $html->tag('span')
                    ->class('old-price')
                    ->content($originalPrice),
            );
        }

        $priceContainer->add(
            $html->tag('h5')
                ->class('price')
                ->content($currentPrice ?? '$0.00'),
        );

        $textContainer->add($priceContainer);
        $infoDiv->add($textContainer);

        $infoDiv->add(
            $html->button('button')
                ->class('btn', 'btn-dark-small')
                ->attribute('data-product-id', $productId)
                ->content('Buy Now'),
        );

        return $infoDiv;
    }

    private function renderEmptyState(): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->tag('div')
            ->class('discount-empty-state')
            ->add(
                $this->iconBuilder->createIcon(
                    'icon-discount-empty',
                    'No discounts available',
                    ['empty-state-icon'],
                ),
                $html->tag('p')
                    ->class('discount-empty-state__message')
                    ->content('No discounted products available at the moment.'),
            );
    }

    private function renderErrorState(): AbstractHtmlComponent
    {
        return $this->htmlBuilder->tag('div')
            ->class('discount-error')
            ->attribute('data-error', 'true');
    }
}