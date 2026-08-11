<?php

declare(strict_types=1);
class Pagination implements PaginationInterface
{
    private const array SECTIONS = ['infos', 'nav', 'perPage'];

    public function __construct(
        private HtmlBuilder $builder,
        private IconBuilder $icon,
        private int $currentPage,
        private int $recordsPerPage,
        private int $totalRecords,
        private int $totalPages,
        private Request $request,
        private array $allowedPageSizes,
    ) {
    }

    public function getPagination(): string
    {
        $html = $this->builder;
        return $html->tag('div')->class('pagination')->add(
            ...$this->buildSections(),
        )->generate();
    }

    private function buildSections(): array
    {
        $htmlComponents = [];
        $factory = new PaginationSectionFactory(
            $this->builder,
            $this->icon,
            $this->currentPage,
            $this->recordsPerPage,
            $this->totalRecords,
            $this->totalPages,
            $this->request,
            $this->allowedPageSizes,
        );
        foreach (self::SECTIONS as $section) {
            $htmlComponents[] = $factory->create($section)->getSection();
        }
        return $htmlComponents;
    }
}