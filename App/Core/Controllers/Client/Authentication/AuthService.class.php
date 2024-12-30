<?php

declare(strict_types=1);

final class AuthService
{
    /** @var AuthService */
    private static $instance = null;

    private Users|null $currentLoggedInUser = null;
    private SessionInterface $session;

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
     * @return Users|null
     */
    public static function currentUser(): Users|null
    {
        return static::getInstance()->currentLoggedInUser;
    }

    /**
     * Get the value of currentLoggedInUser.
     *
     * @return Users|null
     */
    public function getCurrentLoggedInUser(): Users|null
    {
        return $this->currentLoggedInUser;
    }

    private function currenUserFromSessionOrCookie() : void
    {
        $userModel = $this->app->get(UsersModel::class);
        if ($this->session->exists(CURRENT_USER_SESSION_NAME)) {
            $id = $this->session->get(CURRENT_USER_SESSION_NAME);
            $this->currentLoggedInUser = $userModel->getUser($id);
            $this->currentLoggedInUser;
        } else {
            $this->currentLoggedInUser = $this->userFromRememberCookie();
        }
    }

    private function userFromRememberCookie() : Users|null
    {
        $cookie = $this->app->get(CookieInterface::class);
        if ($cookie->exists(REMEMBER_ME_COOKIE_NAME)) {
            $token_value = $cookie->get(REMEMBER_ME_COOKIE_NAME);
            $tokenHash = (new Token($token_value))->getRememberHash();
            /** @var Users */
            $user = $this->app->get(UserSessionModel::class)->getByHash($tokenHash);
            if ($user) {
                return $user;
            }
            return null;
        }
    }
}