<?php

declare(strict_types=1);

final class PaginationSectionFactory
{
    /** @var PaginationSectionInterface[] */
    private array $sections = [];

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
        $this->registerSections();
    }

    public function create(string $key): PaginationSectionInterface
    {
        try {
            foreach ($this->sections as $section) {
                if ($section->supports($key)) {
                    return $section;
                }
            }
            throw new RuntimeException('Section not defined');
        } catch (Throwable $e) {
            throw new TableSectionException('Unable to create a section', $e->getCode());
        }
    }

    private function registerSections(): void
    {
        $this->sections[] = new PaginationInformationSection(
            $this->builder,
            $this->currentPage,
            $this->recordsPerPage,
            $this->totalRecords,
        );
        $this->sections[] = new PaginationNavSection(
            $this->builder,
            $this->icon,
            $this->currentPage,
            $this->totalPages,
            $this->recordsPerPage,
            $this->request,
        );
        $this->sections[] = new PaginationPerPageSection(
            $this->builder,
            $this->recordsPerPage,
            $this->allowedPageSizes,
            $this->request,
        );
    }
}