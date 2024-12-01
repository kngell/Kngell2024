<?php

declare(strict_types=1);
class BtnConnextionElement extends AbstractHTMLElement
{
    private bool $isUerConneted;
    private string $template;

    public function __construct(?array $params, ?TemplatePathsInterface $paths, bool $isUerconnected = false)
    {
        parent::__construct($params, $paths);
        $this->isUerConneted = $isUerconnected;
        $this->template = $this->getTemplate('conectPath');
    }

    public function display(): array
    {
        $connexion = (! $this->isUerConneted) ? $this->btnConnexion() : $this->userMenu();
        $template = str_replace('{{connectedUser}}', $connexion, $this->template);
        $template = str_replace('{{wishlist}}', $this->params['whishlistItems'], $template);
        return ['btnConnexion', $template];
    }

    private function userMenu() : string
    {
        $menu_html = '';
        $userMenuTemplate = $this->getTemplate('menuConnexionPath');
        $user = isset($this->params['user']) ? $this->params['user'] : '';
        $dropMenu = isset($this->params['dropMenu']) ? $this->params['dropMenu'] : [];
        $userMenuTemplate = str_replace('{{user}}', $user, $userMenuTemplate);
        if (! empty($userMenuTemplate) && is_array($dropMenu) && count($dropMenu) > 0) {
            foreach ($dropMenu as $key => $value) {
                $active = ($value == H::currentPage()) ? 'active' : '';
                if ($key == 'separator') {
                    $menu_html .= ' <li role="separator" class="dropdown-divider"></li>';
                } elseif ($key == 'Confirmez votre compte') {
                    $menu_html .= $this->dropdown($active, $key, $value);
                } else {
                    $menu_html .= $this->dropdownActive($active, $key, $value);
                }
            }
        }
        return str_replace('{{dropdownMenu}}', $menu_html, $userMenuTemplate);
    }

    private function btnConnexion() : string
    {
        return '<button type="button" class="px-3 border-right border-left text-dark connexion text-decoration-none"
   data-bs-toggle="modal" data-bs-target="#login-box" id="login_btn">
   <span class="icon login"></span>&nbsp;&nbsp;Login</button>';
    }

    private function dropdown(string $active, string $key, string $value) : string
    {
        $href = $key != 'Logout' ? $value : 'javascript:void(0)';

        return '<li class="dropdown-item nav-item' . $active . ' alert alert-warning">
   <a class="nav-link text-danger" href="' . $href . '"> ' . $key . ' </a>
</li>';
    }

    private function dropdownActive(string $active, string $key, string $value)
    {
        $href = $key != 'Logout' ? $value : 'javascript:void(0)';

        return '<li class="dropdown-item nav-item ' . $active . '">
   <a class="nav-link" href="' . $href . '">' . $key . '</a>
</li>';
    }
}