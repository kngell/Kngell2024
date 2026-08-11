<?php

declare(strict_types=1);

trait SqlQueryStructureTrait
{
    public function groupBy(string ...$columns): static
    {
        $this->ensureFromExists();
        $this->queryFlow[] = 'groupBy';
        $this->groupByMap[__FUNCTION__] = $columns;
        $this->groupByMap['method'] = __FUNCTION__;
        return $this;
    }

    public function orderBy(mixed ...$columnsDirections): static
    {
        $this->ensureFromExists();
        foreach ($columnsDirections as $column) {
            if ($column instanceof SqlCaseblockBuilderInterface) {
                $column->setParent($this);
            }
        }
        $this->queryFlow[] = 'orderBy';
        $this->orderByColumns[] = $columnsDirections;
        $this->method = __FUNCTION__;
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->ensureFromExists();
        $this->queryFlow[] = 'limit';
        $this->limitMap[__FUNCTION__] = $limit;
        $this->limitMap['method'] = __FUNCTION__;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->ensureFromExists();
        $this->queryFlow[] = 'offset';
        $this->offsetMap[__FUNCTION__] = $offset;
        $this->offsetMap['method'] = __FUNCTION__;
        return $this;
    }
}
