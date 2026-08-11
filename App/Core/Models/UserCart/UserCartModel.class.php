<?php

declare(strict_types=1);

class UserCartModel extends Model
{
    public function findByUserId(int $userId): ?UserCart
    {
        $result = $this->one(['user_id' => $userId]);
        if ($result->exists()) {
            return $result->asClass(UserCart::class);
        }
        return null;
    }

    public function findBySessionId(string $sessionId): ?UserCart
    {
        $result = $this->one(['session_id' => $sessionId]);
        if ($result->exists()) {
            return $result->asClass(UserCart::class);
        }
        return null;
    }

    public function findByCartId(int $cartId): ?UserCart
    {
        $result = $this->one(['uc_id' => $cartId]);
        if ($result->exists()) {
            return $result->asClass(UserCart::class);
        }
        return null;
    }

    public function findUserCart(int $userId, string $sessionId): ?UserCart
    {
        $cart = $this->findByUserId($userId);
        if ($cart) {
            return $cart;
        }
        return $this->findBySessionId($sessionId);
    }

    public function deleteCartById(int $cartId): QueryResult
    {
        return $this->delete(['uc_id' => $cartId]);
    }
}