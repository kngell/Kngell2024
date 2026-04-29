<?php

declare(strict_types=1);

class HomeProductSection extends AbstractBaseHtmlSection
{
    private const array TAB_CONFIG = [
        'tab-new-arrival' => [
            'title' => 'New Arrival',
            'state' => 'default',
            'sections' => [],
            'contentClass' => ['products-grid', 'new-arraival'],
            'class' => ['products-tabs__item', 'selected'],
        ],
        'tab-bestseller' => [
            'title' => 'Bestseller',
            'state' => null,
            'sections' => [],
            'contentClass' => ['products-grid', 'bestseller'],
            'class' => ['products-tabs__item'],
        ],
        'tab-featured-product' => [
            'title' => 'Featured Products',
            'state' => null,
            'sections' => [],
            'contentClass' => ['products-grid', 'featured'],
            'class' => ['products-tabs__item'],
        ],
    ];

    private string $pageTarget = 'index';

    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private ProductService $service,
        private HtmlSectionPresentationService $presenter,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $response = $this->service->getForPage($this->pageTarget);
        $html = $this->htmlBuilder;

        // Create tabs navigation
        $tabs = (new FormTabs($html))
            ->setTabClass(['products-tabs'])
            ->setTag('nav');

        $allTabContents = [];

        foreach (self::TAB_CONFIG as $tabId => $config) {
            $tabs->addTab($config['title'], $tabId, $config['state'], $config['class']);

            $tabContent = $html->tag('div')
                ->id($tabId . '-content')
                ->class('tab-content', ...$config['contentClass']);
            if ($config['state'] === 'default') {
                $tabContent->add(...$this->productCard($response));
            }

            $allTabContents[] = $tabContent;
        }

        $contentLayout = array_merge($tabs->getComponents(), $allTabContents);
        return $html->div()
            ->class('container', 'products')
            ->add(
                ...$contentLayout,
            );
    }

    public function getKey(): string
    {
        return IndexPageSection::PRODUCT->value;
    }

    /**
     * @return AbstractHtmlComponent[]
     */
    private function productCard(array $response = []): array
    {
        if (empty($response)) {
            return [];
        }

        $html = $this->htmlBuilder;
        $cards = [];

        /** @var ProductCardResponse $cardResponse */
        foreach ($response as $cardResponse) {
            $image = $cardResponse->getImage();
            $imageSrc = $image['fallback']['src'] ?? '/assets/images/default-product.jpg';
            $imageAlt = $cardResponse->getImageAlt() ?? $cardResponse->getName();

            // Format the price using the presentation service
            $formattedPrice = '';
            try {
                $formattedPrice = $this->presenter->showRelated(
                    $cardResponse->getProduct(),
                    'product_regional_price',
                    'base_price',
                );
            } catch (Throwable $e) {
                $formattedPrice = '';
            }

            $card = $html->div()
                ->class('product-card')
                ->add(
                    $html->div()
                        ->class('product-card__top')
                        ->add(
                            $html->tag('span')
                                ->class('product-card__top--like')
                                ->add(
                                    $this->iconBuilder->createIcon('icon-like', 'LIKE', ['like']),
                                ),
                        ),
                    $html->div()
                        ->class('product-card__image-container')
                        ->add(
                            $html->tag('img')
                                ->src($imageSrc)
                                ->alt($imageAlt)
                                ->class('image'),
                        ),
                    $html->div()
                        ->class('product-card__info')
                        ->add(
                            $html->div()
                                ->class('product-card__info--text')
                                ->add(
                                    $html->tag('p')
                                        ->class('description')
                                        ->content($this->escape($cardResponse->getName())),
                                    $html->tag('h5')
                                        ->class('price')
                                        ->content($this->escape($formattedPrice)),
                                ),
                            $html->button('button')->class('btn', 'btn-dark-small')->content($this->escape($cardResponse->getButtonText() ?? 'Buy Now')),
                        ),
                );

            $cards[] = $card;
        }

        return $cards;
    }
}