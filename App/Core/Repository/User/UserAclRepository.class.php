<?php

declare(strict_types=1);

class UserAclRepository extends Repository
{
    protected const array COLUMN_MAPS = [
        'acl_role' => ['id', 'role_name', 'priority'],
        'acl_user_role' => ['expires_at'],
    ];

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function findAll(array $conditions = [], array $columns = []): void
    {
        $qb = $this->em->createQueryBuilder();
        $columns = $this->getAllColumns();
        $anchor = $qb->select(
            array_merge($columns, [
                'acl_user_role.0 as level',
            ]),
        )->from('acl_user_role')->join('acl_role')->on('role_id', 'id');
        $this->applyMixedConditions($anchor, $conditions);
        $qb->withRecursive('cte_user_roles')
        ->body(
            $anchor->unionAll(
                $qb->select(array_merge($this->getAllColumns(), ['cte_user_roles.level + 1 as level']))
                    ->from('cte_user_roles')
                    ->join('acl_role_hierarchy')->on('id', 'parent_role_id')
                    ->join('acl_role')->on('acl_role_hierarchy.child_role_id', 'id'),
            ),
        )->cycle('id')
        ->mainQuery(
            $qb->select(array_merge($this->getAllColumns(), ['level as level']))
            ->from('cte_user_roles')
            ->orderBy('priority DESC', 'level ASC'),
        )->build();
        // $this->debugSql($qb);
    }
}