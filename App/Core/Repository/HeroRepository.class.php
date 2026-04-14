<?php

declare(strict_types=1);

class HeroRepository extends Repository
{
    public function findOneBy(array $conditions = [], array $columns = []): void
    {
        $conditions = array_merge($conditions, [
            'is_active' => true,
            ...$this->createDateRangeCondition('valid_from'),
            ...$this->createDateRangeCondition('valid_to'),
        ]);
        $conditions = $this->applyGlobalScopes($conditions);
        $qb = $this->em->createQueryBuilder();
        $query = $qb->select()->from('hero');

        $this->applyMixedConditions($query, $conditions);
        $query->orderBy('sort_order ASC', 'created_at DESC')
        ->build();

        // $this->debugSql($qb);
    }
}