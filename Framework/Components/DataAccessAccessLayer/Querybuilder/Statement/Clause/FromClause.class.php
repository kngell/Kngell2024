<?php

declare(strict_types=1);

class FromClause extends SqlQueryComponent implements ClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::FROM;

    public function __construct(
        string $table,
        private array $columns,
    ) {
        parent::__construct();
        $this->table = $table;
    }

    public function build(): string
    {
        if (!$this->helper) {
            throw new RuntimeException('TablesAliasHelper not initialized');
        }

        list($table, $alias) = $this->helper->get($this->table, $this->state->tableAlias, $this->state->aliasCheck);

        if (!empty($this->customAlias)) {
            $alias = $this->customAlias;
        }

        $this->state->tables[$table] = $this->columns;
        $this->query = $table . ' AS ' . $alias;
        return $this->query;
    }

    public function getSqlClause(): ?SqlClause
    {
        return self::CLAUSE;
    }
}