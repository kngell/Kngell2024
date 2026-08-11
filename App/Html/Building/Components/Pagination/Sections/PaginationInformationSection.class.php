<?php

declare(strict_types=1);
class PaginationInformationSection implements PaginationSectionInterface
{
    public function __construct(
        private HtmlBuilder $builder,
        private int $currentPage,
        private int $recordsPerPage,
        private int $totalRecords,
    ) {
    }

    public function supports(string $key): bool
    {
        return $key === 'infos';
    }

    public function getSection(): AbstractHtmlComponent
    {
        $html = $this->builder;
        $startRecord = ($this->currentPage - 1) * $this->recordsPerPage + 1;
        $endRecord = min($this->currentPage * $this->recordsPerPage, $this->totalRecords);

        return $html->tag('div')->class('pagination__info')->add(
            $html->text('Showing'),
            $html->tag('span')->class('pagination__current')->content((string) $startRecord . '-' . $endRecord),
            $html->text('of'),
            $html->tag('span')->class('pagination__total')->content((string) $this->totalRecords),
            $html->text('Items'),
        );
    }

    // public function getSection(): AbstractHtmlComponent
    // {
    //     $html = $this->builder;
    //     $startRecord = ($this->currentPage - 1) * $this->recordsPerPage + 1;
    //     $endRecord = min($this->currentPage * $this->recordsPerPage, $this->totalRecords);

    //     return $html->tag('div')->class('pagination__info')->add(
    //         $html->text('Showing'),
    //         $html->tag('span')->class('pagination__current')->content((string) $startRecord . '-' . $endRecord),
    //         $html->text('of'),
    //         $html->tag('span')->class('pagination__total')->content((string) $this->totalRecords),
    //         $html->text('Pages'),
    //     );
    // }
}