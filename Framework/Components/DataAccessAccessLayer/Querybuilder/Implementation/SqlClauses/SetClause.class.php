<?php

declare(strict_types=1);

class SetClause extends SqlComponent implements RegularClauseComponentInterface, OperatorAwareInterface
{
    private const SqlClause CLAUSE = SqlClause::SET;

    private bool $hasMultiple;
    private bool $hasSingle;
    private ?QueryRulesInterface $setRule;
    private ?SqlOperator $operator = null;
    private ?BulkUpdateType $bulkUpdateType = null;

    public function __construct(
        private array $setData,
        bool $hasMultiple,
        bool $hasSingle,
        string $method,
        EntityManagerInterface $em,
        private null|string $sourceTable = null,
    ) {
        parent::__construct(em:$em);
        $this->method = $method;
        $this->setData = $setData;
        $this->hasMultiple = $hasMultiple;
        $this->hasSingle = $hasSingle;
        $this->joinContext = $sourceTable;
    }

    public function build(): string
    {
        if (empty($this->setData) && !$this->em->hasData()) {
            return '';
        }
        $this->initializeSetRule();

        $setSql = $this->setRule->getRule($this->setData);

        if (empty(trim($setSql))) {
            $this->state->hasSetContent = false;
            return '';
        }
        if ($this->setRule instanceof QueryRulesInterface && method_exists($this->setRule, 'getState')) {
            $this->state = $this->state->merge($this->setRule->getState());
        }

        return $this->query = $setSql;
    }

    public function getSqlClause(): null|SqlClause|SqlCteClause
    {
        return self::CLAUSE;
    }

    public function isComposite(): bool
    {
        return false;
    }

    public function getOperator(): SqlOperator
    {
        if ($this->operator === null) {
            $this->operator = SqlBuilderMethodRegistry::getDefaultOperator($this->method);
        }
        return $this->operator;
    }

    public function getLogicalLink(): string
    {
        return '';
    }

    /**
     * Check if this SET clause has multiple value pairs.
     */
    public function hasMultiple(): bool
    {
        return $this->hasMultiple;
    }

    /**
     * Check if this SET clause has single value pair.
     */
    public function hasSingle(): bool
    {
        return $this->hasSingle;
    }

    /**
     * @param null|BulkUpdateType $bulkUpdateType
     *
     * @return SetClause
     */
    public function setBulkUpdateType(?BulkUpdateType $bulkUpdateType): SetClause
    {
        $this->bulkUpdateType = $bulkUpdateType;
        return $this;
    }

    private function initializeSetRule(): void
    {
        if (!isset($this->conditionRule)) {
            if ($this->joinContext !== null) {
                $this->state->joinContext = $this->joinContext;
            }
            $registry = new SqlFactoryRegistry(
                $this,
                $this->em,
                $this->state,
            );

            $this->setRule = $registry->getRule($this->method, $this->setData);
        }
    }
}