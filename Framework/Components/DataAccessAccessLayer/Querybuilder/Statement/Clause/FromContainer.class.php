<?php

declare(strict_types=1);

/**
 * FROM Container - Manages both simple tables and joins within your component architecture.
 */
class FromContainer extends SqlQueryComponent implements ClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::FROM;

    private ?FromComponentInterface $from = null;
    private array $joins = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function build(): string
    {
        $parts = [];

        // Build main FROM table
        if ($this->mainTable) {
            $this->prepareChild($this->mainTable);
            $parts[] = $this->mainTable->build();
            $this->mergeChildState($this->mainTable);
        }

        // Build JOINs
        foreach ($this->joins as $join) {
            $this->prepareChild($join);
            $parts[] = $join->build();
            $this->mergeChildState($join);
        }

        return implode(' ', $parts);
    }

    public function getSqlClause(): SqlClause
    {
        return self::CLAUSE;
    }

    public function from(string $table, ?string $alias = null): self
    {
        $this->mainTable = new FromClause($table, [], $alias);
        $this->addChild($this->mainTable);
        return $this;
    }

    public function join(SqlJoinType $joinType, string $table, ?string $alias = null): JoinClause
    {
        $join = new JoinClause($joinType, $table, $alias);
        $this->joins[] = $join;
        $this->addChild($join);
        return $join;
    }

    public function innerJoin(string $table, ?string $alias = null): JoinClause
    {
        return $this->join(SqlJoinType::INNER, $table, $alias);
    }

    public function leftJoin(string $table, ?string $alias = null): JoinClause
    {
        return $this->join(SqlJoinType::LEFT, $table, $alias);
    }

    public function rightJoin(string $table, ?string $alias = null): JoinClause
    {
        return $this->join(SqlJoinType::RIGHT, $table, $alias);
    }

    public function fullJoin(string $table, ?string $alias = null): JoinClause
    {
        return $this->join(SqlJoinType::FULL, $table, $alias);
    }

    public function crossJoin(string $table, ?string $alias = null): JoinClause
    {
        return $this->join(SqlJoinType::CROSS, $table, $alias);
    }

    public function getMainTable(): ?FromComponentInterface
    {
        return $this->mainTable;
    }

    public function getJoins(): array
    {
        return $this->joins;
    }

    public function hasJoins(): bool
    {
        return !empty($this->joins);
    }
}