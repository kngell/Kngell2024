<?php

declare(strict_types=1);

class TableBodySection extends AbstractBaseHtmlSection implements TableSectionInterface
{
    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $icon,
        HtmlEscaper $escape,
        private readonly TableConfig $config,
    ) {
        parent::__construct($builder, $icon, $escape);
    }

    public function getConfig(array $context = []): array|AbstractHtmlComponent
    {
        $this->context = $context;
        return $this->config->bodyRows();
    }

    public function getKey(): string
    {
        return TableListSection::TBODY->value;
    }

    public function getTableSectionType(): TableListSection
    {
        return TableListSection::TBODY;
    }
}