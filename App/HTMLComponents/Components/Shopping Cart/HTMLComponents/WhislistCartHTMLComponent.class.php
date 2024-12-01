<?php

declare(strict_types=1);
class WhislistCartHTMLComponent extends AbstractHTMLComponent
{
    private string $html = '';
    private string $section = 'whislist';
    private string $wishlistStyle;

    public function __construct(array $params = [], ?TemplatePathsInterface $paths = null)
    {
        parent::__construct($params, $paths);
    }

    public function display(): array
    {
        $childs = $this->children->all();
        foreach ($childs as $child) {
            [$html,$this->wishlistStyle] = $child->display();
            $this->html = $this->whislistCartItems($html);
        }
        return [$this->section => $this->html];
    }

    private function whislistCartItems(string $html) : string
    {
        $this->section = 'whislist';
        $temp = str_replace('{{whishlist_items}}', $html, $this->getTemplate('whishlistPath'));
        return str_replace('{{display}}', $this->wishlistStyle, $temp);
    }
}