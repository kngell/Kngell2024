<?php

declare(strict_types=1);

class QueryState
{
    public function __construct(
        public array $tableAlias = [],
        public array $aliasCheck = [],
        public array $parameters = [],
        public array $logicalToPhysicalMap = [],
        public array $tables = [],
        public ?string $joinContext = null,
        public bool $withAlias = false,
        public bool $distinct = false,
        public bool $isSubquery = false,
        public ?string $subqueryMainTable = null,
        public bool $isUpdate = false,
        public bool $hasSetContent = true,
        public ?StatementType $statementContext = null,
    ) {
    }

    public function merge(self $other): self
    {
        return new self(
            tableAlias: $this->safeArrayMerge($this->tableAlias, $other->tableAlias),
            aliasCheck: $this->safeArrayMerge($this->aliasCheck, $this->addAliasCheck($other->aliasCheck)),
            parameters: $this->safeArrayMerge($this->parameters, $other->parameters),
            logicalToPhysicalMap: $this->safeArrayMerge($this->logicalToPhysicalMap, $other->logicalToPhysicalMap),
            tables: $this->safeArrayMerge($this->tables, $other->tables),
            joinContext: $other->joinContext ?? $this->joinContext,
            withAlias: $other->withAlias || $this->withAlias,
            isSubquery: $other->isSubquery ?? $this->isSubquery,
            subqueryMainTable: $other->subqueryMainTable ?? $this->subqueryMainTable,
            isUpdate: $other->isUpdate ?? $this->isUpdate,
            hasSetContent: $other->hasSetContent && $this->hasSetContent,
            statementContext: $other->statementContext ?? $this->statementContext,
        );
    }

    /**
     * Add a parameter with unique name generation.
     */
    public function addParameter(string $baseName, mixed $value): string
    {
        $parameterName = $this->generateUniqueParameterName($baseName);
        $this->parameters[$parameterName] = $value;
        return $parameterName;
    }

    public function withLogicalToPhysicalMap(array $logicalToPhysicalMap): self
    {
        $new = clone $this;
        $new->logicalToPhysicalMap = $logicalToPhysicalMap;
        return $new;
    }

    public function withJoinContext(?string $joinContext): self
    {
        $new = clone $this;
        $new->joinContext = $joinContext;
        return $new;
    }

    public function withTableAlias(array $tableAlias): self
    {
        $new = clone $this;
        $new->tableAlias = $tableAlias;
        return $new;
    }

    public function isInitialized(): bool
    {
        return $this->tableAlias !== [] ||
               $this->aliasCheck !== [] ||
               $this->parameters !== [] ||
               $this->logicalToPhysicalMap !== [];
    }

    /**
     * Creates a new state instance with the updated withAlias flag.
     *
     * @param bool $withAlias
     *
     * @return QueryState
     */
    public function withAlias(bool $withAlias): QueryState
    {
        $new = clone $this;
        $new->withAlias = $withAlias;

        return $new;
    }

    /**
     * @param bool $distinct
     *
     * @return QueryState
     */
    public function distinct(bool $distinct): QueryState
    {
        $this->distinct = $distinct;

        return $this;
    }

    /**
     * Generate a unique parameter name.
     */
    private function generateUniqueParameterName(string $baseName): string
    {
        $counter = 1;
        $name = $this->normalizeParameterName($baseName);

        while (array_key_exists($name, $this->parameters)) {
            $name = $this->normalizeParameterName($baseName . '_' . $counter);
            $counter++;
        }

        return $name;
    }

    private function normalizeParameterName(string $name): string
    {
        return 'p_' . preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($name));
    }

    private function safeArrayMerge(array $array1, array $array2): array
    {
        // Ensure both are arrays
        $array1 = is_array($array1) ? $array1 : [];
        $array2 = is_array($array2) ? $array2 : [];

        if (empty($array2)) {
            return $array1;
        }

        // Check for potential issues
        $totalSize = count($array1) + count($array2);
        if ($totalSize > 10000) {
            throw new RuntimeException(
                "Large array merge detected: {$totalSize} elements. " .
                'Array1: ' . count($array1) . ' elements, ' .
                'Array2: ' . count($array2) . ' elements',
            );
        }

        try {
            return array_merge($array1, $array2);
        } catch (Throwable $e) {
            // Fallback: manual merge
            error_log('Array merge failed, using fallback: ' . $e->getMessage());
            foreach ($array2 as $key => $value) {
                $array1[$key] = $value;
            }
            return $array1;
        }
    }

    private function addAliasCheck(array $aliascheck): array
    {
        $aliasArr = [];
        foreach ($aliascheck as $key => $alias) {
            if (!in_array($alias, $this->aliasCheck)) {
                $aliasArr[] = $alias;
            }
        }
        return $aliasArr;
    }
}