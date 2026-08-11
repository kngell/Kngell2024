<?php

declare(strict_types=1);

class SelectStatement extends AbstractStatement
{
    private const StatementType TYPE = StatementType::SIMPLE_SELECT;

    public function __construct(
        private ColumnCollector $columnCollector,
        private array $selectMap = [],
        array $queryFlow = [],
        ?EntityManagerInterface $em = null,
    ) {
        parent::__construct(self::TYPE, $queryFlow, $em);
        $this->map = $selectMap;
        $this->table = $selectMap['select']['table'] ?? null;
        $this->initialize();
    }

    public function build(): string
    {
        if ($this->table === null) {
            throw new QueryBuildException('SELECT statement requires a table');
        }
        $parts = [];
        $selectClause = new SelectClause(
            columnsCollector: $this->columnCollector,
            em: $this->em,
            clauseContext: self::TYPE,
        );
        $suffix = $selectClause->getSuffix();
        if (!empty($suffix)) {
            $parts[] = $suffix;
        }
        $this->prepareChild($selectClause);
        $parts[] = $selectClause->build();
        $this->mergeChildState($selectClause);
        $parts[] = parent::build();
        $this->query = implode(' ', $parts);
        return $this->query;
    }

    #[Override]
    protected function buildSpecificClause(string $clause): void
    {
        $category = SqlMethodCategory::tryFrom($clause);

        if ($category === null) {
            return;
        }

        match($category) {
            SqlMethodCategory::FROM => $this->buildFromGroup(),
            SqlMethodCategory::WHERE => $this->buildWhereClauseFromMap(),
            SqlMethodCategory::GROUP_BY => $this->buildGroupBy(),
            SqlMethodCategory::HAVING => $this->buildWhereClauseFromMap($this->selectMap['having'] ?? []),
            SqlMethodCategory::ORDER_BY => $this->buildOrderBy(),
            SqlMethodCategory::LIMIT => $this->buildLimit(),
            SqlMethodCategory::OFFSET => $this->buildOffset(),
            default => null
        };
    }

    private function buildFromGroup(): void
    {
        $builder = new FromGroupBuilder(
            em: $this->em,
            context: self::TYPE,
            map: $this->selectMap,
            table: $this->table,
            method: $this->selectMap['select']['method'] ?? null,
            customAlias: $this->selectMap['select']['customAlias'] ?? null,
            data: $this->selectMap['select']['data'] ?? null,
            joinMap: $this->selectMap['join'],
        );
        $this->add($builder->getFromGroup());
    }

    private function buildGroupBy(): void
    {
        $groupByClause = new GroupByClause(
            em: $this->em,
            groupByConfig: $this->selectMap['group_by'] ?? [],
            table: $this->table,
        );
        $this->add($groupByClause);
    }

    private function buildOrderBy(): void
    {
        $orderByClause = new OrderByClause(
            orderByColumns: $this->selectMap['order_by'] ?? [],
            table: $this->table,
        );
        $this->add($orderByClause);
    }

    private function buildLimit(): void
    {
        if (!isset($this->selectMap['limit'])) {
            return;
        }

        $limitClause = new LimitClause(
            em: $this->em,
            limitConfig: $this->selectMap['limit'],
        );
        $this->add($limitClause);
    }

    private function buildOffset(): void
    {
        if (!isset($this->selectMap['offset'])) {
            return;
        }
        $offsetClause = new OffsetClause(
            em: $this->em,
            offsetConfig: $this->selectMap['offset'],
        );
        $this->add($offsetClause);
    }
}