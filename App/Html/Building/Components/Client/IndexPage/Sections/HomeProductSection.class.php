<?php

declare(strict_types=1);

class HomeProductSection extends AbstractBaseHtmlSection
{
    private string $pageTarget = 'index';

    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private ProductService $service,
        private readonly ButtonBuilder $buttonBuilder,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $response = $this->service->getForPage($this->pageTarget);
        $html = $this->htmlBuilder;

        $tabConfig = TabConfig::create()
            ->setTabContainerClass(['products-tabs'])
            ->setContentContainerClass(['tab-content-container'])
            ->addTab(
                TabItem::create('tab-new-arrival', 'New Arrival')
                    ->setState('active')
                    ->setContentClass(['products-grid', 'new-arraival'])
                    ->setPriority(1)
                    ->setAttributes(['class' => ['products-tabs__item', 'selected']]),
            )
            ->addTab(
                TabItem::create('tab-bestseller', 'Bestseller')
                    ->setContentClass(['products-grid', 'bestseller'])
                    ->setPriority(2)
                    ->setAttributes(['class' => ['products-tabs__item']]),
            )
            ->addTab(
                TabItem::create('tab-featured-product', 'Featured Products')
                    ->setContentClass(['products-grid', 'featured'])
                    ->setPriority(3)
                    ->setAttributes(['class' => ['products-tabs__item']]),
            );

        $productCards = $this->buildProductCards($response);

        $customContent = $html->div()
            ->class('products-grid-wrapper')
            ->add(...$productCards);

        return TabComponent::create($html)
            ->setTabConfig($tabConfig)
            ->setCustomContent($customContent)
            ->setContainerClass(['container', 'products'])
            ->setContainerTag('section')
            ->build();
    }

    public function getKey(): string
    {
        return IndexPageSection::PRODUCT->value;
    }

    /**
     * @param array<ProductCardResponse> $products
     *
     * @return AbstractHtmlComponent[]
     */
    private function buildProductCards(array $products): array
    {
        if (empty($products)) {
            return [];
        }

        $cards = [];
        $addToCartConfig = AddToCartConfig::create();
        $productCardComponent = new ProductCardComponent(
            $this->iconBuilder,
            $this->htmlBuilder,
            $this->buttonBuilder,
            $addToCartConfig,
        );

        foreach ($products as $product) {
            if ($product instanceof ProductCardResponse && !$product->isDefault()) {
                $card = $productCardComponent->build($product);
                if ($card !== null) {
                    $cards[] = $card;
                }
            }
        }

        return $cards;
    }
}