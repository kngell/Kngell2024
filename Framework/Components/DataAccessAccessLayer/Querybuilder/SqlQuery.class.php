<?php

declare(strict_types=1);

abstract class SqlQuery extends SqlComponent
{
    protected null|SqlClause|SqlCteClause $sqlClause;
    protected null|ClauseBuilderInterface $clauseBuilder;
    protected null|FlowValidatorInterface $flowValidator;
    protected null|DataStandardizerInterface $standardizer;
    protected null|ClauseStandardizerFactory $clauseStandardizer;
    protected array $queryFlow = [];
    protected bool $isClosure = false;
    protected null|string|closure $currentTable = null;
    protected array $clauseStandardiserArray = [];

    public function __construct(
        null|SqlClause|SqlCteClause $sqlClause,
        null|SqlStatement $SqlStatement = null,
        null|EntityManagerInterface $em = null,
    ) {
        parent::__construct($SqlStatement, $em);
        $this->sqlClause = $sqlClause;
    }

    public function build(): string
    {
        $start = microtime(true);
        $result = [];
        $previousClause = null;
        $whereClauseFound = false;

        foreach ($this->children as $child) {
            $currentClause = null;

            if ($child instanceof SqlStatementInterface) {
                $currentClause = $child->getStatement();
            } elseif ($child instanceof ClauseComponentInterface) {
                $currentClause = $child->getSqlClause();
            }

            if ($currentClause !== null && $currentClause !== $previousClause) {
                $this->addClauseKeyword($child, $currentClause, $result);
                $previousClause = $currentClause;
            }

            $this->prepareChild($child);
            $childQuery = $child->build();

            if (!empty($childQuery)) {
                if (($child instanceof ConditionGroup || $child instanceof ConditionClause) &&
                    $child->getSqlClause() === SqlClause::WHERE) {
                    if (!$whereClauseFound) {
                        $result[] = str_replace('_', ' ', SqlClause::WHERE->name);
                        $whereClauseFound = true;
                    } else {
                        $result[] = $child->getLogicalLink() ?? '';
                    }
                    $result[] = $childQuery;
                } else {
                    $result[] = $childQuery;
                }
            }

            $this->mergeChildState($child);
        }

        $this->query = implode(' ', array_filter($result));

        $this->buildTimeMs = (microtime(true) - $start) * 1000;
        return $this->query;
    }

    public function add(SqlComponent $component): void
    {
        $this->children->add($component);
        $component->setParent($this);
    }

    public function remove(SqlComponent $component): void
    {
        $this->children->removeByValue($component);
    }

    public function isComposite(): bool
    {
        return true;
    }

    /**
     * @return null|SqlClause|SqlCteClause
     */
    public function getSqlClause(): null|SqlClause|SqlCteClause
    {
        return $this->sqlClause;
    }

    public function getQueryFlow(): array
    {
        return $this->queryFlow;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    public function execute(): array
    {
        throw new Exception('Not implemented');
    }

    protected function validateQueryFlow(): void
    {
    }

    protected function initializeComponents(): void
    {
        if (!empty($initialParameters)) {
            $this->state = new QueryState(parameters: $initialParameters);
        }

        $registry = new SqlFactoryRegistry($this, $this->em, $this->state);
        $this->clauseBuilder = $registry->getClauseBuilder($this->sqlStatement);
        $this->flowValidator = $registry->getFlowValidator($this->sqlStatement);
        $this->standardizer = $registry->getStandardizer($this->sqlStatement);
        $this->clauseStandardizer = new ClauseStandardizerFactory();
    }

    protected function getClauseStandardizer(string $method): ?DataStandardizerInterface
    {
        // Map the method to its clause context
        $clause = SqlBuilderMethodRegistry::getClauseContext($method)?->value; // Fixed: $value not $valeu

        if (!$clause) {
            return null;
        }

        // Cache by clause (WHERE, FROM, ON, etc.)
        if (!array_key_exists($clause, $this->clauseStandardiserArray)) {
            $this->clauseStandardiserArray[$clause] = $this->clauseStandardizer?->create($method);
        }

        $instance = $this->clauseStandardiserArray[$clause];

        // Set the specific method context for this call
        if ($instance) {
            $instance->setMethod($method);
        }

        return $instance;
    }

    protected function resolveMainTable(): string
    {
        $entity = $this->em->getEntity();
        if ($entity instanceof Entity) {
            return $entity->table();
        }
        if ($entity instanceof CollectionInterface) {
            return $entity->first()->table();
        }

        throw new RuntimeException('Unable to resolve main table');
    }

    protected function getUniqueTableName(string $method, string $table, array $map): array
    {
        $logicalName = $table;
        if (in_array($table, $map)) {
            $counter = 1;
            do {
                $logicalName = $table . '_logical_' . $counter;
                $counter++;
            } while (in_array($logicalName, $map) && $counter < 100);
        }

        $key = $method . '|' . $logicalName;
        return [$logicalName, $key];
    }

    private function addClauseKeyword(
        SqlComponent $child,
        SqlClause|SqlStatement|SqlCteClause $clause,
        array &$result,
    ): void {
        if ($clause instanceof SqlCteClause) {
            $clauseKeyword = $clause->value;
        } elseif ($clause instanceof SqlClause) {
            $clauseKeyword = str_replace('_', ' ', $clause->name);

            if (method_exists($child, 'getPrefix')) {
                $clauseKeyword = $child->getPrefix() . $clauseKeyword;
            }
            if (method_exists($child, 'getSuffix')) {
                $clauseKeyword = $clauseKeyword . $child->getSuffix();
            }
        } else {
            $clauseKeyword = $clause->name;
        }

        if (!($clause instanceof SqlClause && $clause === SqlClause::WHERE)) {
            $result[] = $clauseKeyword;
        }
    }
}