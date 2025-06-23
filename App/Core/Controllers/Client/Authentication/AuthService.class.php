<?php

declare(strict_types=1);

final class AuthService
{
    /** @var AuthService */
    private static $instance = null;

    private User|null $currentLoggedInUser = null;
    private SessionInterface $session;
    private bool $isUserFromCookie = false;
    private string $hash;
    private UserSessionModel $userSession;
    private CookieInterface $cookie;

    private function __construct(private App $app)
    {
        $this->session = $this->app->get(SessionInterface::class);
        $this->currenUserFromSessionOrCookie();
    }

    public static function getInstance() : self
    {
        if (! isset(static::$instance)) {
            static::$instance = new static(App::getInstance());
        }
        return static::$instance;
    }

    public function isUserLoggedIn() : bool
    {
        return $this->session->exists(CURRENT_USER_SESSION_NAME);
    }

    /**
     * @return User|null
     */
    public static function currentUser(): User|null
    {
        return static::getInstance()->currentLoggedInUser;
    }

    /**
     * Get the value of currentLoggedInUser.
     *
     * @return Users|null
     */
    public function getCurrentLoggedInUser(): User|null
    {
        return $this->currentLoggedInUser;
    }

    /**
     * Get the value of isUserFromCookie.
     *
     * @return bool
     */
    public function isUserFromCookie(): bool
    {
        return $this->isUserFromCookie;
    }

    /**
     * Get the value of hash.
     *
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    public function forget() : int
    {
        $result = $this->userSession->delete(['token_hash' => $this->hash]);
        $this->cookie->delete(REMEMBER_ME_COOKIE_NAME);
        return $result->rowCount();
    }

    public function getUserFromRememberCookie() : User|null
    {
        $cookie = $this->app->get(CookieInterface::class);
        if ($cookie->exists(REMEMBER_ME_COOKIE_NAME)) {
            $token_value = $cookie->get(REMEMBER_ME_COOKIE_NAME);
            $tokenHash = (new Token($token_value))->getRememberHash();
            /* @var UserSessionModel */
            $this->userSession = $this->app->get(UserSessionModel::class);
            [$user,$hasExprired] = $this->userSession->getByHash($tokenHash);
            if ($user && ! $hasExprired) {
                $this->isUserFromCookie = true;
                $this->hash = $tokenHash;
                $this->cookie = $cookie;
                return $user;
            }
        }
        return null;
    }

    private function currenUserFromSessionOrCookie() : void
    {
        $userModel = $this->app->get(UserModel::class);
        if ($this->session->exists(CURRENT_USER_SESSION_NAME)) {
            $id = $this->session->get(CURRENT_USER_SESSION_NAME);
            $this->currentLoggedInUser = $userModel->getUser($id);
            $this->currentLoggedInUser;
        } else {
            $this->currentLoggedInUser = $this->getUserFromRememberCookie();
        }
    }
}