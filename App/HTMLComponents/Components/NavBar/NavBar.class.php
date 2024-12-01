<?php

declare(strict_types=1);
class NavBar extends AbstractHTMLComponent
{
    private string $btnConnexion;
    private string $settings;
    private string $searchBox;
    private string $itemsInCart;
    private string $menu;
    private string $navBrand;
    private string $template;

    public function __construct($params = [], ?TemplatePathsInterface $paths = null)
    {
        parent::__construct($params, $paths);
        $this->template = $this->getTemplate('navPath');
    }

    public function display(): array
    {
        $childs = $this->children->all();
        $template = '';
        foreach ($childs as $child) {
            [$property,$html] = $child->display();
            if (property_exists($this, $property)) {
                $this->{$property} = $html;
            }
        }
        if ($this->template != null) {
            $template = str_replace('{{settings}}', $this->settings, $this->template);
            $template = str_replace('{{searchBox}}', $this->searchBox, $template);
            $template = str_replace('{{connection}}', $this->btnConnexion, $template);
            $template = str_replace('{{cartItems}}', $this->itemsInCart, $template);
            $template = str_replace('{{navbar-brand}}', $this->navBrand, $template);
            $template = str_replace('{{menu}}', $this->menu, $template);
            return ['navComponent' => $template];
        }
        return [];
    }
}