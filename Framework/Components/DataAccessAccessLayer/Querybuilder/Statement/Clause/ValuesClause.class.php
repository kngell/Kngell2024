<?php

declare(strict_types=1);

class ValuesClause extends SqlQueryComponent implements ClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::VALUES;

    private ?QueryRulesInterface $valuesRule;

    public function __construct(private EntityManagerInterface $em, private mixed $insertData)
    {
    }

    public function build(): string
    {
        $this->initializeValuesRule();
        $valuesSql = $this->valuesRule->getRule((array) $this->insertData);

        if (empty($valuesSql)) {
            throw new QueryFlowException('No values provided for INSERT');
        }

        return "VALUES $valuesSql";
    }

    public function getSqlClause(): SqlClause
    {
        return self::CLAUSE;
    }

    private function initializeValuesRule(): void
    {
        if (!isset($this->valuesRule)) {
            $registry = new FactoryRegistry($this, $this->em, $this->state);
            $this->valuesRule = $registry->getRule($this->method, $this->insertData);
        }
    }
}