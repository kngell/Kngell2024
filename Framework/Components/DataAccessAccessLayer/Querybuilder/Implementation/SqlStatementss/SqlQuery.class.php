<?php

declare(strict_types=1);

abstract class SqlQuery extends SqlComponent
{
    protected CollectionInterface $children;
    protected null|EntityManagerInterface $em;
    protected null|SqlStatementType $sqlStatementType = null;
    protected null|SqlClause|SqlCteClause $sqlClause;
    protected null|ClauseBuilderInterface $clauseBuilder;
    protected null|FlowValidatorInterface $flowValidator;
    protected null|DataStandardizerInterface $standardizer;
    protected array $queryFlow = [];
    protected array $queryMap = [];

    public function __construct(
        null|SqlClause|SqlCteClause $sqlClause,
        null|SqlStatementType $sqlStatementType = null,
        null|EntityManagerInterface $em = null,
    ) {
        parent::__construct();
        $this->sqlClause = $sqlClause;
        $this->sqlStatementType = $sqlStatementType;
        $this->em = $em;
    }

    public function build(): string
    {
        $result = [];
        $previousClause = null;
        $whereClauseFound = false;

        foreach ($this->children as $child) {
            $currentClause = null;

            if ($child instanceof ClauseComponentInterface) {
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

    /**
     * @return null|SqlStatementType
     */
    public function getSqlStatementType(): ?SqlStatementType
    {
        return $this->sqlStatementType;
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
        $this->clauseBuilder = $registry->getClauseBuilder($this->sqlStatementType);
        $this->flowValidator = $registry->getFlowValidator($this->sqlStatementType);
        $this->standardizer = $registry->getStandardizer($this->sqlStatementType);
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
        $key = $table;
        $logicalName = $table;

        if (array_key_exists($key, $map)) {
            $logicalName = $table . '_logical_';
            $key = $method . '|' . $logicalName;

            $counter = 1;
            do {
                $logicalName = $table . '_' . $counter;
                $key = $method . '|' . $logicalName;
                $counter++;
            } while (array_key_exists($key, $map) && $counter < 100);
        } else {
            $key = $method . '|' . $table;
        }

        return [$logicalName, $key];
    }

    private function addClauseKeyword(
        SqlComponent $child,
        SqlClause|SqlStatementType|SqlCteClause $clause,
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
            // SqlStatementType - shouldn't happen for clauses
            return;
        }

        if (!($clause instanceof SqlClause && $clause === SqlClause::WHERE)) {
            $result[] = $clauseKeyword;
        }
    }
}