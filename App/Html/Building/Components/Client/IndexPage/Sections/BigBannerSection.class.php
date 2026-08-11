<?php

declare(strict_types=1);

class BigBannerSection extends AbstractBaseHtmlSection
{
    private const BlockType SECTION = BlockType::BIG_BANNER;

    private CollectionContentBlockService $service;

    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private ContentBlockServiceFactory $factoryService,
        private ?string $pageTarget = null,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
        $this->service = $this->factoryService->create(self::SECTION);
    }

    #[Override]
    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $widthOverrides = $this->getWidthOverrides();
        $organized = $this->service
            ->setWidths($widthOverrides)
            ->getOrganizedForPage($this->pageTarget);

        if (empty($organized)) {
            return $this->htmlBuilder->tag('div');
        }

        return $this->htmlBuilder->tag('div')
            ->class('banner')
            ->add($this->bannerGrid($organized));
    }

    #[Override]
    public function getKey(): string
    {
        return IndexPageSection::BIG_BANNER->value;
    }

    /**
     * Collect width overrides from positions that need them.
     */
    private function getWidthOverrides(): array
    {
        $overrides = [];
        foreach (BigBannerPosition::cases() as $position) {
            $config = $position->getRenderingConfig();
            if (isset($config['width_override'])) {
                $overrides[$position->value] = $config['width_override'];
            }
        }
        return $overrides;
    }

    /**
     * Render the banner grid with all positions.
     */
    private function bannerGrid(array $organized): AbstractHtmlComponent
    {
        $bannerGrid = $this->htmlBuilder->div()->class('big-card-grid');

        $bigCards = [];
        foreach (BigBannerPosition::cases() as $position) {
            if (array_key_exists($position->value, $organized)) {
                $bigCards[] = $this->getBigCards($organized, $position);
            }
        }

        return $bannerGrid->add(...$bigCards);
    }

    /**
     * Render individual banner card for a position.
     */
    private function getBigCards(array $organized, BigBannerPosition $position): AbstractHtmlComponent
    {
        $config = $position->getRenderingConfig();
        $response = $organized[$position->value];
        $entity = $response->getEntity();

        return $this->htmlBuilder->div()
            ->class(...$config['card_class'])
            ->add(
                $this->bigCardContent($response, $entity),
                $this->processImages($response->getImage(), $position),
            );
    }

    /**
     * Render card content (title, description, button).
     */
    private function bigCardContent(ContentBlockCollectionResponse $response, ContentBlockShow $entity): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->div()->class('big-card__content')->add(
            $html->tag('h4')->class('big-card__content--title')->content($response->getTitle()),
            $html->tag('p')->class('big-card__content--description')->content($response->getSubtitle()),
            $html->button('button')
                ->class('btn', 'btn-outline', 'btn-outline-dark')
                ->content($response->getButtonText()),
        );
    }

    /**
     * Process images based on position configuration.
     */
    private function processImages(array $images, BigBannerPosition $position): AbstractHtmlComponent
    {
        $config = $position->getRenderingConfig();
        $processor = $config['image_processor'];

        return $this->{$processor}($images, $position);
    }

    /**
     * Process single image (or multiple images stacked vertically/horizontally).
     */
    private function processSingleImages(array $images, BigBannerPosition $position): AbstractHtmlComponent
    {
        $config = $position->getRenderingConfig();
        $container = $this->htmlBuilder->div()->class(...$config['image_container_class']);

        $imgComponents = array_map(
            fn ($image) => $this->htmlBuilder->tag('img')
                ->class('image')
                ->src($image['src'])
                ->alt($image['alt']),
            $images,
        );

        return $container->add(...$imgComponents);
    }

    /**
     * Process multiple images in a special layout (LEFT position).
     */
    private function processMultipleImages(array $images, BigBannerPosition $position): AbstractHtmlComponent
    {
        $config = $position->getRenderingConfig();
        $container = $this->htmlBuilder->div()->class(...$config['image_container_class']);

        $multipleContainer = $this->htmlBuilder->div()
            ->class('big-card__img-container', 'big-card-multiple__img-container');

        $imgComponents = [];
        foreach ($images as $index => $image) {
            $imgClass = $index === 0 ? 'img-multiples__left' : 'img-multiples__right';
            $imgComponents[] = $this->htmlBuilder->div()
                ->class($imgClass)
                ->add(
                    $this->htmlBuilder->tag('img')
                        ->class('image')
                        ->src($image['src'])
                        ->alt($image['alt']),
                );
        }

        $multipleContainer->add(
            $this->htmlBuilder->div()->class('img-multiples')->add(...$imgComponents),
        );

        return $container->add($multipleContainer);
    }
}