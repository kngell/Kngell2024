<?php

declare(strict_types=1);

class Paginator
{
    private const array LIST_CLASS = ['pagination__item'];
    private const array LINK_CLASS = ['pagination__link'];
    private const array PREV_CLASS = ['pagination__link', 'pagination__link--previous'];
    private const array NEXT_CLASS = ['pagination__link', 'pagination__link--next'];
    private const array ACTIVE_CLASS = ['pagination__link', 'pagination__link--active'];
    private const string ICON_PREV = '<i class="fa-solid fa-angles-left"></i>';
    private const string ICON_NEXT = '<i class="fa-solid fa-angles-right"></i>';

    /** @var int */
    private float $totalPages;

    private int $recordsPerPage;
    private int $currentPage;
    private int $offset;

    public function __construct(int $totalRecords, int $recordsPerPage, int $currentPage, private HtmlBuilder $builder)
    {
        // $sql="SELECT * FROM 'posts' ORDER BY 'date' DESC, 'id' LIMIT: itemsPerPage OFFSET 0"
        // if currentPage== 1,offset = 0;
        // currentPage == 2, offset = itemsPerPage
        // currentPage == 3, offset = itemsPerpage * 2
        // offset = (currentPage -1) * itemsPerPage
        $this->totalPages = ceil($totalRecords / $recordsPerPage);
        $this->recordsPerPage = $recordsPerPage;
        $this->currentPage = $currentPage;
        $this->offset = ($this->currentPage - 1) * $this->recordsPerPage;
    }

    public function getLinks() : string
    {
        $html = $this->builder;
        $links = $this->totalPages > 1 ? $this->paginationLinks() : [];
        return $html->tag('ul')->class('pagination')->add(
            ...$links
        )->generate();
    }

    /**
     * Get the value of offset.
     *
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Get the value of currentPage.
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @return AbstractHtmlComponent[]
     */
    private function paginationLinks() : array
    {
        $array = [];
        $html = $this->builder;
        if ($this->currentPage > 1) {
            $array[] = $html->tag('li')->class(...self::LIST_CLASS)->add(
                $html->tag('a')->class(...self::PREV_CLASS)->href($this->previousPage())->content(self::ICON_PREV)
            );
        }
        for ($i = 0; $i < $this->totalPages; $i++) {
            $classLink = self::LINK_CLASS;
            if ($i + 1 === $this->currentPage) {
                $classLink = self::ACTIVE_CLASS;
            }
            $array[] = $html->tag('li')->class(...self::LINK_CLASS)->add(
                $html->tag('a')->class(...$classLink)->href('/post/index/' . strval($i + 1))->content(strval($i + 1))
            );
        }
        if ($this->currentPage < $this->totalPages) {
            $array[] = $html->tag('li')->class(...self::LINK_CLASS)->add(
                $html->tag('a')->class(...self::NEXT_CLASS)->href($this->nextPage())->content(self::ICON_NEXT)
            );
        }

        return $array;
    }

    private function previousPage() : string
    {
        if ($this->currentPage - 1 < 1) {
            return '1';
        }
        return '/post/index/' . strval($this->currentPage - 1);
    }

    private function nextPage() : string
    {
        if ($this->currentPage + 1 > $this->totalPages) {
            return strval($this->totalPages);
        }
        return '/post/index/' . strval($this->currentPage + 1);
    }
}
