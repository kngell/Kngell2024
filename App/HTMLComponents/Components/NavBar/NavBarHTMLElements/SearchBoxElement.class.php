<?php

declare(strict_types=1);

class SearchBoxElement extends AbstractHTMLElement
{
    public function __construct(?array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
    }

    public function display(): array
    {
        return ['searchBox', $this->params['frm']];
    }
}