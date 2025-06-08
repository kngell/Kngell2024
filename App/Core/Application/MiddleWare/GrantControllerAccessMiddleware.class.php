<?php

declare(strict_types=1);

class GrantControllerAccessMiddleware implements MiddlewareInterface
{
    private array $acl;

    public function __construct(private RouteInfo $route, private SessionInterface $session, private AclGroupModel $aclGroup, private FlashInterface $flash)
    {
        $this->acl = json_decode(file_get_contents(APP . 'acl.json'), true);
    }

    public function process(Request $request, RequestHandlerInterface $next): Response|string
    {
        $current_user_acls = ['Guest'];
        // get User Acls group if they exists
        [$current_user_acls,$user] = $this->getUserAcls($current_user_acls);
        // Check access granted
        $results = $this->checkAccessGranted($current_user_acls, $request, $user);

        if ($results instanceof Response) {
            return $results;
        }
        if (is_bool($results) && ! $results) {
            return new RedirectResponse('/_restrict');
        }
        return $next->handle($request);
    }

    private function checkAccessGranted(array $current_user_acls, Request $request, ?User $user) : bool|Response
    {
        $grantAccess = false;
        foreach ($current_user_acls as $level) {
            if (! array_key_exists($level, $this->acl)) {
                continue;
            }
            if (! array_key_exists($this->route->getController(), $this->acl[$level])) {
                continue;
            }
            if (array_key_exists('middleware', $this->route->getRouteParams())) {
                $middlewares = $this->route->getRouteParams()['middleware'];
                if (str_contains($middlewares, 'requireLogin')) {
                    return true;
                }
            }

            // Check if the controller and method are allowed for this ACL level
            $allowedMethods = $this->acl[$level][$this->route->getController()];

            if (in_array($this->route->getMethod()->getName(), $allowedMethods) || in_array('*', $allowedMethods)) {
                $grantAccess = true;
                break; // Access granted, no need to check other levels
            }
        }
        return $this->checkDeniedAccess($current_user_acls, $request, $user, $grantAccess);
    }

    private function checkDeniedAccess(array $current_user_acls, Request $request, ?User $user, bool $grantAccess) : bool|Response
    {
        // Only check denied access if access was initially granted
        if ($grantAccess) {
            foreach ($current_user_acls as $level) {
                $denied = $this->getDeniedControllers($level);
                if (empty($denied)) {
                    continue;
                }
                if (array_key_exists($this->route->getController(), $denied)) {
                    if (in_array($this->route->getMethod()->getName(), $denied[$this->route->getController()]) || in_array('*', $denied[$this->route->getController()])) {
                        $grantAccess = false;
                        break; // Access denied, no need to check other levels
                    }
                }
            }
        }
        return $grantAccess ? true : $this->handleDeniedAccess($request, $user);
    }

    private function handleDeniedAccess(Request $request, ?User $user) : bool|Response
    {
        if ($user && $request->get('request_uri') === '/login') {
            $previousUrl = $this->session->get('previous_url');
            $this->session->delete('previous_url');
            return new RedirectResponse($previousUrl);
        }
        if ($user && $request->get('request_uri') === '/logout') {
            return true;
        }
        // Allow access to edit pages
        if (str_contains($request->get('request_uri'), '/edit')) {
            return true;
        }
        // Allow access to login page
        if (str_contains($request->get('request_uri'), '/login')) {
            return true;
        }
        return false;
    }

    private function getDeniedControllers(string $level) : array
    {
        if (array_key_exists($level, $this->acl) && array_key_exists('denied', $this->acl[$level])) {
            return $this->acl[$level]['denied'];
        }
        return [];
    }

    private function getUserAcls(array $current_user_acls) : array
    {
        if ($this->session->exists(CURRENT_USER_SESSION_NAME)) {
            $current_user_acls[] = 'LoggedIn';
            $user = AuthService::currentUser();
            $aclGroup = $this->aclGroup->getUserAuthorization($user);
            foreach ($aclGroup as $a) {
                $current_user_acls[] = $a;
            }
        }
        return [$current_user_acls, $user ?? null];
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