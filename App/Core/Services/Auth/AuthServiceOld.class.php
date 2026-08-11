<?php

declare(strict_types=1);

final class AuthServiceOld
{
    private User|null $currentLoggedInUser = null;
    private bool $isUserFromCookie = false;
    private string $hash;

    public function __construct(
        private SessionInterface $session,
        private CookieInterface $cookie,
        private UserModel $userModel,
        private UserSessionModel $userSession,
    ) {
    }

    public function isUserLoggedIn(): bool
    {
        return $this->session->exists(CURRENT_USER_SESSION_NAME);
    }

    /**
     * @return User|null
     */
    public function getCurrentLoggedInUser(): ?User
    {
        if ($this->currentLoggedInUser === null) {
            $this->loadCurrentUser();
        }
        return $this->currentLoggedInUser;
    }

    /**
     * @return bool
     */
    public function isUserFromCookie(): bool
    {
        return $this->isUserFromCookie;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    public function forget(): int
    {
        $result = $this->userSession->delete(['token_hash' => $this->hash]);
        $this->cookie->delete(REMEMBER_ME_COOKIE_NAME);
        return $result->getRowCount();
    }

    public function getUserFromRememberCookie(): User|null
    {
        if ($this->cookie->exists(REMEMBER_ME_COOKIE_NAME)) {
            $token_value = $this->cookie->get(REMEMBER_ME_COOKIE_NAME);
            $tokenHash = (new Token($token_value))->getRememberHash();
            [$user, $hasExprired] = $this->userSession->getByHash($tokenHash);
            if ($user && !$hasExprired) {
                $this->isUserFromCookie = true;
                $this->hash = $tokenHash;
                return $user;
            }
        }
        return null;
    }

    public function currentUser(): ?User
    {
        return $this->getCurrentLoggedInUser();
    }

    private function loadCurrentUser(): void
    {
        // Load user from session or cookie
        if ($this->session->exists(CURRENT_USER_SESSION_NAME)) {
            $id = $this->session->get(CURRENT_USER_SESSION_NAME);
            // For testing: $id = 158;
            $this->currentLoggedInUser = $this->userModel->getUser($id);
        } else {
            $this->currentLoggedInUser = $this->getUserFromRememberCookie();
        }
    }
}