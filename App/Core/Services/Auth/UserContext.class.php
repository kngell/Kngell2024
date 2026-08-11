<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class UserContext
{
    private ?User $currentUser = null;
    private bool $loaded = false;

    public function __construct(
        private AuthService $authService,
        private AclService $aclService,
        private PermissionService $permissionService,
        // private UserCartItemService $userCartService,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function currentUser(): ?User
    {
        if (!$this->loaded) {
            $this->loadCurrentUser();
        }
        return $this->currentUser;
    }

    public function getUserId(): ?int
    {
        return $this->currentUser()?->getUserId();
    }

    public function isLoggedIn(): bool
    {
        return $this->currentUser() !== null;
    }

    public function getRoles(): array
    {
        return $this->aclService->getUserGroups($this->currentUser());
    }

    public function hasRole(string $role): bool
    {
        $roles = $this->getRoles();
        return in_array($role, $roles, true);
    }

    public function hasAccess(string $controller, string $method): bool
    {
        return $this->aclService->hasAccess($this->currentUser(), $controller, $method);
    }

    public function hasPermission(string $permission): bool
    {
        $user = $this->currentUser();
        if (!$user) {
            return false;
        }
        return $this->permissionService->hasPermission($user, $permission);
    }

    public function getPermissions(): array
    {
        $user = $this->currentUser();
        if (!$user) {
            return $this->permissionService->getRolePermissions('Guest');
        }
        return $this->permissionService->getUserPermissions($user);
    }

    public function filterMenu(array $menu, callable $extractor): array
    {
        return $this->aclService->filterByAccess($menu, $this->currentUser(), $extractor);
    }

    public function getEmail(): ?string
    {
        return $this->currentUser()?->getEmail();
    }

    public function getDisplayName(): ?string
    {
        $user = $this->currentUser();
        return $user?->getDisplayName() ?? $user?->getUsername() ?? 'Guest';
    }

    public function getPreferences(): array
    {
        return $this->currentUser()?->getPreferences() ?? [];
    }

    public function getPreference(string $key, mixed $default = null): mixed
    {
        $preferences = $this->getPreferences();
        return $preferences[$key] ?? $default;
    }

    public function clear(): void
    {
        $this->currentUser = null;
        $this->loaded = false;
    }

    public function refresh(): void
    {
        $userId = $this->getUserId();
        if ($userId) {
            $this->currentUser = $this->authService->findUser($userId);
            $this->aclService->clearUserCache($userId);
        }
    }

    private function loadCurrentUser(): void
    {
        $this->loaded = true;
        $userId = 158; //$this->authService->getSessionUserId();

        if ($userId) {
            $this->currentUser = $this->authService->findUser($userId);
            if ($this->currentUser) {
                $this->logger?->debug('User loaded from session', ['userId' => $userId]);
                return;
            }
        }

        $this->currentUser = $this->authService->validateRememberMeCookie();

        if ($this->currentUser) {
            $this->logger?->debug('User loaded from remember me cookie', [
                'userId' => $this->currentUser->getUserId(),
            ]);
            return;
        }

        $this->logger?->debug('No authenticated user found, using Guest');
    }
}