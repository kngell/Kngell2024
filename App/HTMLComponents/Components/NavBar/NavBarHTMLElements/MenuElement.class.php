<?php

declare(strict_types=1);
class MenuElement extends AbstractHTMLElement
{
    public function __construct(?array $params, ?TemplatePathsInterface $paths)
    {
        parent::__construct($params, $paths);
    }

    public function display(): array
    {
        $menu = isset($this->params['menu']) ? $this->params['menu'] : [];
        if (! empty($menu)) {
            $menu = array_filter($menu->all(), function ($item) {
                return $item != 'menu_admin_user';
            }, ARRAY_FILTER_USE_KEY);
        }
        $menu = $this->menu($menu);
        return ['menu', $menu];
    }

    private function menu(array $menu) : string
    {
        $menu_html = '';
        if (is_array($menu) && count($menu) > 0) {
            foreach ($menu as $name => $link) {
                $active = '';
                if ($name === array_key_first($menu)) {
                    $active = 'active';
                }
                $menu_html .= $this->menuItem($name, $link, $active);
            }
        }
        return $menu_html;
    }

    private function menuItem(string $name, string $link, string $active)
    {
        return '<li class="nav-item">
        <a class="nav-link ' . $active . '" aria-current="page" href="' . $link . '">' . $name . '</a></li>';
    }
}