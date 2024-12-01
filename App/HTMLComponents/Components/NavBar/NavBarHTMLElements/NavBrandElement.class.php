<?php

declare(strict_types=1);
class NavBrandElement extends AbstractHTMLElement
{
    public function __construct(?array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
    }

    public function display(): array
    {
        $route = isset($this->params['route']) ? $this->params['route'] : '/';
        $template = str_replace('{{brandRoute}}', $route, $this->getTemplate('navBrandPath'));
        return ['navBrand', $template];
    }
}