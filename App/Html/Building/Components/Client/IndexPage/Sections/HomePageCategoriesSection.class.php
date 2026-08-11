<?php

declare(strict_types=1);

class HomePageCategoriesSection extends AbstractBaseHtmlSection
{
    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private CategoryMenuService $categoryService,
        private ?string $pageTarget = null,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        try {
            $response = $this->categoryService->getFlatMenu(true);

            if (empty($response)) {
                return $this->renderEmptyState();
            }

            return $this->renderSection($response);
        } catch (Throwable $e) {
            error_log("Category section error on page {$this->pageTarget}: {$e->getMessage()}");
            return $this->renderErrorState();
        }
    }

    public function getKey(): string
    {
        return IndexPageSection::CATEGORY->value;
    }

    private function renderSection(array $response): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->div()
            ->class('container category-container')
            ->attribute('aria-labelledby', 'category-heading')
            ->add(
                $this->categoryHeader(),
                $this->categoryBody($response),
            );
    }

    private function renderEmptyState(): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->tag('div')
            ->class('category-empty-state')
            ->add(
                $this->iconBuilder->createIcon(
                    'icon-category-empty',
                    'No categories available',
                    ['empty-state-icon'],
                ),
                $html->tag('p')
                    ->class('category-empty-state__message')
                    ->content('No categories available at the moment.'),
            );
    }

    private function renderErrorState(): AbstractHtmlComponent
    {
        return $this->htmlBuilder->tag('div')
            ->class('category-error')
            ->attribute('data-error', 'true');
    }

    private function categoryHeader(): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->div()->class('category-header')->add(
            $html->tag('h2')
                ->id('category-heading')
                ->class('category-header__title')
                ->content('Browse By Category'),
            $html->div()
                ->class('category-header__arrows')
                ->attribute('role', 'group')
                ->attribute('aria-label', 'Category navigation controls')
                ->add(
                    ...$this->arrows(),
                ),
        );
    }

    private function categoryBody(array $response): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        return $html->div()
            ->class('category-body')
            ->attribute('data-category-count', count($response))
            ->attribute('data-category-slider', 'true')
            ->add(
                ...$this->bodyCards($response),
            );
    }

    /**
     * @param Category[] $response
     *
     * @return AbstractHtmlComponent[]
     */
    private function bodyCards(array $response): array
    {
        $cards = [];

        /** @var Category $categoryResponse */
        foreach ($response as $categoryResponse) {
            $card = $this->bodyCard($categoryResponse);
            if ($card !== null) {
                $cards[] = $card;
            }
        }

        return $cards;
    }

    private function bodyCard(Category $response): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $icon = $response->getIcon();

        if (empty($icon)) {
            return null;
        }

        $name = $response->getName();
        $url = '';
        $categoryId = $response->getId();

        return $html->tag('a')
            ->href($url)
            ->class('category-body__card')
            ->attribute('aria-label', "Browse {$name} category")
            ->attribute('data-category-id', $categoryId)
            ->attribute('data-category-name', $name)
            ->attribute('data-category-slug', $response ? $response->getSlug() : '')
            ->attribute('data-analytics-event', 'category_click')
            ->add(
                $html->div()->class('category-body__card--icon-wrapper')->add(
                    $this->iconBuilder->createIcon(
                        $icon,
                        $name,
                        [$response->getCssClass() ?? ''],
                    ),
                ),
                $html->tag('p')->class('category-body__card--icon-label')->content($name),
            );
    }

    /**
     * @return AbstractHtmlComponent[]
     */
    private function arrows(): array
    {
        $html = $this->htmlBuilder;
        $arrows = [];

        $arrows[] = $html->button('button')
            ->class('arrow arrow-left')
            ->attribute('aria-label', 'Previous categories')
            ->attribute('data-slider-direction', 'prev')
            ->add(
                $this->iconBuilder->createIcon('icon-arrow-left', 'Left Arrow', ['left-arrow']),
            );

        $arrows[] = $html->button('button')
            ->class('arrow arrow-right')
            ->attribute('aria-label', 'Next categories')
            ->attribute('data-slider-direction', 'next')
            ->add(
                $this->iconBuilder->createIcon('icon-arrow-right', 'Right Arrow', ['right-arrow']),
            );

        return $arrows;
    }
}