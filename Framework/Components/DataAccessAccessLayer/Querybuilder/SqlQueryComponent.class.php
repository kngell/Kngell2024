<?php

declare(strict_types=1);

abstract class SqlQueryComponent
{
    use QueryBuilderGettersAndSettersTrait;

    protected CollectionInterface $children;
    protected ?SqlQueryComponent $parent = null;
    protected QueryState $state; // Single source of truth for state
    protected string $query = '';
    protected ?TablesAliasHelper $helper = null;
    protected ?ParameterManager $parameterManager = null;
    protected array $parameterManagerArray = [];
    protected ?string $table = null;
    protected bool $withAlias = false;
    protected ?string $customAlias = null;
    protected ?string $method = null;
    protected ?string $joinContext = null;
    protected ?string $logicalLink = null;

    public function __construct()
    {
        $this->state = new QueryState();
        $this->parameterManager = new ParameterManager();
        $this->children = new Collection();
    }

    public function add(SqlQueryComponent $query): void
    {
        // Base implementation - overridden in composites
    }

    public function remove(SqlQuery $query): void
    {
        // Base implementation
    }

    public function isComposite(): bool
    {
        return false;
    }

    abstract public function build(): string;

    public function resetState(): self
    {
        $this->state = new QueryState(); // Reset to empty state
        $this->query = '';
        return $this;
    }

    public function setLogicalLink(?string $link): void
    {
        $this->logicalLink = $link;
    }

    // Getters for state properties (maintain compatibility)
    public function getTableAlias(): array
    {
        return $this->state->tableAlias;
    }

    public function getParameters(): array
    {
        return $this->state->parameters;
    }

    public function getBindArr(): array
    {
        return $this->state->bindArr;
    }

    public function getAliasCheck(): array
    {
        return $this->state->aliasCheck;
    }

    public function getLogicalToPhysicalMap(): array
    {
        return $this->state->logicalToPhysicalMap;
    }

    public function setParameterManager(ParameterManager $parameterManager): void
    {
        $this->parameterManager = $parameterManager;
    }

    public function getParameterManager(): ParameterManager
    {
        if ($this->parameterManager === null) {
            // Create one only if parent didn't provide one
            $this->parameterManager = new ParameterManager();
        }
        return $this->parameterManager;
    }

    public function getTables(): array
    {
        return $this->state->tables;
    }

    public function getState(): QueryState
    {
        return $this->state;
    }

    /**
     * @return null|string
     */
    public function getLogicalLink(): ?string
    {
        return $this->logicalLink;
    }

    /**
     * @return null|ConditionRuleInterface
     */
    public function getRules(): ?QueryRulesInterface
    {
        return null;
    }

    public function initializeWithDependencies(
        TablesAliasHelper $helper,
        QueryState $initialState,
    ): void {
        $this->helper = $helper;
        $this->state = $initialState;
    }

    /**
     * @return null|SqlClause|SqlStatementType
     */
    public function getSqlClause(): null|SqlClause|SqlStatementType
    {
        return null;
    }

    protected function prepareChild(SqlQueryComponent $child): void
    {
        if ($this->helper && method_exists($child, 'initializeWithDependencies')) {
            // Pass the current immutable state snapshot to the child
            $child->initializeWithDependencies($this->helper, $this->state);
        }
        // $conditionRule = $child->getConditionRule();
        // if ($conditionRule !== null && method_exists($conditionRule, 'initialize')) {
        //     // Pass the state's parameters to condition rule
        //     $conditionRule->initialize($this->state);
        // }
    }

    protected function mergeChildState(SqlQueryComponent $child): void
    {
        // Merge the child's result state into the parent's state, resulting in a NEW state object
        $this->state = $this->state->merge($child->getState());
    }

    protected function addParameters(): void
    {
        // Base implementation
    }

    protected function addAliasCheck(array $aliascheck): array
    {
        $aliasArr = [];
        foreach ($aliascheck as $key => $alias) {
            if (!in_array($alias, $this->state->aliasCheck)) {
                $aliasArr[] = $alias;
            }
        }
        return $aliasArr;
    }
}