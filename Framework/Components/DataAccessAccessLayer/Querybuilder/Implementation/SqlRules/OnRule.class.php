<?php

declare(strict_types=1);

class OnRule extends WhereRule
{
    public function __construct(
        private mixed $conditions,
        EntityManagerInterface $em,
        string $method,
        QueryState $state,
        ConditionNormalizer $conditionNormalizer,
        ?string $customAlias = null,
    ) {
        parent::__construct($conditions, $em, $method, $state, $conditionNormalizer, $customAlias);
    }

    public function getRule(array $conditions): string
    {
        foreach ($conditions as $condition) {
            if ($condition instanceof Closure) {
                throw new InvalidArgumentException(
                    'Closures are not supported in ON clauses',
                );
            }
        }
        return parent::getRule($conditions);
    }

    protected function buildRightSide(array $condition, TablesAliasHelper $tableHelper): string
    {
        $rawValue = $condition['right'];

        // For column references (most common in ON)
        if ($this->looksLikeColumnReference($rawValue)) {
            return $this->buildColumnReference($rawValue, $tableHelper);
        }

        // For BETWEEN with column references
        if (is_array($rawValue) && $this->isBetweenOperator($condition['operator'] ?? '')) {
            return $this->buildBetweenColumns($rawValue, $tableHelper);
        }

        // Fall back to parent for everything else
        // (parameters, literals, subqueries)
        return parent::buildRightSide($condition, $tableHelper);
    }

    private function looksLikeColumnReference(mixed $value): bool
    {
        return is_string($value) &&
               str_contains($value, '.') &&
               !str_starts_with($value, ':') &&
               !is_numeric($value);
    }

    private function isBetweenOperator(string $operator): bool
    {
        $upperOp = strtoupper($operator);
        return $upperOp === 'BETWEEN' || $upperOp === 'NOT BETWEEN';
    }

    private function buildBetweenColumns(array $columns, TablesAliasHelper $tableHelper): string
    {
        $parts = [];
        foreach ($columns as $column) {
            if (!$this->looksLikeColumnReference($column)) {
                throw new InvalidArgumentException(
                    'BETWEEN in ON clause requires column references',
                );
            }
            $parts[] = $this->buildColumnReference($column, $tableHelper);
        }

        return implode(' AND ', $parts);
    }
}