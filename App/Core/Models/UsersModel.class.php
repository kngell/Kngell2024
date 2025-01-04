<?php

declare(strict_types=1);

class UserModel extends Model
{
    private const int RESET_PW_EXPIRY = 60 * 60 * 2;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function getUser(string|int $id) : User|bool
    {
        $user = $this->find($id);
        if ($user->getQueryResult() && $user->count() === 0) {
            return false;
        }
        return $user->getResults('class')->single();
    }

    public function getByEmail(string $email) : User|bool
    {
        $user = $this->all(['email' => $email]);
        if ($user->getQueryResult() && $user->count() === 0) {
            return false;
        }
        return $user->getResults('class')->single();
    }

    public function saveRegisteredUser(array $data, TokenInterface $token) : QueryResult
    {
        $data = array_merge($data, [
            'activation_hash' => $token->getRememberHash(),
        ]);
        return parent::save($data);
    }

    public function updatUser(array $data, HashInterface $hash) : QueryResult
    {
        if (array_key_exists('password', $data)) {
            $data['password'] = $hash->password($data['password']);
        }
        $data['user_id'] = AuthService::currentUser()->getUserId();
        return parent::save($data);
    }

    public function processPasswordResetRequest(TokenInterface $token, array $userData) : array|bool
    {
        $user = $this->getByEmail($userData['email']);
        if ($user) {
            $user_token = $token->getValue();
            $result = $this->save([
                'password_reset_hash' => $token->getRememberHash(),
                'user_id' => $user->getUserId(),
                'paswword_reset_expiry' => date('Y-m-d H:i:s', time() + self::RESET_PW_EXPIRY),
            ]);
            if ($result->getQueryResult() && $result->rowCount()) {
                return ['token' => $user_token, 'user' => $user];
            }
        }
        return false;
    }

    public function getUserByResetPw(string $token) : User|null
    {
        $token = new Token($token);
        $token_hash = $token->getRememberHash();
        $this->entityManager->createQueryBuilder()->select()
            ->where('password_reset_hash', $token_hash)
            ->build();
        $results = $this->entityManager->persist()->getResults();
        if (! $results->getQueryResult() && $results->rowCount()) {
            return null;
        }
        /** @var User */
        $user = $results->getResults('class')->single();
        if ($user) {
            if (strtotime($user->getPaswwordResetExpiry()) > time()) {
                return $user;
            }
        }
        return null;
    }

    public function resetPassword(User $user, array $userData, HashInterface $hash) : bool
    {
        $result = $this->save([
            'user_id' => $user->getUserId(),
            'password' => $hash->password($userData['password']),
            'password_reset_hash' => null,
            'paswword_reset_expiry' => null,
        ]);
        if ($result->getQueryResult() && $result->rowCount()) {
            return true;
        }
    }

    public function activateAccount(string $token) : bool
    {
        $hash_token = (new Token($token))->getRememberHash();
        $this->entityManager->createQueryBuilder()->update()
            ->set('active', 1, 'activation_hash', null)
            ->where('activation_hash', $hash_token)
            ->build();
        $result = $this->entityManager->persist()->getResults();
        if (! $result->rowCount()) {
            return false;
        }
        return true;
    }
}