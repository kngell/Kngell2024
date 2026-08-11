<?php

declare(strict_types=1);

final class AclService
{
    private const CACHE_TTL = 3600;

    public function __construct(
        private AclConfigLoader $configLoader,
        private UserAclModel $aclGroup,
        private CacheInterface $cache,
    ) {
    }

    public function getUserGroups(?User $user): array
    {
        if ($user === null) {
            return ['Guest'];
        }

        $cacheKey = 'user_roles_' . $user->getUserId();

        // Try cache first
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Load from database
        $groups = ['Guest', 'LoggedIn'];
        $dbGroups = $this->aclGroup->getUserAuthorization($user);
        $groups = array_merge($groups, $dbGroups);
        $groups = array_unique($groups);

        // Cache for 1 hour
        $this->cache->set($cacheKey, $groups, self::CACHE_TTL);

        return $groups;
    }

    public function hasAccess(?User $user, string $controller, string $action): bool
    {
        $userGroups = $this->getUserGroups($user);
        $config = $this->configLoader->getConfig();
        $isControllerReferenced = false;

        foreach ($userGroups as $group) {
            if (!isset($config[$group])) {
                continue;
            }

            $groupConfig = $config[$group];

            // Check denied first (explicit deny overrides everything)
            if (isset($groupConfig['denied'][$controller])) {
                $deniedActions = $groupConfig['denied'][$controller];
                if (in_array('*', $deniedActions, true) || in_array($action, $deniedActions, true)) {
                    $isControllerReferenced = true;
                    continue;
                }
            }

            // Check allowed
            if (isset($groupConfig[$controller])) {
                $isControllerReferenced = true;
                $allowedActions = $groupConfig[$controller];

                if (in_array('*', $allowedActions, true) || in_array($action, $allowedActions, true)) {
                    return true;
                }
            }
        }

        if (!$isControllerReferenced) {
            return true;
        }

        return false;
    }

    public function filterByAccess(array $items, ?User $user, callable $extractor): array
    {
        $filtered = [];

        foreach ($items as $key => $item) {
            if (is_array($item) && isset($item['controller'])) {
                $access = $extractor($item);
                if ($this->hasAccess($user, $access['controller'], $access['action'])) {
                    $filtered[$key] = $item;
                }
            } elseif (is_array($item) && !isset($item['controller'])) {
                $filtered[$key] = $this->filterByAccess($item, $user, $extractor);
            } elseif ($item === 'separator') {
                $filtered[$key] = $item;
            } elseif (is_string($item) && !empty($item)) {
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

    public function roleHasAccess(string $role, string $controller, string $method): bool
    {
        $config = $this->configLoader->getConfig();

        if (!isset($config[$role])) {
            return false;
        }

        $roleConfig = $config[$role];

        // Check denied first
        if (isset($roleConfig['denied'][$controller])) {
            $deniedMethods = $roleConfig['denied'][$controller];
            if (in_array('*', $deniedMethods, true) || in_array($method, $deniedMethods, true)) {
                return false;
            }
        }

        // Check allowed
        if (isset($roleConfig[$controller])) {
            $allowedMethods = $roleConfig[$controller];
            if (in_array('*', $allowedMethods, true) || in_array($method, $allowedMethods, true)) {
                return true;
            }
        }

        return false;
    }

    public function clearUserCache(int $userId): void
    {
        $this->cache->delete('user_roles_' . $userId);
    }

    public function clearAllUserCache(): void
    {
        // If your cache supports pattern deletion
        if (method_exists($this->cache, 'deletePattern')) {
            $this->cache->deletePattern('user_roles_*');
        }
    }
}