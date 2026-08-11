<?php

declare(strict_types=1);

class FromClause extends SqlComponent implements RegularClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::FROM;

    private null|string $columnList = null;

    public function __construct(
        null|string|Closure $table,
        private mixed $data = null,
        ?EntityManagerInterface $em = null,
        null|string $method = null,
        null|string $customAlias = null,
        private null|BulkUpdateType $type = null,
    ) {
        parent::__construct(null, $em);
        $this->table = $table;
        $this->method = $method;
        $this->customAlias = $customAlias;
    }

    public function build(): string
    {
        if (!$this->helper) {
            throw new RuntimeException('TablesAliasHelper not initialized');
        }
        if ($this->data !== null) {
            $stop = true;
        }

        if ($this->data instanceof Closure) {
            $query = $this->type !== null ? new SqlRowClosure($this->data, $this->em, $this->method, $this->customAlias ?? 'rowTable') : new SqlClosure($this->em, $this->data);
            $this->prepareChild($query);
            $innerSql = $query->build();
            $this->mergeChildState($query);

            if (method_exists($query, 'getColumnList')) {
                $this->columnList = $query->getColumnList();
            }
            // if ($this->type == null) {
            //     $this->table = $innerSql;
            // }
        }

        list($table, $alias) = $this->helper->get(
            $this->table,
            $this->state->tableAlias,
            $this->state->aliasCheck,
        );

        if (!empty($this->customAlias)) {
            $alias = $this->customAlias;
        }
        if (isset($innerSql)) {
            $this->query = $this->getBulquery($table, $alias, $innerSql);
            return $this->query;
        }
        return $this->query = $this->getFromstatementContext($table, $alias);
    }

    public function getSqlClause(): ?SqlClause
    {
        if ($this->context === StatementType::BULK_UPDATE) {
            return null;
        }
        return self::CLAUSE;
    }

    private function getBulquery(string $table, string $alias, string $innerSql)
    {
        $columnsList = $this->columnList;

        // $tbl = $this->getFromstatementContext($table, $alias) . "($columnsList)";
        $columnsList = $columnsList === null ? '' : "($columnsList)";
        return $innerSql . ' AS ' . $alias . $columnsList;
    }

    private function getFromstatementContext(string $table, string $alias): string
    {
        $query = '';
        $statementContext = $this->context;

        if ($statementContext instanceof DeleteStatement) {
            if ($statementContext->isMariadbDialect()) {
                $query = $table . ' AS ' . $alias;
            } else {
                $query = $table;
            }
        } else {
            $query = $table . ' AS ' . $alias;
        }
        return $query;
    }
}