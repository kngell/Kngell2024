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
    protected ?string $entryMethod = null;
    protected array $queryMap = [];
    protected float $buildTimeMs = 0;

    public function __construct(protected null|SqlStatement $sqlStatement = null, protected null|EntityManagerInterface $em = null)
    {
        $this->state = new QueryState();
        $this->children = new Collection();
    }

    abstract public function build(): string;

    public function add(SqlComponent $query): void
    {
        // Base implementation - overridden in composites
    }

    public function remove(SqlComponent $query): void
    {
        // Base implementation
    }

    public function getContext(): null|StatementType
    {
        return null;
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

    public function resetState(): self
    {
        $this->state = new QueryState();
        $this->query = '';
        $this->distinct = false;
        $this->logicalLink = null;
        $this->joinContext = null;
        $this->method = null;
        if (isset($this->children)) {
            $this->children->clear();
        }
        $this->helper->reset();
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

    /**
     * @return null|SqlStatement
     */
    public function getStatement(): ?SqlStatement
    {
        return $this->sqlStatement;
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

    /**
     * @param null|EntityManagerInterface $em
     *
     * @return SqlQuery
     */
    public function setEntityManager(?EntityManagerInterface $em): SqlQuery
    {
        $this->em = $em;

        return $this;
    }

    public function prepareChild(SqlComponent $child): void
    {
        if ($this->helper && method_exists($child, 'initializeWithDependencies')) {
            $child->initializeWithDependencies($this->helper, $this->state);
        }
    }

    public function mergeChildState(SqlComponent $child): void
    {
        $this->state = $this->state->merge($child->getState());
    }

    public function setQueryMap(array $queryMap): self
    {
        $this->queryMap = $queryMap;

        return $this;
    }

    /**
     * @return array
     */
    public function getQueryMap(): array
    {
        return $this->queryMap;
    }

    public function debugSql(): SqlDebugInfo
    {
        $start = microtime(true);
        $sql = $this->query;

        // Check for precedence safely
        $hasPrecedenceIssues = false;
        foreach ($this->children->all() as $child) {
            if ($child instanceof LogicalContainerInterface && $child->hasOrOperators()) {
                $hasPrecedenceIssues = true;
                break;
            }
        }

        $duration = $this->buildTimeMs;

        return new SqlDebugInfo(
            rawSql: $sql,
            interpolatedSql: $this->formatSql($sql),
            parameters: $this->getParameters(),
            executionTimeMs: $duration,
            metadata: [
                'type' => (new ReflectionClass($this))->getShortName(),
                'precedence_logic_detected' => $hasPrecedenceIssues,
            ],
        );
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

    private function wouldCreateCircularReference(SqlComponent $component): bool
    {
        // Check if adding this component would create a circular reference
        $current = $this;
        while ($current !== null) {
            if ($current === $component) {
                return true;
            }
            $current = $current->getParent();
        }
        return false;
    }

    /**
     * Basic SQL Pretty-Printer for the debug output.
     */
    private function formatSql(string $sql): string
    {
        $keywords = ['SELECT', 'FROM', 'WHERE', 'AND', 'OR', 'GROUP BY', 'ORDER BY', 'LIMIT', 'JOIN', 'LEFT JOIN'];
        foreach ($keywords as $keyword) {
            $sql = str_replace(" $keyword ", "\n$keyword ", $sql);
        }
        return $sql;
    }
}