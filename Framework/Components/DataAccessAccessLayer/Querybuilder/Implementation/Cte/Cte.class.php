<?php

declare(strict_types=1);
class Cte extends SqlComponent
{
    private string $name;
    private SqlSelectQuery $internalQuery;

    public function __construct(string $name, SqlSelectQuery $internalQuery)
    {
        $this->name = $name;
        $this->internalQuery = $internalQuery;
    }

    public function build(): string
    {
        $parts = [];
        $this->prepareChild($this->internalQuery);
        $parts[] = $this->internalQuery->build();
        $this->mergeChildState($this->internalQuery);

        if ($this->internalQuery->hasUnion()) {
            $unionMap = $this->internalQuery->getUnionMap();
            foreach ($unionMap as $unionData) {
                $parts[] = SqlBuilderMethodRegistry::getDefaultOperator($unionData['method'])->value;
                $query = $unionData['query'];
                $this->prepareChild($query);
                $parts[] = $query->build();
                $this->mergeChildState($query);
            }
        }

        $this->query = sprintf('%s AS (%s)', $this->name, implode(' ', $parts));
        return $this->query;
    }
}