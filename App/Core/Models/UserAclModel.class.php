<?php

declare(strict_types=1);

class UserAclModel extends Model
{
    public function getUserAuthorization(?User $user): array
    {
        $aclGroup = $this->getUserAclGroup($user);
        $response = [];
        /** @var UserAcl $role */
        foreach ($aclGroup as $role) {
            $response[$role->getId()] = $role->getRoleName();
        }
        return $response;
    }

    private function getUserAclGroup(?User $user): array
    {
        $conditions = [
            'user_id', $user ? $user->getUserId() : 158,
            'is_active', true,
            '(', 'expires_at IS NULL', 'OR', 'expires_at', '>', 'NOW()', ')',
        ];
        return $this->all($conditions)->asClass();
    }
}