<?php

declare(strict_types=1);

final class AclService
{
    private array $acl = [];

    public function __construct(
        private UserAclModel $aclGroup,
        private CacheInterface $cache,
    ) {
        $this->loadAcl();
    }

    /**
     * Get all ACL groups for a user.
     */
    public function getUserGroups(?User $user): array
    {
        $groups = ['Guest'];
        // $dbGroups = $this->aclGroup->getUserAuthorization($user);
        if ($user) {
            $groups[] = 'LoggedIn';
            $dbGroups = $this->aclGroup->getUserAuthorization($user);
            $groups = array_merge($groups, $dbGroups);
        }

        return $groups;
    }

    public function filterByAccess(array $items, ?User $user, callable $extractor): array
    {
        $filtered = [];

        foreach ($items as $key => $item) {
            // Check if this is a menu item (has controller key) or a nested menu
            if (is_array($item) && isset($item['controller'])) {
                // This is a menu item with controller/action
                $access = $extractor($item);
                if ($this->hasAccess($user, $access['controller'], $access['action'])) {
                    $filtered[$key] = $item;
                }
            } elseif (is_array($item) && !isset($item['controller'])) {
                // This is a nested menu (like "Account")
                $filtered[$key] = $this->filterByAccess($item, $user, $extractor);
            } elseif ($item === 'separator') {
                // Keep separators
                $filtered[$key] = $item;
            } elseif (is_string($item) && !empty($item)) {
                // Legacy string path - try to extract info via callback
                $access = $extractor($item);
                if (is_array($access) && isset($access['controller'])) {
                    if ($this->hasAccess($user, $access['controller'], $access['action'])) {
                        $filtered[$key] = $item;
                    }
                }
            }
        }

        return $filtered;
    }

    public function hasAccess(?User $user, string $controller, string $action): bool
    {
        $userGroups = $this->getUserGroups($user);

        $isControllerReferenced = false;
        $isAllowedByAnyGroup = false;

        foreach ($userGroups as $group) {
            if (!isset($this->acl[$group])) {
                continue;
            }

            $groupConfig = $this->acl[$group];

            if (isset($groupConfig['denied'][$controller])) {
                $deniedActions = $groupConfig['denied'][$controller];
                if (in_array('*', $deniedActions) || in_array($action, $deniedActions)) {
                    $isControllerReferenced = true;
                    continue;
                }
            }

            if (isset($groupConfig[$controller])) {
                $isControllerReferenced = true;
                $allowedActions = $groupConfig[$controller];

                if (in_array('*', $allowedActions) || in_array($action, $allowedActions)) {
                    return true;
                }
            }
        }

        if (!$isControllerReferenced) {
            return true;
        }
        return $isAllowedByAnyGroup;
    }

    private function loadAcl(): void
    {
        $cacheKey = 'acl_config';
        $acl = $this->cache->get($cacheKey);

        if (!$acl) {
            $this->acl = json_decode(file_get_contents(APP . 'acl.json'), true);
            $this->cache->set($cacheKey, $this->acl, 3600);
        } else {
            $this->acl = $acl;
        }
    }
}