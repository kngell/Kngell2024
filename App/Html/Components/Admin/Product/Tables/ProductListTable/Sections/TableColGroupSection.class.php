<?php

declare(strict_types=1);

class TableColGroupSection extends AbstractTableSection implements ProductTableSectionInterface
{
    public function __construct(HtmlBuilder $builder, IconBuilder $icon, HtmlSectionPresentationService $presenter)
    {
        parent::__construct($builder, $icon, $presenter);
    }

    public function supports(string $key): bool
    {
        return $key === 'colGroup';
    }

    public function getSection(): AbstractHtmlComponent
    {
        $html = $this->builder;
        return $html->tag('colgroup')->add(
            $html->tag('col')->class('table__col', 'table__col--product'),
            $html->tag('col')->class('table__col', 'table__col--sku'),
            $html->tag('col')->class('table__col', 'table__col--category'),
            $html->tag('col')->class('table__col', 'table__col--stock'),
            $html->tag('col')->class('table__col', 'table__col--price'),
            $html->tag('col')->class('table__col', 'table__col--status'),
            $html->tag('col')->class('table__col', 'table__col--added'),
            $html->tag('col')->class('table__col', 'table__col--action'),
        );
    }
}