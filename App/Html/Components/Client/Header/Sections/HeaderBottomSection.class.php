<?php

declare(strict_types=1);

class HeaderBottomSection extends AbstractBaseHtmlSection
{
    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private CategoryService $navService,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder); // Removed 'return'
    }

    public function getKey(): string
    {
        return 'header_bottom';
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $categories = $this->navService->getNavCategories();

        if (empty($categories)) {
            return [];
        }

        return $this->buildCategoryNav($categories);
    }

    private function buildCategoryNav(array $categories): array
    {
        $categoryLinks = [];

        foreach ($categories as $category) {
            $categoryLinks[] = $this->buildCategoryLink($category);
        }

        return $categoryLinks;
    }

    private function buildCategoryLink(array $category): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $link = $html->tag('a')
            ->href($this->generateCategoryUrl($category))
            ->class('category-nav__link');

        $link->add(
            $this->iconBuilder->createIcon(
                $html,
                $category['icon'],
                $category['name'],
                [
                    'category-nav__link-icon',
                    StringUtils::kebabCase(strtolower($category['name'])),
                ],
                $category['name'],
            ),
        );

        // Add text span
        $link->add(
            $html->tag('span')
                ->class('category-nav__link-text')
                ->content($category['name']),
        );

        return $link;
    }

    private function generateCategoryUrl(array $category): string
    {
        // Generate URL based on category slug
        // You might want to inject a Router service for this
        return '/category/' . ($category['slug'] ?? '#');
    }
}
