<?php

declare(strict_types=1);

class TableCaptionSection extends AbstractTableSection implements ProductTableSectionInterface
{
    private const string CAPTION_TEXT = 'This table lists products with their SKU, category, stock, price, status, date added and actions.Each product row starts with a checkbox followed by an image and product name.';
    private const array TABLE_CAPTION_CLASS = ['visually-hidden'];
    private const string TABLE_CAPTION_ID = 'table-desc';

    public function __construct(HtmlBuilder $builder, IconBuilder $icon, HtmlSectionPresentationService $presenter)
    {
        parent::__construct($builder, $icon, $presenter);
    }

    public function supports(string $key): bool
    {
        return $key === 'caption';
    }

    public function getSection(): AbstractHtmlComponent
    {
        $html = $this->builder;
        return $html->tag('caption')
            ->class(...self::TABLE_CAPTION_CLASS)
            ->id(self::TABLE_CAPTION_ID)
            ->content(self::CAPTION_TEXT);
    }
}