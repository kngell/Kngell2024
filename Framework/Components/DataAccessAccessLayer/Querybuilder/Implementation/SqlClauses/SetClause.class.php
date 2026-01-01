<?php

declare(strict_types=1);

class SetClause extends SqlComponent implements RegularClauseComponentInterface, OperatorAwareInterface
{
    private const SqlClause CLAUSE = SqlClause::SET;

    private bool $hasMultiple;
    private bool $hasSingle;
    private ?QueryRulesInterface $setRule;
    private ?SqlOperator $operator = null;

    public function __construct(
        private UpdatePayload $setData,
        bool $hasMultiple,
        bool $hasSingle,
        string $method,
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
        // parent::__construct(self::CLAUSE, null, $em);
        $this->method = $method;
        $this->setData = $setData;
        $this->hasMultiple = $hasMultiple;
        $this->hasSingle = $hasSingle;
    }

    public function build(): string
    {
        if (empty($this->setData)) {
            return '';
        }
        $this->initializeConditionRule();
        $method = $this->setData->getMethod();
        $setSql = $this->setRule->getRule($this->setData->getUpdateData());

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

    private function initializeConditionRule(): void
    {
        if (!isset($this->conditionRule)) {
            if ($this->joinContext !== null) {
                $this->state->joinContext = $this->joinContext;
            }
            $registry = new SqlFactoryRegistry($this, $this->em, $this->state);
            $this->setRule = $registry->getRule($this->method, $this->setData->getUpdateData());
        }
    }
}