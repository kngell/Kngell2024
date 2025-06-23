<?php

declare(strict_types=1);

class NavbarHtmlElement extends AbstractNavbarHtmlElement
{
    private array $menuItems;
    private Request $request;

    public function __construct(private HtmlBuilder $builder, private SessionInterface $session, Request $request)
    {
        $this->request = $request;
        $this->menuItems = json_decode(file_get_contents(FileManager::get(APP, 'menu_acl.json')), true);
    }

    public function display(): string
    {
        $nav = $this->builder;
        $user = AuthService::getInstance()->getCurrentLoggedInUser();
        return $nav->tag('nav')->class(...self::NAVBAR_CLASS)->add(
            $nav->tag('div')->class('nav-brand')->add(
                $nav->tag('a')->class('nav-brand--link')->href('/')->add(
                    $nav->tag('img')->src($this->file('logo.png'))->class('nav-brand--link-img')
                ),
                $nav->button('button')->class('nav-brand--icon')->add(
                    $nav->tag('img')->src($this->file('hemburger-menu.svg'))->class('icon-img')
                    // $nav->tag('i')->class('fa-solid fa-bars')
                ),
            ),
            ...$this->getNavElements($user)
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
        $ul = $html->tag('ul')->class('nav-menu');
        foreach ($this->menuItems as $menuItem => $link) {
            // $class = $menuItem === array_key_first($this->menuItems) ? ['nav-menu__item--btn', 'active'] : ['nav-menu__item--btn'];
            if (is_array($link) && $menuItem === 'Account') {
                $accountElements = $html->tag('div')->class('nav-connexion')->add(
                    ...$this->accountElements($user, $link),
                );
            } else {
                $class = $this->getClass($menuItem, $link);
                $ulElements[] = $html->tag('li')->class('nav-menu__item')->add(
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
        $accountElements = $this->getAccountElements($user);
        foreach ($accountElements as $menuItem => $link) {
            $class = $this->getClass($menuItem, $link);
            if (is_array($link)) {
                $accountElts[] = $this->builder->tag('a')->href($link[0])->content($menuItem, false)->class(...$class)->add(
                    $this->builder->htmlBlock()->content('<svg  width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M12 10C14.2091 10 16 8.20914 16 6C16 3.79086 14.2091 2 12 2C9.79086 2 8 3.79086 8 6C8 8.20914 9.79086 10 12 10Z" stroke="#F4F2E3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 22C21 17.0294 16.9706 13 12 13C7.02945 13 3 17.0294 3 22" stroke="#F4F2E3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>')
                    // $this->builder->tag('img')->src($link[1])->class('nav-connexion__img')
                );
            } else {
                $accountElts[] = $this->builder->tag('a')->href($link)->content($menuItem)->class('nav-connexion__btn');
            }
        }
        return $accountElts;
    }

    private function getClass(string $menuitem, string|array $link) : array
    {
        $url = $this->request->getQuery()->get('url');
        is_array($link) ? $link = $link[0] : $link;
        if ($this->request->isGet() && $this->request->getQuery()->get('url') === ltrim($link, DS)) {
            if ($menuitem === 'Account') {
                return ['nav-connexion__btn', 'active'];
            } else {
                return['nav-menu__item--btn', 'active'];
            }
        }
        return['nav-menu__item--btn'];
    }

    private function getAccountElements(User|null $user = null) : array
    {
        if (! AuthService::getInstance()->isUserLoggedIn()) {
            return [
                'Login' => ['/login'],
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
