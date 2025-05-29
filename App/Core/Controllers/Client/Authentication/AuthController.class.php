<?php

declare(strict_types=1);

abstract class AuthController extends Controller
{
    public function __construct(
        protected UserModel $user,
        protected UserSessionModel $userSession,
        protected CookieInterface $cookie,
        protected HashInterface $hash,
        protected LoginAttemptsModel $loginAttempts
    ) {
        $this->currentModel($user);
    }

    protected function isUserAuthenticated(array $userData) : bool
    {
        $user = $this->user->loginAttempts($userData['email']);
        if ($user) {
            if ($user->getLoginAttempts() > MAX_LOGIN_ATTEMPTS) {
                $this->flash->add('You have reach the maximum login attempts. Please try later', FlashType::WARNING);
                return false;
            }
            if (! $this->hash->passwordCheck($userData['password'], $user->getPassword())) {
                $this->flash->add('Incorrect email or password! Please try again or create a new account.', FlashType::WARNING);
                $this->addUserLoginAttempt($user);
                return false;
            }
            if (! $user->isActive()) {
                $this->flash->add('Login failed! Please activate your account and try again', FlashType::WARNING);
                $this->addUserLoginAttempt($user);
                return false;
            }
            if (! $this->loginUser($user, $userData)) {
                $this->flash->add('Login failed! Please try again', FlashType::WARNING);
                $this->addUserLoginAttempt($user);
                return false;
            } else {
                $this->flash->add('Login successful!', FlashType::SUCCESS);
                $this->removeUserLoginAttempt($user);
                return true;
            }
        } else {
            $this->flash->add('You do not have an account to login! Please create a new account', FlashType::WARNING);
            return false;
        }
    }

    protected function getRedirectUrl() : string|null
    {
        if ($this->session->exists('current_url')) {
            $previousUrl = $this->session->get('current_url');
            $this->session->delete('current_url');
            return $previousUrl;
        }
        return $this->session->get('previous_url');
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

    protected function addUserLoginAttempt(User $user) : void
    {
        $userAttemptModel = $this->loginAttempts->save(
            [
                'user_id' => $user->getUserId(),
                'timestamp' => time(),
                'ipAddress' => $this->request->getServer()->get('remote_addr'),
            ]
        );
    }

    protected function removeUserLoginAttempt(User $user) : void
    {
        $userAttemptModel = $this->loginAttempts->delete(
            [
                'user_id' => $user->getUserId(),

            ]
        );
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