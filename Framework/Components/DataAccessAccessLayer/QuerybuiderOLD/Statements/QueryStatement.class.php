<?php

declare(strict_types=1);
class QueryStatement extends MainQuery
{
    protected CollectionInterface $children;

    public function __construct(QueryType $type)
    {
        $this->children = new Collection();
        $this->methodList = MethodList::getInstance();
        $this->queryType = $type;
    }

    public function getSql(): array
    {
        $results = [];
        $nestedGroups = [];

        /** @var MainQuery $child */
        foreach ($this->children as $child) {
            $this->method = $child->getMethod() ?? '';
            $this->methodList->setMethods($this->method);
            // Set state from parent to chil
            $child->setTableAlias($this->tableAlias);
            $child->setAliasCheck($this->aliasCheck);
            $child->setParameters($this->parameters);
            $child->setBindArr($this->bind_arr);
            $child->setLogicalToPhysicalMap($this->logicalToPhysicalMap);

            list($query, $childTableAlias, $childAliasCheck, $childParameters, $childBindArr) = $child->getSql();

            // Safely merge child state back to parent
            $this->tableAlias = $this->safeArrayMerge($this->tableAlias, $childTableAlias ?? []);
            $this->logicalToPhysicalMap = $this->safeArrayMerge($this->logicalToPhysicalMap, $child->getLogicalToPhysicalMap() ?? []);
            $this->debugLargeArrays($this->aliasCheck, $childAliasCheck ?? [], 'aliasCheck in getSql');
            $this->aliasCheck = $this->safeArrayMerge($this->aliasCheck, $this->addAliasCheck($childAliasCheck));
            $this->parameters = $this->safeArrayMerge($this->parameters, $childParameters ?? []);
            $this->bind_arr = $this->safeArrayMerge($this->bind_arr, $childBindArr ?? []);

            // Handle nested condition groups
            if ($child->isNestedGroup()) {
                $nestedGroups[] = [
                    'operator' => $child->getNestedOperator() ?? 'AND',
                    'query' => $query,
                ];
            } else {
                $results[] = $this->formatChildQuery($child, $query);
            }
        }

        // Process nested groups with proper operators
        foreach ($nestedGroups as $group) {
            $results[] = $group['operator'] . ' (' . $group['query'] . ')';
        }

        $method = $this->children->first()?->getMethod() ?? '';
        $statement = $this->statement($method);
        $query = $statement . rtrim(implode($this->separator($method), $results), $this->separator($method));
        $this->query = $this->sanitizeQuery($query, $statement);

        return [
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
        $component->setLevel($this->level + 1);
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

    private function debugLargeArrays(array $array1, array $array2, string $context): void
    {
        $totalSize = count($array1) + count($array2);

        if ($totalSize > 1000) {
            error_log("=== LARGE ARRAY DEBUG: {$context} ===");
            error_log("Total elements: {$totalSize}");
            error_log('Array1 size: ' . count($array1));
            error_log('Array2 size: ' . count($array2));

            // Sample some keys to see what's being stored
            $sample1 = array_slice($array1, 0, 10, true);
            $sample2 = array_slice($array2, 0, 10, true);

            error_log('Array1 sample keys: ' . implode(', ', array_keys($sample1)));
            error_log('Array2 sample keys: ' . implode(', ', array_keys($sample2)));

            // Check for duplicate keys
            $duplicateKeys = array_intersect_key($array1, $array2);
            error_log('Duplicate keys count: ' . count($duplicateKeys));

            error_log('=== END DEBUG ===');
        }
    }

    private function formatChildQuery(MainQuery $child, string $query): string
    {
        $link = $this->link();

        // Don't add links for the first child or for nested groups
        if ($child === $this->children->first() || $child->isNestedGroup()) {
            return $query;
        }

        return $link . $query;
    }

    private function sanitizeQuery(string $query, string $statement): string
    {
        return match (true) {
            trim($statement) === 'VALUES' => rtrim($query, ','),
            trim($statement) === 'DELETE' => 'DELETE',
            default => $query
        };
    }

    private function separator(string $method): string
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