<?php

declare(strict_types=1);

class HeroSection extends AbstractBaseHtmlSection
{
    private const BlockType SECTION = BlockType::HERO;

    private SingleContentBlockService $service;

    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private ContentBlockServiceFactory $factoryService,
        private readonly ButtonBuilder $buttonBuilder,
        private ?string $pageTarget = null,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
        $this->service = $this->factoryService->create(self::SECTION);
    }

    public function getKey(): string
    {
        return IndexPageSection::HERO->value;
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $response = $this->service->getForPage($this->pageTarget);
        if (!$response instanceof ContentBlockResponse) {
            return $this->htmlBuilder->tag('div');
        }

        $html = $this->htmlBuilder;
        return $html->tag('div')->class('container', 'hero')->add(
            $html->tag('div')->class('hero__content')->add(
                $this->heroContent($response),
                $this->heroCta($response),
            ),
            $this->heroImage($response),
        );
    }

    private function heroContent(?ContentBlockResponse $hero = null): ?AbstractHtmlComponent
    {
        if ($hero === null) {
            return null;
        }
        $html = $this->htmlBuilder;
        return $html->tag('div')->class('hero__content-text')->add(
            ...$this->heroContentText($hero),
        );
    }

    private function heroImage(ContentBlockResponse $response): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $alt = $response->getImageAlt() ?? $response->getEntity()->getTitle() ?? '';
        $src = $response->getImage()['src'];

        return $html->tag('div')->class('hero__img-container', 'animate-fade-in-right', 'animate-delay-200')->add(
            $html->tag('img')
                ->src($src)
                ->alt($alt)
                ->class('image'),
        );
    }

    private function heroCta(?ContentBlockResponse $hero = null): ?AbstractHtmlComponent
    {
        if ($hero === null) {
            return null;
        }
        $html = $this->htmlBuilder;
        $ctaText = $hero->getCtaText() ?? '';

        return $this->buttonBuilder
                    ->add(
                        type: 'button',
                        label: $ctaText,
                        buttonSize: 'hero-btn-size',
                        ariaLabel: 'Shop Now',
                        buttonStyle: 'outline-white',
                        buttonClass: [],
                    )
                    ->build();
        return $html->button('button')
            ->class('hero__content-cta', 'btn btn-outline', 'btn-outline-white')
            ->content($ctaText);
    }

    private function heroContentText(ContentBlockResponse $hero): array
    {
        $html = $this->htmlBuilder;
        $introduction = $hero->getTitleIntro() ?? '';
        $subTitle = $hero->getEntity()->getSubtitle() ?? '';
        $title = $hero->getEntity()->getTitle() ?? '';
        $specializedTitle = $hero->getSpanTitle();

        return [
            $html->tag('div')->class('hero__content-text--titles')->add(
                $html->tag('p')->class('title-intro')->content($introduction),
                $html->tag('h1')->class('title-main', 'animate-fade-in-up', 'animate-delay-200')->content($title . '&nbsp;')->add(
                    $html->tag('span')->class('title-sub')->content($specializedTitle),
                ),
            ),
            $html->tag('p')->class('hero__content-text--body', 'animate-fade-in-up', 'animate-delay-300')
                ->content($subTitle),
        ];
    }
}