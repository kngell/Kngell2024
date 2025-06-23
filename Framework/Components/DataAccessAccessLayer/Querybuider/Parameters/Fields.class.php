<?php

declare(strict_types=1);

class Fields extends MainQuery
{
    private array|string|null $columns;

    public function __construct(EntityManagerInterface $em, array|string|int|null ...$columns)
    {
        $this->columns = $columns;
        $this->em = $em;
    }

    public function getSql(): array
    {
        $columns = $this->columns();
        $newColumns = '(' . rtrim(implode(', ', $columns), ', ') . ')' . $this->end();
        return [
            $newColumns,
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->bind_arr,
        ];
    }

    private function columns() : array
    {
        $columns = $this->columns;
        if (ArrayUtils::isMultidimentional($columns)) {
            $columns = ArrayUtils::flattenArrayRecursive($this->columns);
        }
        $columnsDbleCheck = ArrayUtils::first($columns);
        if (is_array($columnsDbleCheck) && empty($columnsDbleCheck)) {
            $properties = $this->em->getEntityProperties();
            if ($this->method === 'fields') {
                return array_keys($properties);
            }
        }
        return $columns;
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