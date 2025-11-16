<?php

declare(strict_types=1);

abstract class SqlQueryComponentOLD
{
    use QueryBuilderGettersAndSettersTrait;

    protected ?SqlQueryComponent $parent = null;
    protected array $tableAlias = [];
    protected array $aliasCheck = [];
    protected array $parameters = [];
    protected array $bindArr = [];
    protected array $logicalToPhysicalMap = [];
    protected array $tables = [];
    protected ?string $table = null;
    protected string $query = '';
    protected ?string $joinContext = null;
    protected string $method = '';
    protected bool $withAlias = false;
    protected ?string $customAlias = '';
    protected ?TablesAliasHelper $helper = null;
    protected ?ParameterManager $parameterManager = null;

    /** @var ParameterManager[] */
    protected array $parameterManagerArray = [];

    protected QueryState $state;

    public function __construct()
    {
        $this->state = new QueryState();
    }

    public function add(SqlQueryComponent $query): void
    {
    }

    public function remove(SqlQuery $query): void
    {
    }

    public function isComposite(): bool
    {
        return false;
    }

    abstract public function build(): string;

    public function resetState(): self
    {
        $this->tableAlias = [];
        $this->aliasCheck = [];
        $this->parameters = [];
        $this->bindArr = [];
        $this->query = '';
        return $this;
    }

    public function initializeWithDependencies(
        TablesAliasHelper $helper,
        array $initialState = [],
    ): void {
        $this->helper = $helper;

        // Merge initial state
        foreach ($initialState as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }

    protected function addParameters(): void
    {
    }

    // protected function prepareChild(SqlQueryComponent $child): void
    // {
    //     if ($this->helper && method_exists($child, 'initializeWithDependencies')) {
    //         $child->initializeWithDependencies($this->helper, [
    //             'tableAlias' => $this->tableAlias,
    //             'aliasCheck' => $this->aliasCheck,
    //             'parameters' => $this->parameters,
    //             'bindArr' => $this->bindArr,
    //             'logicalToPhysicalMap' => $this->logicalToPhysicalMap,
    //             'tables' => $this->tables,
    //             'joinContext' => $this->joinContext,
    //             // 'method' => $this->method,
    //             'withAlias' => $this->withAlias,
    //         ]);
    //     }
    // }
    protected function prepareChild(SqlQueryComponent $child): void
    {
        if ($this->helper && method_exists($child, 'initializeWithDependencies')) {
            $child->initializeWithDependencies($this->helper, $this->state);
        }
    }

    protected function mergeChildState(SqlQueryComponent $child): void
    {
        // $this->tableAlias = $this->safeArrayMerge($this->tableAlias, $child->getTableAlias());
        // $this->logicalToPhysicalMap = $this->safeArrayMerge($this->logicalToPhysicalMap, $child->getLogicalToPhysicalMap());
        // $this->aliasCheck = $this->safeArrayMerge($this->aliasCheck, $child->getAliasCheck());
        // $this->parameters = $this->safeArrayMerge($this->parameters, $child->getParameters());
        // $this->bindArr = $this->safeArrayMerge($this->bindArr, $child->getBindArr());
        // $this->tables = $this->safeArrayMerge($this->tables, $child->getTables());
        $this->state = $this->state->merge($child->getState());
        $parameterManager = $child->getParameterManager();
        if (null !== $parameterManager) {
            $this->parameterManagerArray[] = $parameterManager;
        }
    }

    protected function addAliasCheck(array $aliascheck): array
    {
        $aliasArr = [];
        foreach ($aliascheck as $key => $alias) {
            if (!in_array($alias, $this->aliasCheck)) {
                $aliasArr[] = $alias;
            }
        }
        return $aliasArr;
    }

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
}