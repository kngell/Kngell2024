<?php

declare(strict_types=1);

class NavbarHtmlElement extends AbstractNavbarHtmlElement
{
    private array $menuItems;

    public function __construct(private HtmlBuilder $builder, private UserModel $user, private SessionInterface $session)
    {
        $this->menuItems = json_decode(file_get_contents(FileManager::get(APP, 'menu_acl.json')), true);
    }

    public function display(): string
    {
        $nav = $this->builder;
        $user = AuthService::getInstance()->getCurrentLoggedInUser();
        return $nav->tag('div')->class('container')->add(
            $nav->tag('nav')->class(...self::NAVBAR_CLASS)
                ->style(self::NAVBAR_STYLE)->add(
                    $nav->tag('div')->class('nav__brand')->add(
                        $nav->tag('a')->class('nav__brand--link')->href('/')->add(
                            $nav->tag('img')->src($this->logo())->class('nav__brand--img')
                        ),
                        $nav->button('button')->class('nav__brand--btn')->custom(self::BTN_CUSTOM)->add(
                            $nav->tag('span')->class('nav__brand--icon-container')->add(
                                $nav->tag('i')->class('fa-solid fa-bars')
                            )
                        ),
                    ),
                    $nav->tag('div')->class('nav__menu')->add(
                        ...$this->getNavElements($user)
                    )
                )
        )->generate();
    }

    /**
     * @param array $menuItems
     * @param User|null $user
     * @return AbstractHtmlComponent[]
     */
    private function getNavElements(User|null $user) : array
    {
        $ulElements = [];
        $html = $this->builder;
        $ul = $html->tag('ul')->class('nav__menu-list');
        foreach ($this->menuItems as $menuItem => $link) {
            $class = $menuItem === array_key_first($this->menuItems) ? ['nav__menu-list--link', 'active'] : ['nav__menu-list--link'];
            if (is_array($link) && $menuItem === 'Account') {
                $accountElements = $this->builder->tag('div')->class('nav_menu-list--connexion')->add(
                    ...$this->accountElements($user, $link),
                );
            } else {
                $ulElements[] = $html->tag('li')->class('nav__menu-list-item')->add(
                    $html->tag('a')->class(...$class)->href($link)->content($menuItem)->custom(['aria-current' => 'page'])
                );
            }
        }
        return [$ul->add(...$ulElements), $accountElements];
    }

    /**
     * @param Users|null $user
     * @param array $acountMenuElements
     * @return AbstractHtmlComponent[]
     */
    private function accountElements(User|null $user, array $accountElements) : array
    {
        $accountElts = [];
        $accountElements = $this->getAccountElements($user, $accountElements);
        foreach ($accountElements as $menuItem => $link) {
            $accountElts[] = $this->builder->tag('a')->href($link)->content($menuItem);
        }
        return $accountElts;
    }

    private function getAccountElements(User|null $user = null, array $accountElements) : array
    {
        if (! AuthService::getInstance()->isUserLoggedIn()) {
            return [
                'Login' => '/login',
                'Register' => '/signup',
            ];
        }
        if ($user !== null) {
            $menuItems = [];
            if ($user->isVerified() && array_key_exists('Confirmez votre compte', $this->menuItems['Account'])) {
                unset($this->menuItems['Account']['Confirmez votre compte']);
            }
        }
        foreach ($this->menuItems['Account'] as $menuItem => $link) {
            if (! empty($link)) {
                $menuItems[$menuItem] = $link;
            }
        }
        return $menuItems;
    }
}