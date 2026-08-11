<?php

declare(strict_types=1);

class UserAclRepository extends Repository
{
    protected const array COLUMN_MAPS = [
        'acl_role' => ['id', 'role_name', 'description', 'is_system', 'priority', 'created_at', 'updated_at'],
        'acl_user_role' => ['expires_at', 'user_id'],
    ];

    public function findAll(array $conditions = [], ?int $limit = null, ?int $offset = null, array $columns = []): void
    {
        $qb = $this->em->createQueryBuilder();
        $columns = $this->getAllColumns();
        $anchor = $qb->select(
            array_merge($columns, ['0 as level']),
        )
        ->from('acl_role')
        ->innerJoin('acl_user_role')->on('id', 'acl_user_role.role_id');

        $this->applyMixedConditions($anchor, $conditions);

        $anchor->groupBy(
            'acl_role.id',
            'acl_role.role_name',
            'acl_role.description',
            'acl_role.is_system',
            'acl_role.priority',
            'acl_role.created_at',
            'acl_role.updated_at',
            'acl_user_role.expires_at',
            'acl_user_role.user_id',
        );

        $recursive = $qb->select(
            array_merge($this->getAllColumns('acl_role'), ['cte_user_roles.level + 1 as level', 'cte_user_roles.expires_at', 'cte_user_roles.user_id']),
        )
        ->from('cte_user_roles')
        ->join('acl_role_hierarchy')->on('cte_user_roles.id', 'acl_role_hierarchy.parent_role_id')
        ->join('acl_role')->on('acl_role_hierarchy.child_role_id', 'acl_role.id');

        $qb->withRecursive('cte_user_roles')
            ->body($anchor->unionAll($recursive))
            ->cycle('id')
            ->mainQuery(
                $qb->select(array_merge($this->getAllColumns('acl_role'), ['level', 'expires_at', 'user_id']))
                    ->from('cte_user_roles')
                    ->orderBy('priority DESC', 'level ASC'),
            )
            ->build();

        // $this->debugSql($qb);
    }
}