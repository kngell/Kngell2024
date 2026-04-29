<?php

declare(strict_types=1);

class JoinClause extends SqlQuery implements RegularClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::FROM;

    public function __construct(
        ?string $customAlias,
        string|Closure $table,
        bool $withAlias,
        private SqlSelectQueryBuilderInterface|Closure|null $selectQuery = null,
        ?EntityManagerInterface $em = null,
        ?string $method = null,
    ) {
        parent::__construct(self::CLAUSE, null, $em);
        $this->withAlias = $withAlias;
        $this->table = is_string($table) ? $table : '';
        $this->customAlias = $customAlias;
        $this->table = $table;
        $this->method = $method;
    }

    public function build(): string
    {
        if (!$this->helper) {
            throw new RuntimeException('TablesAliasHelper not initialized');
        }
        $parts = [];
        if ($this->selectQuery !== null) {
            if ($this->selectQuery instanceof Closure) {
                $query = new SqlQueryClosure($this->selectQuery, $this->em, $this->method);
            } elseif ($this->selectQuery instanceof SqlComponent) {
                $query = $this->selectQuery;
            }
            $this->prepareChild($query);
            $innerSql = '(' . $query->build() . ')';
            $this->mergeChildState($query);
        }

        $tableAlias = $this->state->tableAlias;
        $aliasCheck = $this->state->aliasCheck;

        if ($this->joinContext !== null) {
            $this->helper->isJoincontext();
            $this->helper->setJoinContext($this->joinContext);
        }

        list($table, $alias) = $this->helper->get($this->table, $tableAlias, $aliasCheck);

        if (!empty($this->customAlias)) {
            $alias = $this->customAlias;
        }

        $this->state->logicalToPhysicalMap[$this->table] = $table;

        if ($this->state->statementContext === StatementType::BULK_UPDATE) {
            $table = $innerSql;
        }

        if (!empty($this->customAlias)) {
            $alias = $this->customAlias;
        }

        $this->state->tableAlias = $tableAlias;
        $this->state->aliasCheck = $aliasCheck;

        $parts[] = $table . ' AS ' . $alias;

        foreach ($this->children as $child) {
            $this->prepareChild($child);
            if (method_exists($child, 'getLogicalLink')) {
                $parts[] = $child->getLogicalLink();
            }

            $parts[] = $child->build();
            $this->mergeChildState($child);
        }

        $this->query = implode(' ', $parts);

        $this->state->joinContext = null;
        $this->helper->isJoincontext(false);
        $this->helper->setJoinContext(null);

        return $this->query;
    }

    public function getSqlClause(): ?SqlClause
    {
        if ($this->state->statementContext === StatementType::BULK_UPDATE) {
            return null;
        }
        return SqlClause::tryFrom($this->method);
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getLink(): string
    {
        $joinType = SqlJoinType::{$this->method};
        return str_replace('_', ' ', $joinType->value);
    }
}