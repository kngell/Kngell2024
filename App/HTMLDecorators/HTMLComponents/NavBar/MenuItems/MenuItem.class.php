<?php

declare(strict_types=1);

class MenuItem implements MenuItemInterface
{
    private array $acl;
    private array $userAclGroup;
    private array $menu;

    public function __construct(private SessionInterface $session)
    {
        $this->acl = json_decode(file_get_contents(FileManager::get(APP, 'acl.json')), true);
        $this->menu = json_decode(file_get_contents(FileManager::get(APP, 'menu_acl.json')), true);
        //   $this->userAclGroup = AuthService::currentUser()->getAcl();
    }

    public function getmenu(): array
    {
        return [];
    }

    private function verifyAccount(array $menuItems, User|null $user = null) : array
    {
        if ($user === null) {
            return $menuItems;
        }
        if ($user->isVerified() && array_key_exists('Confirmez votre compte', $menuItems['Account'])) {
            unset($menuItems['Account']['Confirmez votre compte']);
        }
        return $menuItems;
    }
}
