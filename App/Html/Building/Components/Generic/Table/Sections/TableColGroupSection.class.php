<?php

declare(strict_types=1);

class TableColGroupSection extends AbstractBaseHtmlSection implements TableSectionInterface
{
    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $icon,
        HtmlEscaper $escaper,
        private readonly TableConfig $config,
    ) {
        parent::__construct($builder, $icon, $escaper);
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $cols = array_map(
            fn (string $modifier) => $html->tag('col')->class('table__col', $modifier),
            $this->config->colGroupClasses(),    // ← derived
        );
        return $html->tag('colgroup')->add(...$cols);
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