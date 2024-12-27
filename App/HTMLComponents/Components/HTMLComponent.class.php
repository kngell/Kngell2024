<?php

declare(strict_types=1);
class HTMLComponent_old extends AbstractHTMLComponent
{
    private string $html;
    private string $section;

    public function __construct(?string $section = '', array $params = [], ?TemplatePathsInterface $paths = null)
    {
        parent::__construct($params, $paths);
        $this->section = $section;
    }

    public function display(): array
    {
        $childs = $this->children->all();
        foreach ($childs as $child) {
            [$property,$html] = $child->display();
            if (property_exists($this, $property)) {
                $this->{$property} = $html;
            }
        }
        return [$this->section => $this->html];
    }
}