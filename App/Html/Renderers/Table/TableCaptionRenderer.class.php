<?php

declare(strict_types=1);

final class TableCaptionRenderer implements TableSectionRendererInterface
{
    public function render(mixed $config, HtmlBuilder $builder): AbstractHtmlComponent
    {
        // Config could be a pre-built component
        if ($config instanceof AbstractHtmlComponent) {
            return $config;
        }

        // Or an array with text/class
        if (is_array($config)) {
            $text = $config['text'] ?? '';
            $class = $config['class'] ?? 'table__caption';

            return $builder->tag('caption')
                ->class($class)
                ->content($text);
        }

        // Fallback: string content
        return $builder->tag('caption')
            ->class('table__caption')
            ->content((string) $config);
    }
}