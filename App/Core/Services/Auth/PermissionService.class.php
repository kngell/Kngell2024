<?php

declare(strict_types=1);

final class PermissionService
{
    public function __construct(
        private AclService $aclService,
        private AclConfigLoader $configLoader,
        private CacheInterface $cache,
    ) {
    }

    public function getUserPermissions(User $user): array
    {
        $cacheKey = 'user_permissions_' . $user->getUserId();
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        $userGroups = $this->aclService->getUserGroups($user);
        $config = $this->configLoader->getConfig();
        $permissions = [];

        foreach ($userGroups as $role) {
            if (isset($config[$role])) {
                $roleConfig = $config[$role];

                foreach ($roleConfig as $controller => $methods) {
                    if ($controller === 'denied') {
                        continue;
                    }

                    if (!isset($permissions[$controller])) {
                        $permissions[$controller] = [];
                    }

                    if ($methods === ['*']) {
                        $permissions[$controller] = ['*'];
                    } else {
                        $permissions[$controller] = array_merge(
                            $permissions[$controller] ?? [],
                            $methods,
                        );
                    }
                }
            }
        }

        // Apply denied rules
        foreach ($userGroups as $role) {
            if (isset($config[$role]['denied'])) {
                foreach ($config[$role]['denied'] as $controller => $methods) {
                    if ($methods === ['*']) {
                        unset($permissions[$controller]);
                    } else {
                        if (isset($permissions[$controller])) {
                            $permissions[$controller] = array_diff(
                                $permissions[$controller],
                                $methods,
                            );
                            if (empty($permissions[$controller])) {
                                unset($permissions[$controller]);
                            }
                        }
                    }
                }
            }
        }

        $this->cache->set($cacheKey, $permissions, 3600);
        return $permissions;
    }

    public function getRolePermissions(string $role): array
    {
        $config = $this->configLoader->getRoleConfig($role);
        unset($config['denied']);
        return $config;
    }

    public function hasPermission(User $user, string $permission): bool
    {
        // Permission format: "Controller@action" or "Controller.*"
        [$controller, $action] = explode('@', $permission, 2);
        return $this->aclService->hasAccess($user, $controller, $action);
    }

    public function getAllowedControllers(User $user): array
    {
        $permissions = $this->getUserPermissions($user);
        return array_keys($permissions);
    }

    public function clearUserCache(int $userId): void
    {
        $this->cache->delete('user_permissions_' . $userId);
    }
}