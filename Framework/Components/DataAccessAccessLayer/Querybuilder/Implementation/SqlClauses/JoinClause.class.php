<?php

declare(strict_types=1);

class JoinClause extends SqlQuery implements RegularClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::FROM;

    public function __construct(
        ?string $customAlias,
        string|Closure $table,
        bool $withAlias = false,
    ) {
        parent::__construct(self::CLAUSE);
        $this->withAlias = $withAlias;
        $this->table = is_string($table) ? $table : '';
        $this->customAlias = $customAlias;
        $this->table = $table;
    }

    public function getSqlClause(): ?SqlClause
    {
        return SqlClause::tryFrom($this->method);
    }

    public function build(): string
    {
        if (!$this->helper) {
            throw new RuntimeException('TablesAliasHelper not initialized');
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

        if (!empty($this->customAlias)) {
            $alias = $this->customAlias;
        }

        $this->state->tableAlias = $tableAlias;
        $this->state->aliasCheck = $aliasCheck;
        $parts = [];

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