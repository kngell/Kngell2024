<?php

declare(strict_types=1);

class AclGroupModel extends Model
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function getUserAuthorization(User $user) : array
    {
        $aclGroup = $this->getUserAclGroup($user);
        $response = [];
        foreach ($aclGroup as $role) {
            $response[$role->group_id] = $role->name;
        }
        return $response;
    }

    private function getUserAclGroup(User $user) : array
    {
        $this->entityManager->createQueryBuilder()->select()
            ->innerjoin('acl_user_group', ['user_id', 'group_id'])
            ->on('acl_group.gr_id', 'acl_user_group.group_id')
            ->where('acl_user_group.user_id', $user->getUserId())
            ->build();
        $result = $this->entityManager->persist()->getResults();
        if ($result->getQueryResult() && $result->rowCount() > 0) {
            return $result->getResults('object')->all();
        }
        return [];
    }
}