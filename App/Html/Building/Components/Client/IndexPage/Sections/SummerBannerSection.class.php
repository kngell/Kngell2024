<?php

declare(strict_types=1);

class SummerBannerSection extends AbstractBaseHtmlSection
{
    private const BlockType SECTION = BlockType::SUMMER_BANNER;

    private SingleContentBlockService $service;

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
        $response = $this->service->getForPage($this->pageTarget);
        if (!$response instanceof ContentBlockResponse) {
            return $this->htmlBuilder->tag('div');
        }
        return array_merge(
            [$this->summerBannerContent($response)],
            $this->summerBannerImages($response),
        );
    }

    #[Override]
    public function getKey(): string
    {
        return IndexPageSection::SUMMER_BANNER->value;
    }

    private function summerBannerContent(ContentBlockResponse $response): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $title = $response->getTitle();
        $titleSpan = $response->getSpanTitle();
        $description = $response->getDescription();
        $buttonText = $response->getCtaText();

        return $html->div()->class('banner-section__content')->add(
            $html->div()->class('text')->add(
                $html->tag('h2')->class('text__heading')->add(
                    $html->text($title),
                    $html->tag('span')->content($titleSpan),
                ),
                $html->tag('p')->class('text__description')->content($description),
            ),
            $buttonText !== null ? $html->button('button')->class('btn', 'btn-outline', 'btn-outline-white')->content($buttonText) : null,
        );
    }

    private function summerBannerImages(ContentBlockResponse $response): array
    {
        $html = $this->htmlBuilder;
        $images = $response->getImage();
        $imageComponents = [];
        if (empty($images)) {
            return [];
        }
        $positions = SummerBannerPosition::getAllValues();
        foreach ($positions as $position) {
            $image = $images[$position] ?? null;
            if ($image === null || ($image['src'] ?? null) === null) {
                continue;
            }

            $imageComponents[] = $html->div()->class('banner-section__img-' . $position)->add(
                $html->tag('img')->src($image['src'])->class('image')->alt($image['alt']),
            );
        }

        return $imageComponents;
    }
}