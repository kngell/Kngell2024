<?php

declare(strict_types=1);

class NavbarHtmlElement extends AbstractNavbarHtmlElement
{
    private array $menuItems;

    public function __construct(private HtmlBuilder $builder, private UsersModel $users, private SessionInterface $session)
    {
        $this->menuItems = json_decode(file_get_contents(FileManager::get(APP, 'menu_acl.json')), true);
    }

    public function display(): string
    {
        $nav = $this->builder;
        $user = AuthService::getInstance()->getCurrentLoggedInUser();
        return $nav->tag('div')->class(['container'])->add(
            $nav->tag('nav')->class(self::NAVBAR_CLASS)
                ->style(self::NAVBAR_STYLE)->add(
                    $nav->tag('div')->class(['container-fluid'])->add(
                        $nav->tag('a')->class(['navbar-brand'])->href('#')->content('k\'nGELL'),
                        $nav->button('button')->class(['navbar-toggler'])->custom(self::BTN_CUSTOM)->add(
                            $nav->tag('span')->class(['navbar-toggler-icon'])
                        ),
                        $nav->tag('div')->class(['collapse navbar-collapse'])->id('navbarSupportedContent')->add(
                            ...$this->getNavElements($user)
                        )
                    )
                )
        )->generate();
    }

    /**
     * @param array $menuItems
     * @param Users|null $user
     * @return AbstractHtmlComponent[]
     */
    private function getNavElements(Users|null $user) : array
    {
        $ulElements = [];
        $html = $this->builder;
        $ul = $html->tag('ul')->class(['navbar-nav', 'me-auto', 'mb-2', 'mb-lg-0']);
        foreach ($this->menuItems as $menuItem => $link) {
            $class = $menuItem === array_key_first($this->menuItems) ? ['nav-link', 'active'] : ['nav-link'];
            if (is_array($link) && $menuItem === 'Account') {
                $accountElements = $this->builder->tag('div')->add(
                    ...$this->accountElements($user, $link),
                );
            } else {
                $ulElements[] = $html->tag('li')->class(['nav-item'])->add(
                    $html->tag('a')->class($class)->href($link)->content($menuItem)->custom(['aria-current' => 'page'])
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
    private function accountElements(Users|null $user, array $accountElements) : array
    {
        $accountElts = [];
        $accountElements = $this->getAccountElements($user, $accountElements);
        foreach ($accountElements as $menuItem => $link) {
            $accountElts[] = $this->builder->tag('a')->href($link)->content($menuItem);
        }
        return $accountElts;
    }

    private function getAccountElements(Users|null $user = null, array $accountElements) : array
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