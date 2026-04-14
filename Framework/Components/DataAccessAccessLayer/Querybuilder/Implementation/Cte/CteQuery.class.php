<?php

declare(strict_types=1);
class cteQuery extends SqlComponent
{
    private string $name;

    public function __construct(string $name, private SqlSelectQuery $cteBody)
    {
        $this->name = $name;
        $this->cteBody->setParent($this);
    }

    public function build(): string
    {
        $parts = [];
        $this->prepareChild($this->cteBody);
        $parts[] = $this->cteBody->build();
        $this->mergeChildState($this->cteBody);

        if ($this->cteBody->hasUnion()) {
            $unionMap = $this->cteBody->getUnionMap();
            foreach ($unionMap as $unionData) {
                $parts[] = SqlBuilderMethodRegistry::getDefaultOperator($unionData['method'])->value;
                $query = $unionData['query'];
                $this->prepareChild($query);
                $parts[] = $query->build();
                $this->mergeChildState($query);
            }
        }

        $this->query = sprintf('%s AS (%s)', $this->helper->getPhysicalTable($this->name), implode(' ', $parts));
        return $this->query;
    }
}