<?php

declare(strict_types=1);

abstract class AbstractTableSection implements ProductTableSectionInterface
{
    public function __construct(
        protected HtmlBuilder $builder,
        protected IconBuilder $icon,
    ) {
    }

    /**
     * Create media HTML component.
     */
    protected function media(?string $media = null, string $alt = ''): AbstractHtmlComponent
    {
        $html = $this->builder;

        if ($media) {
            return $html->tag('span')->class('img-container')->add(
                $html->tag('img')->src($media)->alt($alt)->class('image'),
            );
        }

        return $html->tag('span')->class('img-container');
    }

    /**
     * Format product variations for display.
     */
    protected function variation(array $variations): AbstractHtmlComponent
    {
        $html = $this->builder;
        $count = count($variations);

        if ($count === 0) {
            return $html->tag('li')->class('text-container__variant')->content('No variants');
        }

        $text = $count === 1 ? '1 Variant' : $count . ' Variants';
        return $html->tag('li')->class('text-container__variant')->content($text);
    }
}
