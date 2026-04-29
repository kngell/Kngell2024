<?php

declare(strict_types=1);

class HeroTableColGroupSection extends AbstractBaseHtmlSection implements TableSectionInterface
{
    public function __construct(HtmlBuilder $builder, IconBuilder $icon, HtmlSectionPresentationService $presenter)
    {
        parent::__construct($builder, $icon, $presenter);
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        return $html->tag('colgroup')->add(
            $html->tag('col')->class('table__col', 'table__col--start'),
            $html->tag('col')->class('table__col', 'table__col--normal'),
            $html->tag('col')->class('table__col', 'table__col--normal'),
            $html->tag('col')->class('table__col', 'table__col--normal'),
            $html->tag('col')->class('table__col', 'table__col--normal'),
            $html->tag('col')->class('table__col', 'table__col--badge'),
            $html->tag('col')->class('table__col', 'table__col--action'),
        );
    }

    public function getKey(): string
    {
        return TableListSection::COL_GROUP->value;
    }

    public function getTableSectionType(): TableListSection
    {
        return TableListSection::COL_GROUP;
    }
}