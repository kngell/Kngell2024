<?php

declare(strict_types=1);

class HeroSection extends AbstractBaseHtmlSection
{
    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private HeroService $heroService,
        private ?string $pageTarget = null,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    public function getKey(): string
    {
        return IndexPageSection::HERO->value;
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $response = $this->heroService->getForPage($this->pageTarget);
        if (!$response instanceof HeroResponse) {
            return $this->htmlBuilder->tag('div');
        }

        $html = $this->htmlBuilder;
        return $html->tag('div')->class('container', 'hero')->add(
            $html->tag('div')->class('hero__content')->add(
                $this->heroContent($response->getHero()),
                $this->heroCta($response->getHero()),
            ),
            $this->heroImage($response, $response->getHero()),
        );
    }

    private function heroContent(?Hero $hero = null): ?AbstractHtmlComponent
    {
        if ($hero === null) {
            return null;
        }
        $html = $this->htmlBuilder;
        return $html->tag('div')->class('hero__content-text')->add(
            ...$this->heroContentText($hero),
        );
    }

    private function heroImage(EntityResponseInterface $response, ?Hero $hero = null): ?AbstractHtmlComponent
    {
        if ($hero === null) {
            return null;
        }
        $html = $this->htmlBuilder;

        // Escape all attributes
        $alt = $this->escape($hero->getImageAlt() ?? $hero->getTitle() ?? '');
        $src = $this->escape($response->getImage()['src']);

        return $html->tag('div')->class('hero__img-container', 'animate-fade-in-right', 'animate-delay-200')->add(
            $html->tag('img')
                ->src($src)
                ->alt($alt)
                ->class('image'),
        );
    }

    private function heroCta(?Hero $hero = null): ?AbstractHtmlComponent
    {
        if ($hero === null) {
            return null;
        }
        $html = $this->htmlBuilder;
        $ctaText = $this->escape($hero->getCtaText() ?? '');
        return $html->button('button')
            ->class('hero__content-cta', 'btn btn-outline', 'btn-outline-white')
            ->content($ctaText);
    }

    private function heroContentText(Hero $hero): array
    {
        $html = $this->htmlBuilder;
        $introduction = $this->escape($hero->getIntroduction() ?? '');
        $subTitle = $this->escape($hero->getSubtitle() ?? '');
        $title = $this->escape($hero->getTitle() ?? '');
        $specializedTitle = $this->escape($hero->getSpecializedTitle());

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
