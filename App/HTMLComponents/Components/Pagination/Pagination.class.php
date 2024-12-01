<?php

declare(strict_types=1);

class Pagination extends AbstractPagination
{
    public function __construct(?array $params = [], ?TemplatePathsInterface $paths = null)
    {
        parent::__construct($params, $paths);
    }

    public function display(): array
    {
        return [$this->previousLink(), $this->nextLink(), $this->links()];
    }

    private function nextLink() : string
    {
        return str_replace('{{href_next}}', $this->currentPage < $this->totalPages ? '?page=' . $this->currentPage + 1 : '#', $this->getTemplate('nextPath'));
    }

    private function previousLink() : string
    {
        return str_replace('{{href_prev}}', $this->currentPage > 2 ? '?page=' . $this->currentPage - 1 : '/account', $this->getTemplate('previousPath'));
    }

    private function links() : string
    {
        $linkHtml = '';
        $range = $this->range($this->currentPage, $this->totalPages);
        foreach ($range as $key => $value) {
            if ($key === 'init') {
                $linkHtml .= $this->getLink(1, $range[$key]);
            } elseif ($key === 'separator1' || $key === 'separator2') {
                $linkHtml .= $this->linkDots;
            } elseif ($key === 'lastTotal') {
                $linkHtml .= $this->getLink($range[$key], $range[$key]);
            } else {
                $linkHtml .= $this->getLink($value[0], $value[2]);
            }
        }

        return $linkHtml;
    }
}