<?php

declare(strict_types=1);

abstract class AuthController extends Controller
{
    public function __construct(protected UserModel $user, protected UserSessionModel $userSession, protected CookieInterface $cookie, protected HashInterface $hash)
    {
    }

    protected function authenticate(array $userData) : User|bool
    {
        $user = $this->user->getByEmail($userData['email']);
        if ($user && $this->hash->passwordCheck($userData['password'], $user->getPassword()) && $user->isActive()) {
            return $user;
        }
        return false;
    }

    protected function loginUser(User $user, array $userData = []) : bool
    {
        try {
            if (! $this->session->exists(CURRENT_USER_SESSION_NAME)) {
                $this->loginUserInTheSession($user);
            }
            $this->deleteOldTokenHash();
            if (array_key_exists('remember_me', $userData)) {
                $this->setNewTokenHash($user);
            }
            return true;
        } catch (Throwable $th) {
            return false;
        }
    }

    protected function setNewTokenHash(User $user) : void
    {
        list($result, $token) = $this->userSession->rememberLogin($user, $this->cookie);
        if ($result->getQueryResult() && $result->getLastInsertId()) {
            $this->cookie->set($token, REMEMBER_ME_COOKIE_NAME);
        }
    }

    protected function deleteOldTokenHash() : QueryResult|null
    {
        if ($this->cookie->exists(REMEMBER_ME_COOKIE_NAME)) {
            $old_cookie = $this->cookie->get(REMEMBER_ME_COOKIE_NAME);
            $this->cookie->delete(REMEMBER_ME_COOKIE_NAME);
            return $this->userSession->delete(['token_hash' => (new Token($old_cookie))->getRememberHash()]);
        }
        return null;
    }

    private function loginUserInTheSession(User $user) : void
    {
        $this->session->regenerate();
        $this->session->set(CURRENT_USER_SESSION_NAME, $user->getUserId());
        $this->flash->add('You have successfully logged In');
    }
}