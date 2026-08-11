<?php

declare(strict_types=1);

class TableCaptionSection extends AbstractBaseHtmlSection implements TableSectionInterface
{
    private const array  TABLE_CAPTION_CLASS = ['visually-hidden'];
    private const string TABLE_CAPTION_ID = 'table-desc';

    public function __construct(
        HtmlBuilder $builder,
        IconBuilder $icon,
        HtmlEscaper $escaper,
        private readonly TableConfig $config,
    ) {
        parent::__construct($builder, $icon, $escaper);
    }

    public function getConfig(array $context = []): array|AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        return $html->tag('caption')
            ->class(...self::TABLE_CAPTION_CLASS)
            ->id(self::TABLE_CAPTION_ID)
            ->content($this->config->captionText);
    }

    public function getKey(): string
    {
        return TableListSection::CAPTION->value;
    }

    public function getTableSectionType(): TableListSection
    {
        return TableListSection::CAPTION;
    }

    public function getEntities(): array
    {
        return [];
    }
}