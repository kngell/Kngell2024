<?php

declare(strict_types=1);

interface TableSectionRendererInterface
{
    /**
     * Render a table section from its configuration.
     *
     * @param mixed       $config  Section-specific configuration
     * @param HtmlBuilder $builder The HTML builder instance
     *
     * @return AbstractHtmlComponent
     */
    public function render(mixed $config, HtmlBuilder $builder): AbstractHtmlComponent;
}