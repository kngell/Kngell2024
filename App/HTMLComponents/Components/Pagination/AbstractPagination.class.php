<?php

declare(strict_types=1);

abstract class AbstractPagination extends AbstractHTMLPage
{
    protected int $totalPages;
    protected int $currentPage;
    protected int $totalRecords;
    protected int $recordPerPage;
    protected array $params;
    protected string $linkDots;

    public function __construct(array $params = [], ?TemplatePathsInterface $paths = null)
    {
        $this->params = $params;
        $this->getRepositoryParts($params);
        parent::__construct($paths);
        $this->linkDots = $this->getTemplate('dotsPath');
    }

    protected function range(int $page, int $totalPages) : array
    {
        if ($totalPages < 6) {
            $start = 2;
        } elseif ($page < 3) {
            $start = 2;
        } elseif ($page > $totalPages - 3) {
            $start = $totalPages - 3;
        } else {
            $start = $page - 1;
        }
        $output = ['init' => 1];
        if ($start > 2) {
            $output['separator1'] = '...';
        }
        for ($i = $start; $i < $totalPages; $i++) {
            $output['main'][] = $i;
            if ($i > ($start + 1)) {
                break;
            }
        }
        if ($start < $totalPages - 3) {
            $output['separator2'] = '...';
        }
        if ($totalPages > 1) {
            $output['lastTotal'] = $totalPages;
        }

        return $output;
    }

    protected function getLink(int $start, int $end)
    {
        $linkHtml = '';
        for ($page = $start; $page <= $end; $page++) {
            $active = $page == $this->currentPage ? ' active' : '';
            $temp = str_replace('{{href}}', '?page=' . $page, $this->getTemplate('linkPath'));
            $temp = str_replace('{{page}}', strval($page), $temp);
            $temp = str_replace('{{active}}', $active, $temp);
            $linkHtml .= $temp;
        }

        return $linkHtml;
    }

    private function getRepositoryParts(array $params) : void
    {
        if (! empty($params)) {
            list('page' => $this->currentPage, 'pagin' => $this->totalPages, 'totalRecords' => $this->totalRecords, 'records_per_page' => $this->recordPerPage, 'additional_conditions' => $adc) = $params;
        }
    }
}