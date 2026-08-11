<?php

declare(strict_types=1);

class SmallBannerSection extends AbstractBaseHtmlSection
{
    private const BlockType SECTION = BlockType::SMALL_BANNER;

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

    public function getKey(): string
    {
        return IndexPageSection::SMALL_BANNER->value;
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $widthOverride = [
            SmallBannerPosition::RIGHT->value => ['desktop' => 800],
        ];
        $organized = $this->service
            ->setWidths($widthOverride)
            ->getOrganizedForPage($this->pageTarget);

        if (empty($organized)) {
            return $this->htmlBuilder->tag('div');
        }

        $html = $this->htmlBuilder;

        return $html->tag('div')
            ->class('banner')
            ->add(
                $this->buildLeftSection($organized),
                $this->buildRightSection($organized),
            );
    }

    /**
     * @param array $organized Organized banners from service
     */
    private function buildLeftSection(array $organized): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $leftDiv = $html->tag('div')->class('banner-left');
        $leftDiv->add(
            $this->getLeftWideBanner(SmallBannerPosition::LEFT_WIDE, $organized),
            $html->tag('div')->class('banner-left__squares')->add(
                ...$this->getBannerSquares($organized),
            ),
        );
        return $leftDiv;
    }

    private function buildRightSection(array $organized): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $enumClass = SmallBannerPosition::RIGHT;
        $response = $organized[$enumClass->value] ?? null;
        if ($response === null) {
            return null;
        }
        return $html->tag('div')->class('banner-right')->add(
            $this->getRightBannerContent($response),
            $this->getRightBannerImage($response),
        );
    }

    private function getRightBannerContent(ContentBlockCollectionResponse $response): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $title = $response->getTitle();
        $spanTitle = $response->getTitleSpan();
        $description = $response->getSubtitle();
        $buttonText = $response->getButtonText();
        return $html->tag('div')->class('banner-right__content')->add(
            $html->tag('div')->class('text-container')->add(
                $html->tag('h2')->class('text-container__heading')->content($title ?? '')->add(
                    $html->tag('span')->content($spanTitle ?? ''),
                ),
                $html->tag('p')->class('text-container__body')->content($description ?? ''),
            ),
            $html->tag('button')->class('btn btn-outline btn-outline-dark')->content($buttonText ?? ''),
        );
    }

    private function getRightBannerImage(ContentBlockCollectionResponse $response): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $image = $response->getImage();

        if (empty($image)) {
            return null;
        }
        return $html->tag('div')->class('banner-right__img-container')->add(
            $html->tag('img')->src($image['src'] ?? '#')->alt($response->getImageAlt() ?? 'Small Banner')->class('image'),
        );
    }

    private function getLeftWideBanner(SmallBannerPosition $enumClass, array $organized): ?AbstractHtmlComponent
    {
        /** @var null|ContentBlockCollectionResponse */
        $response = $organized[$enumClass->value] ?? null;

        $html = $this->htmlBuilder;

        if ($response === null) {
            return null;
        }
        $image = $response->getImage();
        return $html->tag('div')->class(...$enumClass->getClasses())->add(
            $html->tag('div')->class('image-container')->add(
                $html->tag('img')
                         ->src($image['src'])
                         ->alt($response->getImageAlt() ?? 'Banner')
                         ->class('image-container--img'),
            ),
            $html->tag('div')->class('text-container')->add(
                $html->tag('h2')->class('text-container__heading')->content($response->getTitle() ?? ''),
                $html->tag('p')->class('text-container__body')->content($response->getSubtitle()),
            ),
        );
    }

    /**
     * @param array $organized
     *
     * @return AbstractHtmlComponent[]
     */
    private function getBannerSquares(array $organized): array
    {
        $bannerSquares = [];
        $squareTypes = [
            SmallBannerPosition::LEFT_SQUARE_LIGHT,
            SmallBannerPosition::LEFT_SQUARE_DARK,
            // Add more easily without changing logic
        ];

        foreach ($squareTypes as $enum) {
            $response = $organized[$enum->value] ?? null;
            if ($response !== null) {
                $bannerSquares[] = $this->getBannerSquare([
                    $enum->getClasses(),
                    $response,
                ]);
            }
        }

        return $bannerSquares;
    }

    private function getBannerSquare(array $config): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $response = $config[1];

        return $html->tag('div')->class(...$config[0])->add(
            $this->buildTextContainer($response),
            $this->buildImageContainer($response),
        );
    }

    private function buildTextContainer(ContentBlockCollectionResponse $response): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->tag('div')->class('banner-square__text-container')->add(
            $html->tag('h2')->class('banner-square__text-container--heading')->add(
                $html->text($response->getTitle() ?? ''),
                $html->tag('span')->content($response->getTitleSpan()),
            ),
            $html->tag('p')->class('banner-square__text-container--body')->content(
                $response->getSubtitle(),
            ),
        );
    }

    private function buildImageContainer(ContentBlockCollectionResponse $response): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $image = $response->getImage();

        return $html->tag('div')->class('banner-square__img-container')->add(
            $html->tag('img')
                ->class('img')
                ->alt($response->getTitle())
                ->src($image['src'] ?? '#'),
        );
    }
}