<?php

declare(strict_types=1);
class QueryStatement extends MainQuery
{
    protected CollectionInterface $children;

    public function __construct()
    {
        $this->children = new Collection();
        $this->methodList = MethodList::getInstance();
    }

    public function getSql(): array
    {
        $results = [];
        /** @var MainQuery $child */
        foreach ($this->children as $child) {
            $this->method = $child->getMethod() ?? '';
            $this->methodList->setMethods($this->method);
            $child->setTableAlias($this->tableAlias);
            $child->setAliasCheck($this->aliasCheck);
            $child->setParameters($this->parameters);
            $child->setBindArr($this->bind_arr);
            list($query, $this->tableAlias, $this->aliasCheck, $this->parameters, $this->bind_arr) = $child->getSql();
            $results[] = ($this->children->last() !== $child ? $this->link() : '') . $query;
        }
        $method = $this->children->first()->getMethod();
        $statement = $this->statement($method);
        $query = $statement . rtrim(implode($this->separator($method), $results), $this->separator($method));
        $this->query = $this->sanitizeQuery($query, $statement);
        return  [
            $this->query,
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->bind_arr,
        ];
    }

    /**
     * A composite object can add or remove other components (both simple or
     * complex) to or from its child list.
     */
    public function add(MainQuery $component): void
    {
        $this->children->add($component);
        $component->level = $this->level + 1;
        $component->setParent($this);
    }

    public function remove(MainQuery $component): void
    {
        $this->children->removeByValue($component);
        $component->setParent(null);
    }

    public function isComposite(): bool
    {
        return true;
    }

    /**
     * Get the value of children.
     */
    public function getChildren()
    {
        return $this->children;
    }

    private function sanitizeQuery(string $query, string $statement) : string
    {
        return match (true) {
            trim($statement) === 'VALUES' => rtrim($query, ','),
            trim($statement) === 'DELETE' => 'DELETE',
            default => $query
        };
    }

    private function separator(string $method) : string
    {
        if (empty($this->method)) {
            return ' ';
        }
        if (in_array($method, ['select', 'orderBy'])) {
            return ', ';
        }
        return ' ';
    }
}