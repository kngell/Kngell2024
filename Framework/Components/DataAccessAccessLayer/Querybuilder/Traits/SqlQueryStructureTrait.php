<?php

declare(strict_types=1);

trait SqlQueryStructureTrait
{
    public function groupBy(string ...$columns): self
    {
        $this->queryFlow['groupBy'] = true;
        $this->groupByMap[__FUNCTION__] = $columns;
        $this->groupByMap['method'] = __FUNCTION__;
        return $this;
    }

    public function orderBy(string ...$columnsDirections): self
    {
        $this->queryFlow['orderBy'] = true;
        $this->orderByColumns = $columnsDirections;
        $this->method = __FUNCTION__;
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->queryFlow['limit'] = true;
        $this->limitMap[__FUNCTION__] = $limit;
        $this->limitMap['method'] = __FUNCTION__;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->queryFlow['offset'] = true;
        $this->offsetMap[__FUNCTION__] = $offset;
        $this->offsetMap['method'] = __FUNCTION__;
        return $this;
    }
}