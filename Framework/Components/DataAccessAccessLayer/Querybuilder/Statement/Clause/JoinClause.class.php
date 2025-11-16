<?php

declare(strict_types=1);

class JoinClause extends SqlQuery implements ClauseComponentInterface
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

        // Build the JOIN table part
        list($table, $alias) = $this->helper->get($this->table, $tableAlias, $aliasCheck);

        if (!empty($this->customAlias)) {
            $alias = $this->customAlias;
        }

        $this->state->tableAlias = $tableAlias;
        $this->state->aliasCheck = $aliasCheck;

        $joinPart = $table . ' AS ' . $alias;

        $onPart = '';
        foreach ($this->children as $child) {
            $this->prepareChild($child);
            $onPart = ' ON ' . $child->build();
            $this->mergeChildState($child);
        }

        $this->query = $joinPart . $onPart;
        return $this->query;
    }

    // public function build(): string
    // {
    //     if (!$this->helper) {
    //         throw new RuntimeException('TablesAliasHelper not initialized');
    //     }

    //     list($table, $alias) = $this->helper->get($this->table, $this->tableAlias, $this->aliasCheck);

    //     if (!empty($this->customAlias)) {
    //         $alias = $this->customAlias;
    //     }

    //     return $table . ' AS ' . $alias;
    // }

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