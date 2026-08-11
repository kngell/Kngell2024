<?php

declare(strict_types=1);

final class AuthService
{
    private ?User $authenticatedUser = null;

    public function __construct(
        private SessionInterface $session,
        private CookieInterface $cookie,
        private UserModel $userModel,
        private UserSessionModel $userSession,
        private HashInterface $hash,
        private TokenInterface $token,
        private MailerInterface $mailer,
    ) {
    }

    public function login(string $email, string $password): ?User
    {
        $user = $this->userModel->getByEmail($email);

        if (!$user || !$this->hash->passwordCheck($password, $user->getPassword())) {
            return null;
        }
        $this->session->set(CURRENT_USER_SESSION_NAME, $user->getUserId());
        $this->authenticatedUser = $user;
        return $user;
    }

    public function loginById(int $userId): ?User
    {
        $user = $this->userModel->getUser($userId);
        if ($user) {
            $this->session->set(CURRENT_USER_SESSION_NAME, $userId);
            $this->authenticatedUser = $user;
        }

        return $user;
    }

    public function logout(): void
    {
        // Delete remember me token if exists
        if ($this->cookie->exists(REMEMBER_ME_COOKIE_NAME)) {
            $tokenValue = $this->cookie->get(REMEMBER_ME_COOKIE_NAME);
            $tokenHash = $this->token->getRememberHash($tokenValue);
            $this->userSession->delete(['token_hash' => $tokenHash]);
            $this->cookie->delete(REMEMBER_ME_COOKIE_NAME);
        }
        $this->session->delete(CURRENT_USER_SESSION_NAME);
        $this->authenticatedUser = null;
    }

    public function register(array $userData): User|bool
    {
        $userData['password'] = $this->hash->password($userData['password']);

        $result = $this->userModel->save($userData);
        if (!$result->isSuccess()) {
            return false;
        }
        if ($result->getSqlOperation() !== SqlStatement::INSERT) {
            return false;
        }
        $userData['user_id'] = $result->getLastInsertId();
        $user = $this->userModel->createFromArray($userData);
        // Auto-login after registration
        $this->session->set(CURRENT_USER_SESSION_NAME, $user->getUserId());
        $this->authenticatedUser = $user;

        return $user;
    }

    public function createRememberToken(User $user): string|false
    {
        $token = $this->token->generate();
        $tokenHash = $this->token->getRememberHash($token);

        $result = $this->userSession->save([
            'user_id' => $user->getUserId(),
            'token_hash' => $tokenHash,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
        ]);
        if ($result->isSuccess() && $result->getSqlOperation() === SqlStatement::INSERT) {
            return $token;
        }
        return false;
    }

    public function validateRememberMeCookie(): ?User
    {
        if (!$this->cookie->exists(REMEMBER_ME_COOKIE_NAME)) {
            return null;
        }

        $tokenValue = $this->cookie->get(REMEMBER_ME_COOKIE_NAME);
        $tokenHash = $this->token->getRememberHash($tokenValue);

        [$user, $hasExpired] = $this->userSession->getByHash($tokenHash);

        if ($user && !$hasExpired) {
            // Refresh session
            $this->session->set(CURRENT_USER_SESSION_NAME, $user->getId());
            return $user;
        }

        // Clean up expired token
        if ($hasExpired) {
            $this->userSession->delete(['token_hash' => $tokenHash]);
            $this->cookie->delete(REMEMBER_ME_COOKIE_NAME);
        }

        return null;
    }

    public function findUser(int $userId): ?User
    {
        return $this->userModel->getUser($userId);
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->userModel->getByEmail($email);
    }

    public function isLoggedIn(): bool
    {
        return $this->session->exists(CURRENT_USER_SESSION_NAME);
    }

    public function getSessionUserId(): ?int
    {
        return $this->session->get(CURRENT_USER_SESSION_NAME);
    }

    public function sendPasswordReset(string $email): bool
    {
        $user = $this->userModel->getByEmail($email);
        if (!$user) {
            return false;
        }

        $token = $this->token->generate();

        $result = $this->userModel->save([
            'user_id' => $user->getUserId(),
            'toke' => $token,
        ]);
        if (!$result->isSuccess()) {
            return false;
        }
        $link = '<a classs="reset-pw-linnk" href="/reset-pw">' . 'Please Reset your password by clicking to this link' . '</a>';
        $this->mailer
            ->subject('Password reset')
            ->from(['noReply@kngell.com'])
            ->body($link)
            ->charset('utf-8')
            ->address($user->getEmail())
            ->send();

        return true;
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $userId = $this->userModel->getByResetToken($token);

        if (!$userId) {
            return false;
        }

        $hashedPassword = $this->hash->password($newPassword);
        $result = $this->userModel->save(
            ['user_id' => $userId,
                'password' => $hashedPassword,
            ],
        );
        if (!$result->isSuccess()) {
            return false;
        }
        return true;
    }
}