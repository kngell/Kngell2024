<?php

declare(strict_types=1);

/**
 * The base Component class declares common operations for both simple and
 * complex objects of a composition.
 */
abstract class MainQuery
{
    /**
     * @var MainQuery|null
     */
    protected $parent;

    protected ?string $nestedOperator = null;
    protected bool $isNestedGroup = false;
    protected string $method;
    protected int $level = 0;
    protected MethodList $methodList;
    protected array $tableAlias = [];
    protected array $aliasCheck = [];
    protected array $parameters = [];
    protected array $bind_arr = [];
    protected string $query = '';
    protected string|null $table = null;
    protected Token $token;
    protected EntityManagerInterface $em;
    protected ?QueryType $queryType;
    protected array $tableMap = [];
    protected array $logicalToPhysicalMap = [];
    protected ?string $joinContext = null;

    /**
     * Optionally, the base Component can declare an interface for setting and
     * accessing a parent of the component in a tree structure. It can also
     * provide some default implementation for these methods.
     */
    public function setParent(?self $parent)
    {
        $this->parent = $parent;
    }

    public function getParent(): self
    {
        return $this->parent;
    }

    /**
     * In some cases, it would be beneficial to define the child-management
     * operations right in the base Component class. This way, you won't need to
     * expose any concrete component classes to the client code, even during the
     * object tree assembly. The downside is that these methods will be empty
     * for the leaf-level components.
     */
    public function add(self $component): void
    {
    }

    public function remove(self $component): void
    {
    }

    /**
     * You can provide a method that lets the client code figure out whether a
     * component can bear children.
     */
    public function isComposite(): bool
    {
        return false;
    }

    public function addAliasCheck(array $aliascheck): array
    {
        $aliasArr = [];
        foreach ($aliascheck as $key => $alias) {
            if (!in_array($alias, $this->aliasCheck)) {
                $aliasArr[] = $alias;
            }
        }
        return $aliasArr;
    }

    public function resetState(): self
    {
        $this->tableAlias = [];
        $this->aliasCheck = [];
        $this->parameters = [];
        $this->bind_arr = [];
        $this->query = '';
        return $this;
    }

    /**
     * The base Component may implement some default behavior or leave it to
     * concrete classes (by declaring the method containing the behavior as
     * "abstract").
     */
    abstract public function getSql(): array;

    /**
     * Set the value of method.
     *
     * @param string $method
     *
     * @return self
     */
    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get the value of method.
     *
     * @return string
     */
    public function getMethod(): string|null
    {
        if (isset($this->method)) {
            return $this->method;
        }
        return null;
    }

    /**
     * Get the value of tableAlias.
     *
     * @return array
     */
    public function getTableAlias(): array
    {
        return $this->tableAlias;
    }

    /**
     * Set the value of tableAlias.
     *
     * @param array $tableAlias
     *
     * @return self
     */
    public function setTableAlias(array $tableAlias): self
    {
        $this->tableAlias = $tableAlias;

        return $this;
    }

    /**
     * Get the value of aliasCheck.
     *
     * @return array
     */
    public function getAliasCheck(): array
    {
        return $this->aliasCheck;
    }

    /**
     * Set the value of aliasCheck.
     *
     * @param array $aliasCheck
     *
     * @return self
     */
    public function setAliasCheck(array $aliasCheck): self
    {
        $this->aliasCheck = $aliasCheck;

        return $this;
    }

    /**
     * Get the value of parameters.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Set the value of parameters.
     *
     * @param array $parameters
     *
     * @return self
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get the value of bind_arr.
     *
     * @return array
     */
    public function getBindArr(): array
    {
        return $this->bind_arr;
    }

    /**
     * Set the value of bind_arr.
     *
     * @param array $bind_arr
     *
     * @return self
     */
    public function setBindArr(array $bind_arr): self
    {
        $this->bind_arr = $bind_arr;

        return $this;
    }

    /**
     * Get the value of query.
     *
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * Set the value of query.
     *
     * @param string $query
     *
     * @return self
     */
    public function setQuery(string $query): self
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get the value of table.
     *
     * @return string|null
     */
    public function getTable(): string|null
    {
        return $this->table;
    }

    /**
     * Set the value of table.
     *
     * @param string|null $table
     *
     * @return self
     */
    public function setTable(string|null $table): self
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @param string $operator
     *
     * @return MainQuery
     */
    public function setNestedOperator(string $operator): MainQuery
    {
        $this->nestedOperator = $operator;
        $this->isNestedGroup = true;
        return $this;
    }

    public function getNestedOperator(): ?string
    {
        return $this->nestedOperator;
    }

    public function isNestedGroup(): bool
    {
        return $this->isNestedGroup;
    }

