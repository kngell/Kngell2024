<?php

declare(strict_types=1);
class SpecialPriceHTMLComponent extends AbstractHTMLComponent
{
    private string $specialPriceHTML;
    private string $section = 'specialPrice';

    public function __construct(?string $template = null)
    {
        parent::__construct($template);
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
        return [$this->section => $this->specialPriceHTML];
    }
}