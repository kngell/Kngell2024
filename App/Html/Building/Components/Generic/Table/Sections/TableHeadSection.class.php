<?php

declare(strict_types=1);

class TableHeadSection extends AbstractBaseHtmlSection implements TableSectionInterface
{
    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $icon,
        HtmlEscaper $escaper,
        private readonly TableConfig $config,
    ) {
        parent::__construct($builder, $icon, $escaper);
    }

    public function getConfig(array $entities = []): array|AbstractHtmlComponent
    {
        return $this->config->headRows();
    }

    public function getKey(): string
    {
        return TableListSection::THEAD->value;
    }

    public function getTableSectionType(): TableListSection
    {
        return TableListSection::THEAD;
    }
}