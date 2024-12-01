<?php

declare(strict_types=1);
class UserAccountHTMLComponent extends AbstractHTMLComponent
{
    private string $section;
    private string $html = '';

    public function __construct(?string $template = null)
    {
        parent::__construct($template);
    }

    public function display(): array
    {
        $childs = $this->children->all();
        foreach ($childs as $child) {
            [$this->section,$this->html] = $child->display();
        }
        return [$this->section => $this->html];
    }
}