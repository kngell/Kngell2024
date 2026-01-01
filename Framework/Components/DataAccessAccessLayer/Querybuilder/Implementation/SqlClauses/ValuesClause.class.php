<?php

declare(strict_types=1);

class ValuesClause extends SqlComponent implements RegularClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::VALUES;

    private ?QueryRulesInterface $valuesRule;

    public function __construct(
        private EntityManagerInterface $em,
        private ProcessedInsertData $processedData,
        null|string $method,
    ) {
        $this->method = $method;
    }

    public function build(): string
    {
        if (!$this->processedData->hasData()) {
            throw new QueryFlowException('No values data available for INSERT');
        }

        $this->initializeValuesRule();

        $this->query = $this->valuesRule->getRule($this->processedData->getData());

        return $this->query;
    }

    public function getSqlClause(): SqlClause
    {
        return self::CLAUSE;
    }

    private function initializeValuesRule(): void
    {
        if (!isset($this->valuesRule)) {
            $registry = new SqlFactoryRegistry($this, $this->em, $this->state);
            $this->valuesRule = $registry->getRule($this->method, $this->processedData->getData());
        }
    }
}