<?php

declare(strict_types=1);
class TopSalesHTMLComponent extends AbstractHTMLComponent
{
    private string $topSalesHTML;
    private string $section = 'topSales';

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
        return [$this->section => $this->topSalesHTML];
    }
}