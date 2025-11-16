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
            return '';
        }

        $childrenSql = parent::build();

        // Update state from condition rule
        if ($this->valuesRule instanceof QueryRulesInterface && method_exists($this->valuesRule, 'getState')) {
            $this->state = $this->state->merge($this->valuesRule->getState());
        }

        return $this->combineWithChildren($valuesSql, $childrenSql);
        // return $this->applyConditionParentheses($result);
    }

    public function getSqlClause(): SqlClause
    {
        return self::CLAUSE;
    }

    private function initializeValuesRule(): void
    {
        if (!isset($this->valuesRule)) {
            $factory = new RulesFactory(
                $this->em,
                $this->state,
            );

            $this->valuesRule = $factory->create($this->method, $this->insertData);
        }
    }

    private function combineWithChildren(string $conditionSql, string $childrenSql): string
    {
        if (empty($childrenSql)) {
            return $conditionSql;
        }

        // If we have both condition and children, combine with AND
        return "{$conditionSql} AND {$childrenSql}";
    }
}