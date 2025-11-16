<?php

declare(strict_types=1);

class TableParameter extends SqlQueryComponent
{
    public function __construct(
        private string $table,
        private array $columns,
    ) {
    }

    public function build(): string
    {
        list($table, $alias) = $this->helper->get($this->table, $this->tableAlias, $this->aliasCheck);
        if (!empty($this->customAlias)) {
            $alias = $this->customAlias;
        }
        $this->tables[$table] = $this->columns;
        $this->query = $this->table . ' AS ' . $alias;
        return $this->query;
    }
}