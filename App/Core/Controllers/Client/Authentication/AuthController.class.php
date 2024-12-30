<?php

declare(strict_types=1);

abstract class AuthController extends Controller
{
    protected SessionInterface $session;

    public function __construct(protected UsersModel $users, protected UserSessionModel $userSession, protected CookieInterface $cookie, protected HashInterface $hash)
    {
    }

    protected function authenticate(array $userData) : Users|bool
    {
        $user = $this->users->getByEmail($userData['email']);
        if ($user && $this->hash->passwordCheck($userData['password'], $user->getPassword())) {
            return $user;
        }
        return false;
    }

    protected function loginUser(Users $user, array $userData = []) : bool
    {
        try {
            if (! $this->session->exists(CURRENT_USER_SESSION_NAME)) {
                $this->session->regenerate();
                $this->session->set(CURRENT_USER_SESSION_NAME, $user->getUserId());
                $this->flash->add('You have successfully logged In');
            }
            if (array_key_exists('remember_me', $userData)) {
                list($result, $token) = $this->userSession->rememberLogin($user, $this->cookie);
                if ($result->getQueryResult() && $result->getLastInsertId()) {
                    if ($this->cookie->exists(REMEMBER_ME_COOKIE_NAME)) {
                        $old_cookie = $this->cookie->get(REMEMBER_ME_COOKIE_NAME);
                        $this->cookie->delete(REMEMBER_ME_COOKIE_NAME);
                        $r = $this->userSession->delete(['token_hash' => $old_cookie]);
                    }
                    $this->cookie->set($token, REMEMBER_ME_COOKIE_NAME);
                }
            }
            return true;
        } catch (Throwable $th) {
            return false;
        }
    }
}