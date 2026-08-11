<?php

declare(strict_types=1);

class FooterMenuShowRepository extends AbstractFooterRepository
{
    protected const array COLUMN_MAPS = [
        'footer_menu_column' => [
            'id', 'column_key', 'title', 'sort_order', 'is_active', 'created_at', 'updated_at',
        ],
        'footer_menu_link' => [
            'id', 'column_id', 'title', 'sort_order', 'is_active', 'url', 'link_target',
        ],
    ];

    public function findBy(array $conditions = [], ?int $limit = null, ?int $offset = null, array $columns = []): void
    {
        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);

        if ($mode === ConditionListMode::MODE_FRONTEND->value) {
            $this->executeFrontendQuery($baseConditions, $columns, $limit, $offset);
            return;
        }

        if ($mode === ConditionListMode::MODE_ADMIN->value) {
            $this->executeAdminQuery($baseConditions, $columns, $limit, $offset);
            return;
        }

        parent::findBy($baseConditions, $limit, $offset, $columns);
    }

    public function findOneBy(array $conditions = [], array $columns = []): void
    {
        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);

        if ($mode === ConditionListMode::MODE_FRONTEND->value) {
            $this->executeFrontendQuery($baseConditions, $columns);
            return;
        }

        parent::findOneBy($baseConditions);
    }

    private function getJoinCondition(SqlSelectQueryBuilderInterface $query, bool $frontend = true): SqlSelectQueryBuilderInterface
    {
        $query = $query->leftJoin('footer_menu_link')
            ->on('id', 'footer_menu_link.column_id');

        if ($frontend) {
            $query = $query
                ->onValue('footer_menu_link.is_active', true)
                ->onValue('footer_menu_link.valid_from is null')
                ->orOnValue('footer_menu_link.valid_from <= NOW()')
                ->onValue('footer_menu_link.valid_to is null')
                ->orOnValue('footer_menu_link.valid_to >= NOW()');
        }

        return $query;
    }

    private function executeFrontendQuery(array $conditions, array $columns = [], ?int $limit = null, ?int $offset = null): void
    {
        $qb = $this->em->createQueryBuilder();
        $query = $qb->selectWithAlias($this->getAllColumns())
            ->from('footer_menu_column');

        $query = $this->getJoinCondition($query, true);

        $caseConditions = $this->extractCaseConditions($conditions);
        $this->applyMixedConditions($query, $conditions);

        if (!empty($caseConditions)) {
            $query = $query->orderBy(
                $qb->case()->when($caseConditions)->then(0)->else(1)->end(),
                'ASC',
            );
        }

        $query->orderBy(
            'sort_order ASC',
            'created_at DESC',
            'footer_menu_link.sort_order ASC',
        );

        if ($limit !== null) {
            $query->limit($limit);
        }
        if ($offset !== null) {
            $query->offset($offset);
        }

        $query->build();
    }

    private function executeAdminQuery(array $conditions, array $columns = [], ?int $limit = null, ?int $offset = null): void
    {
        $qb = $this->em->createQueryBuilder();
        $query = $qb->selectWithAlias($this->getAllColumns())
            ->from('footer_menu_column');

        $query = $this->getJoinCondition($query, false);

        $caseConditions = $this->extractCaseConditions($conditions);
        $this->applyMixedConditions($query, $conditions);

        if (!empty($caseConditions)) {
            $query = $query->orderBy(
                $qb->case()->when($caseConditions)->then(0)->else(1)->end(),
                'ASC',
            );
        }

        $query->orderBy('title ASC', 'sort_order ASC');

        if ($limit !== null) {
            $query->limit($limit);
        }
        if ($offset !== null) {
            $query->offset($offset);
        }

        $query->build();
    }
}