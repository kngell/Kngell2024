<?php

declare(strict_types=1);
class PaginationNavSection implements PaginationSectionInterface
{
    public function __construct(
        private HtmlBuilder $builder,
        private IconBuilder $icon,
        private int $currentPage,
        private int $totalPages,
        private int $recordsPerPage,
        private Request $request,
    ) {
    }

    public function supports(string $key): bool
    {
        return $key === 'nav';
    }

    public function getSection(): AbstractHtmlComponent
    {
        $html = $this->builder;

        $navContent = [
            $this->createPrevButton(),
        ];

        $navContent = array_merge($navContent, $this->generatePageLinks());

        $navContent[] = $this->createNextButton();

        return $html->tag('nav')->class('pagination__nav')->ariaLabel('Product pagination')->add(
            ...$navContent,
        );
    }

    private function generatePageLinks(): array
    {
        $links = [];
        $html = $this->builder;
        $windowSize = 2;
        $start = max(1, $this->currentPage - $windowSize);
        $end = min($this->totalPages, $this->currentPage + $windowSize);

        if ($this->currentPage <= $windowSize + 1) {
            $end = min($this->totalPages, 2 * $windowSize + 1);
        }

        if ($this->currentPage >= $this->totalPages - $windowSize) {
            $start = max(1, $this->totalPages - 2 * $windowSize);
        }

        $showFirstPage = $start > 1;

        if ($showFirstPage) {
            $links[] = $this->createPageLink(1);
            if ($start > 2) {
                $links[] = $html->tag('span')->class('pagination__ellipsis')->content('...');
            }
        }

        for ($page = $start; $page <= $end; $page++) {
            $links[] = $this->createPageLink($page);
        }

        if ($end < $this->totalPages) {
            if ($end < $this->totalPages - 1) {
                $links[] = $html->tag('span')->class('pagination__ellipsis')->content('...');
            }
            $links[] = $this->createPageLink($this->totalPages);
        }

        return $links;
    }

    private function createPageLink(int $page): AbstractHtmlComponent
    {
        $html = $this->builder;
        $isActive = $page === $this->currentPage;

        $classes = ['pagination__page'];
        if ($isActive) {
            $classes[] = 'pagination__page--active';
        }

        $queryParams = $this->request->getQuery()->getAll();
        $queryParams['per_page'] = $this->recordsPerPage;
        $queryParams['page'] = $page;

        $basePath = $this->request->getPathFromUri() ?? '/';
        $linkUrl = $basePath . '?' . http_build_query($queryParams);

        return $html->tag('a')
            ->class(...$classes)
            ->href($linkUrl)
            ->custom([
                'aria-label' => 'Page ' . $page,
                'aria-current' => $isActive ? 'page' : null,
            ])
            ->content((string) $page);
    }

    private function createPrevButton(): AbstractHtmlComponent
    {
        $html = $this->builder;
        $isDisabled = $this->currentPage <= 1;

        $button = $html->tag('a')
            ->class('pagination__btn', 'pagination__btn--prev')
            ->ariaLabel('Previous page')
            ->disabled($isDisabled);

        if (!$isDisabled) {
            $prevPage = $this->currentPage - 1;
            $queryParams = $_GET;
            $queryParams['page'] = $prevPage;
            $url = '?' . http_build_query($queryParams);
            $button->href($url);
        }

        return $button->add(
            $this->icon->createIcon('icon-arrow-left', 'Arrow Left', ['arrow-left'])->ariaHidden(),
        );
    }

    private function createNextButton(): AbstractHtmlComponent
    {
        $html = $this->builder;
        $isDisabled = $this->currentPage >= $this->totalPages;

        // Calculate next page URL
        $nextPage = $this->currentPage + 1;
        $queryParams = $_GET;
        $queryParams['page'] = $nextPage;
        $url = $isDisabled ? '#' : '?' . http_build_query($queryParams);

        $button = $html->tag('a')
            ->class('pagination__btn', 'pagination__btn--next')
            ->href($url)
            ->ariaLabel('Next page')
            ->disabled($isDisabled);

        if ($isDisabled) {
            $button->custom(['onclick' => 'return false;']);
        }

        return $button->add(
            $this->icon->createIcon('icon-arrow-right', 'Arrow Right', ['arrow-right'])->ariaHidden(),
        );
    }
}