<?php

declare(strict_types=1);

abstract class SqlQuery extends SqlQueryComponent
{
    protected CollectionInterface $children;
    protected null|SqlClause|SqlStatementType $sqlClause;
    protected null|ClauseBuilderInterface $clauseBuilder;
    protected null|FlowValidatorInterface $flowValidator;
    protected array $queryFlow = [];

    public function __construct(
        null|SqlClause|SqlStatementType $sqlClause,
        array $initialParameters = [],
    ) {
        parent::__construct();
        $this->sqlClause = $sqlClause;
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
                $clauseKeyword = str_replace('_', ' ', $currentClause->name);

                // CRITICAL ARCHITECTURAL CHANGE:
                // We defer adding the WHERE keyword until we know the group is not empty.
                if ($currentClause !== SqlClause::WHERE) {
                    $result[] = $clauseKeyword;
                }
                $previousClause = $currentClause;
            }

            $this->prepareChild($child);

            $childQuery = $child->build();

            if (!empty($childQuery)) {
                if (($child instanceof ConditionGroup || $child instanceof ConditionClause) && $child->getSqlClause() === SqlClause::WHERE) {
                    if (!$whereClauseFound) {
                        $result[] = str_replace('_', ' ', SqlClause::WHERE->name);
                        $whereClauseFound = true;
                    } else {
                        $result[] = $child->getLogicalLink() ?? '';
                    }
                    // Add the condition group's result (which is already wrapped internally)
                    $result[] = $childQuery;
                } else {
                    // Add other clauses (FROM table, column names, JOIN conditions)
                    $result[] = $childQuery;
                }
            }

            $this->mergeChildState($child);
        }

        // The old global precedence logic is entirely removed here.
        // We rely 100% on ConditionGroup to wrap itself.

        $this->query = implode(' ', array_filter($result));
        return $this->query;
    }

    public function add(SqlQueryComponent $component): void
    {
        $this->children->add($component);
        $component->setParent($this);
    }

    public function isComposite(): bool
    {
        return true;
    }

    /**
     * @return null|SqlClause|SqlStatementType
     */
    public function getSqlClause(): null|SqlClause|SqlStatementType
    {
        return $this->sqlClause;
    }

    public function getQueryFlow(): array
    {
        return $this->queryFlow;
    }

    protected function validateQueryFlow(): void
    {
    }

    protected function initializeComponents(): void
    {
        if (!empty($initialParameters)) {
            $this->state = new QueryState(parameters: $initialParameters);
        }
        if ($this->sqlClause instanceof SqlStatementType) {
            $this->clauseBuilder = (new ClauseBuilderFactory($this))->create($this->sqlClause);
            $this->flowValidator = (new FlowValidatorFactory($this))->create($this->sqlClause);
        }
    }

    protected function resolveMainTable(Entity $entity): string
    {
        if ($entity instanceof Entity) {
            return $entity->table();
        }
        if ($entity instanceof CollectionInterface) {
            return $entity->first()->table();
        }

        throw new RuntimeException('Unable to resolve main table');
    }
}