    /**
     * Merge parameters from another MainQuery instance
     * Useful for nested queries.
     */
    public function mergeParameters(self $other): self
    {
        $this->parameters = array_merge($this->parameters, $other->getParameters());
        $this->bind_arr = array_merge($this->bind_arr, $other->getBindArr());
        $this->tableAlias = array_merge($this->tableAlias, $other->getTableAlias());
        $this->aliasCheck = array_merge($this->aliasCheck, $other->getAliasCheck());

        return $this;
    }

    /**
     * Get the depth level in the query tree.
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Set the depth level in the query tree.
     */
    public function setLevel(int $level): self
    {
        $this->level = $level;
        return $this;
    }

    /**
     * @return array
     */
    public function getTableMap(): array
    {
        return $this->tableMap;
    }

    /**
     * @param array $tableMap
     *
     * @return MainQuery
     */
    public function setTableMap(array $tableMap): MainQuery
    {
        $this->tableMap = $tableMap;

        return $this;
    }

    /**
     * @return array
     */
    public function getLogicalToPhysicalMap(): array
    {
        return $this->logicalToPhysicalMap;
    }

    /**
     * @param array $logicalToPhysicalMap
     *
     * @return MainQuery
     */
    public function setLogicalToPhysicalMap(array $logicalToPhysicalMap): MainQuery
    {
        $this->logicalToPhysicalMap = $logicalToPhysicalMap;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getJoinContext(): ?string
    {
        return $this->joinContext;
    }

    /**
     * @param null|string $joinContext
     *
     * @return MainQuery
     */
    public function setJoinContext(?string $joinContext): MainQuery
    {
        $this->joinContext = $joinContext;

        return $this;
    }

    // protected function link(): string
    // {
    //     // If this is a nested group, don't add links - they'll be handled by the parent
    //     // if ($this->isNestedGroup && $this->parent !== null) {
    //     //     return '';
    //     // }

    //     $methodArr = $this->methodList->getMethods();
    //     $counts = array_count_values($methodArr);
    //     $stCase = Statement::getFromValue($this->method);

    //     if (!empty($stCase) && in_array($stCase->value, ['where', 'having']) && $counts[$stCase->value] === 1) {
    //         if ($this->method === 'orWhere') {
    //             return ' OR ';
    //         }
    //         $this->methodList->setWhereCondition(true);
    //         return '';
    //     }

    //     $lastKey = array_key_last($methodArr);
    //     $method = $this->method;

    //     if ($lastKey === null && empty($method)) {
    //         return '';
    //     }
    //     if ($lastKey === 0) {
    //         return '';
    //     }
    //     if (!Statement::isCondition($method)) {
    //         return '';
    //     }
    //     if (!Statement::isCondition($methodArr[$lastKey - 1])) {
    //         return '';
    //     }

    //     if (str_contains(strtolower($method), 'or')) {
    //         return ' OR ';
    //     }
    //     if (str_contains(strtolower($method), 'on')) {
    //         return 'ON';
    //     } else {
    //         return 'AND ';
    //     }
    // }
    protected function safeArrayMerge(array $array1, array $array2): array
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

    protected function link(): string
    {
        $methodArr = $this->methodList->getMethods();
        $currentMethod = $this->method;

        // If this is the first condition in a group, no link needed
        // if (empty($methodArr) || end($methodArr) === $currentMethod) {
        //     return '';
        // }

        // Determine if we're in a nested context
        $isNested = $this->parent !== null;

        // Get the statement type
        $statementType = Statement::getFromValue($currentMethod);

        if ($statementType === null) {
            return '';
        }

        switch ($statementType->value) {
            case 'where':
            case 'having':
                // For OR conditions in nested groups
                if (str_contains(strtolower($currentMethod), 'or')) {
                    return 'OR ';
                }

                // First condition in nested group doesn't need AND
                if ($isNested && count($methodArr) === 1) {
                    return '';
                }

                return 'AND ';
            case 'on':
                return 'ON ';
            default:
                return ' ';
        }
    }

    protected function statement(string $method): string
    {
        // Don't add statements for nested groups - they're handled by the parent
        if ($this->isNestedGroup && $this->parent !== null) {
            return '';
        }

        if (empty($this->method)) {
            return '';
        }

        if (!empty($method) && Statement::exists($method)) {
            $statement = Statement::getFromValue($method)->name;

            // 🎯 FIX: Handle SELECT statement properly
            // if ($this->parent->getLevel() === 0) {
            //     return strtoupper(str_replace('_', ' ', $statement)) . ' ';
            // }
            if ($statement === 'SELECT') {
                return 'SELECT ';
            }

            if ($statement === 'ON') {
                if ($this->parent->getMethod() === '') {
                    return '';
                }
            }

            if (str_ends_with(strtolower($this->method), 'join')) {
                return '';
            }

            return strtoupper(str_replace('_', ' ', $statement)) . ' ';
        }

        return '';
    }
}