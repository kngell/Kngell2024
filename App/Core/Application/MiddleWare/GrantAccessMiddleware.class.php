<?php

declare(strict_types=1);

class GrantAccessMiddleware extends AbstractMiddleware implements MiddlewareInterface
{
    private array $acl;

    public function __construct(private RouteInfo $route, private SessionInterface $session, private AclGroupModel $aclGroup)
    {
        $this->acl = json_decode(file_get_contents(FileManager::get(APP, 'acl.json')), true);
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        $current_user_acls = ['Guest'];
        $grantAccess = false;
        if ($this->session->exists(CURRENT_USER_SESSION_NAME)) {
            $current_user_acls[] = 'LoggedIn';
            $user = AuthService::currentUser();
            $aclGroup = $this->aclGroup->acl($user);
            foreach ($aclGroup as $a) {
                $current_user_acls[] = $a;
            }
        }
        foreach ($current_user_acls as $level) {
            if (array_key_exists($level, $this->acl) && array_key_exists($this->route->getController(), $this->acl[$level])) {
                if (in_array($this->route->getMethod()->getName(), $this->acl[$level][$this->route->getController()]) || in_array('*', $this->acl[$level][$this->route->getController()])) {
                    $grantAccess = true;
                    break;
                }
            }
        }
        // Checck for denied
        foreach ($current_user_acls as $level) {
            $denied = $this->acl[$level]['denied'];
            if (! empty($denied) && array_key_exists($this->route->getController(), $denied)) {
                if (in_array($this->route->getMethod()->getName(), $denied[$this->route->getController()]) || in_array('*', $denied[$this->route->getController()])) {
                    $grantAccess = false;
                    break;
                }
            }
        }
        if (! $grantAccess) {
            return $this->redirect('/_restrict', false);
        }
        return $next->handle($request);
    }

    // private function menuItem(User|null $user) : array
    // {
    //     $menuItems = json_decode(file_get_contents(FileManager::get(APP, 'menu_acl.json')), true);
    //     $menuItems = $this->verifyAccount($menuItems, $user);
    //     $menuAry = [];
    //     foreach ($menuItems as $menuItem => $link) {
    //         if (is_array($link)) {
    //             $subMenu = [];
    //             foreach ($link as $subItem => $subLink) {
    //                 if ($subItem == 'separator' && ! empty($subMenu)) {
    //                     $subMenu[$subItem] = '';
    //                     continue;
    //                 }
    //                 if ($finalVal = $this->getlink($subLink)) {
    //                     $subMenu[$subItem] = $finalVal;
    //                 }
    //             }
    //             if (! empty($subMenu)) {
    //                 $menuAry[$menuItem] = $subMenu;
    //             }
    //         } else {
    //             if ($finalVal = $this->getlink($link)) {
    //                 $menuAry[$menuItem] = $finalVal;
    //             }
    //         }
    //     }
    //     return $menuAry;
    // }

    // private function getLink(string $link) : bool|string
    // {
    //     if (preg_match('/https?:\/\//', $link) == 1) {
    //         return $link;
    //     } else {
    //         if ($this->grantAccess()) {
    //             return $link;
    //         }
    //         return false;
    //     }
    // }
}
