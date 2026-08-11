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
            'acl_user_role.user_id', $user ? $user->getUserId() : 158,
            'acl_user_role.is_active', true,
            '(', 'acl_user_role.expires_at IS NULL', 'OR', 'acl_user_role.expires_at', '>', 'NOW()', ')',
        ];
        return $this->all($conditions)->asClass();
    }
}