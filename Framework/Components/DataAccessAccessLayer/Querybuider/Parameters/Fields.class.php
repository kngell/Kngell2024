<?php

declare(strict_types=1);

class Fields extends MainQuery
{
    private array|string|null $columns;

    public function __construct(array|string|int|null ...$columns)
    {
        $this->columns = $columns;
    }

    public function getSql(): array
    {
        $columns = $this->columns;
        if (ArrayUtils::isMultidimentional($columns)) {
            $columns = ArrayUtils::flattenArrayRecursive($this->columns);
        }
        $newColumns = '(' . rtrim(implode(', ', $columns), ', ') . ')' . $this->end();
        return [
            $newColumns,
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->bind_arr,
        ];
    }

    private function end() : string
    {
        if ($this->method === 'fields') {
            return '';
        }
        if ($this->method === 'values') {
            return ',';
        }
        return '';
    }
}