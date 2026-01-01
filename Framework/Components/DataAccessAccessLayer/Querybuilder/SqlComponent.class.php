<?php

declare(strict_types=1);

abstract class SqlComponent
{
    use QueryBuilderGettersAndSettersTrait;

    protected CollectionInterface $children;
    protected ?SqlComponent $parent = null;
    protected QueryState $state;
    protected string $query = '';
    protected ?TablesAliasHelper $helper = null;
    protected null|string|Closure $table = null;
    protected bool $withAlias = false;
    protected bool $distinct = false;
    protected ?string $customAlias = null;
    protected ?string $method = null;
    protected ?string $joinContext = null;
    protected ?string $logicalLink = null;

    public function __construct()
    {
        $this->state = new QueryState();
        $this->children = new Collection();
    }

    public function add(SqlComponent $query): void
    {
        // Base implementation - overridden in composites
    }

    public function remove(SqlComponent $query): void
    {
        // Base implementation
    }

    public function getPrefix(): string
    {
        return '';
    }

    public function getSuffix(): string
    {
        return '';
    }

    public function isComposite(): bool
    {
        return false;
    }

    abstract public function build(): string;

    public function resetState(): self
    {
        $this->state = new QueryState();
        $this->query = '';
        return $this;
    }

    public function setLogicalLink(?string $link): void
    {
        $this->logicalLink = $link;
    }

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

    public function getTables(): array
    {
        return $this->state->tables;
    }

    public function getState(): QueryState
    {
        return $this->state;
    }

    /**
     * @return null|SqlStatementType
     */
    public function getSqlStatementType(): ?SqlStatementType
    {
        return null;
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
     * @return null|SqlClause|SqlCteClause
     */
    public function getSqlClause(): null|SqlClause|SqlCteClause
    {
        return null;
    }

    protected function prepareChild(SqlComponent $child): void
    {
        if ($this->helper && method_exists($child, 'initializeWithDependencies')) {
            $child->initializeWithDependencies($this->helper, $this->state);
        }
    }

    protected function mergeChildState(SqlComponent $child): void
    {
        $this->state = $this->state->merge($child->getState());
    }

    protected function addParameters(): void
    {
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