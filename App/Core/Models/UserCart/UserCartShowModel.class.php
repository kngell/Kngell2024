<?php

declare(strict_types=1);

class UserCartShowModel extends Model
{
    public function findByUserId(int $userId): ?UserCartShow
    {
        $result = $this->one(['user_id' => $userId]);
        if ($result->exists()) {
            return $result->asClass(UserCartShow::class);
        }
        return null;
    }

    public function findBySessionId(string $sessionId): ?UserCartShow
    {
        $result = $this->one(['session_id' => $sessionId]);
        if ($result->exists()) {
            return $result->asClass(UserCartShow::class);
        }
        return null;
    }

    public function findByCartId(int $cartId): ?UserCartShow
    {
        $result = $this->one(['uc_id' => $cartId]);
        if ($result->exists()) {
            return $result->asClass(UserCartShow::class);
        }
        return null;
    }

    public function findUserCart(int $userId, string $sessionId): ?UserCartShow
    {
        $conditions = [
            'user_cart.user_id' => $userId,
            'OR', 'user_cart.session_id' => $sessionId,
        ];
        $result = $this->one($conditions, true);
        if ($result->isSuccess()) {
            return $result->asClass();
        }
        return null;
    }

    public function existsForUser(int $userId): bool
    {
        $result = $this->one(['user_id' => $userId]);
        return $result->exists();
    }

    public function existsForSession(string $sessionId): bool
    {
        $result = $this->one(['session_id' => $sessionId]);
        return $result->exists();
    }
}