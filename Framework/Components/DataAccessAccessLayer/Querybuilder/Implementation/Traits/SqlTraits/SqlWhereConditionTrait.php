<?php

declare(strict_types=1);

trait SqlWhereConditionTrait
{
    private const string FLOW = 'where';

    public function where(mixed ...$conditions): static
    {
        $this->ensureFromExists();
        $this->ensureSetExists();
        $innerMethod = $conditions['innerMethod'] ?? __FUNCTION__;
        $innerData = $conditions['innerData'] ?? $conditions;

        if (is_array($innerData) && count($innerData) === 3 && $this->isOperator($innerData[1])) {
            $standardized = $innerData;
        } else {
            $standardized = $this->standardize($innerMethod, $innerData);
        }

        if (empty($standardized)) {
            return $this;
        }
        $this->conditionsMap[] = [
            'method' => $innerMethod,
            'conditions' => $standardized,
        ];

        // Record in query flow
        $this->queryFlow[] = $innerMethod;

        return $this;
    }

    /**
     * OR WHERE convenience methods.
     */
    public function orWhere(mixed ...$conditions): static
    {
        return $this->where(...['innerData' => $conditions, 'innerMethod' => __FUNCTION__]);
    }

    public function or(mixed ...$conditions): static
    {
        return $this->orWhere(...$conditions);
    }

    /**
     * AND WHERE convenience methods.
     */
    public function andWhere(mixed ...$conditions): static
    {
        return $this->where(...['innerData' => $conditions, 'innerMethod' => __FUNCTION__]);
    }

    public function and(mixed ...$conditions): static
    {
        return $this->andWhere(...$conditions);
    }

    /**
     * WHERE IN methods.
     */
    public function whereIn(mixed ...$conditions): static
    {
        return $this->where(...['innerData' => $conditions, 'innerMethod' => __FUNCTION__]);
    }

    public function whereNotIn(mixed ...$conditions): static
    {
        return $this->where(...['innerData' => $conditions, 'innerMethod' => __FUNCTION__]);
    }

    public function orWhereIn(mixed ...$conditions): static
    {
        return $this->where(...['innerData' => $conditions, 'innerMethod' => __FUNCTION__]);
    }

    public function orWhereNotIn(mixed ...$conditions): static
    {
        return $this->where(...['innerData' => $conditions, 'innerMethod' => __FUNCTION__]);
    }

    /**
     * WHERE BETWEEN methods.
     */
    public function whereBetween(string $column, mixed $min, mixed $max): static
    {
        return $this->where($column, 'BETWEEN', [$min, $max]);
    }

    public function whereNotBetween(string $column, mixed $min, mixed $max): static
    {
        return $this->where($column, 'NOT BETWEEN', [$min, $max]);
    }

    public function orWhereBetween(string $column, mixed $min, mixed $max): static
    {
        return $this->orWhere($column, 'BETWEEN', [$min, $max]);
    }

    public function whereNull(string $column): static
    {
        return $this->where(...['innerData' => [$column . ' is null'], 'innerMethod' => __FUNCTION__]);
    }

    public function whereNotNull(string $column): static
    {
        return $this->where($column, 'IS NOT NULL');
    }

    public function orWhereNull(string $column): static
    {
        return $this->orWhere($column, 'IS NULL');
    }

    public function orWhereNotNull(string $column): static
    {
        return $this->orWhere($column, 'IS NOT NULL');
    }

    /**
     * WHERE comparison methods.
     */
    public function whereEqualTo(string $column, mixed $value): static
    {
        return $this->where($column, '=', $value);
    }

    public function whereNotEqualTo(string $column, mixed $value): static
    {
        return $this->where($column, '!=', $value);
    }

    public function whereLessThan(string $column, mixed $value): static
    {
        return $this->where($column, '<', $value);
    }

    public function whereGreaterThan(string $column, mixed $value): static
    {
        return $this->where($column, '>', $value);
    }

    public function whereLessThanOrEqualTo(string $column, mixed $value): static
    {
        return $this->where($column, '<=', $value);
    }

    public function whereGreaterThanOrEqualTo(string $column, mixed $value): static
    {
        return $this->where($column, '>=', $value);
    }

    /**
     * WHERE pattern matching methods.
     */
    public function whereLike(string $column, string $pattern): static
    {
        return $this->where($column, 'LIKE', $pattern);
    }

    public function whereNotLike(string $column, string $pattern): static
    {
        return $this->where($column, 'NOT LIKE', $pattern);
    }

    public function whereILike(string $column, string $pattern): static
    {
        return $this->where($column, 'ILIKE', $pattern);
    }

    public function orWhereLike(string $column, string $pattern): static
    {
        return $this->orWhere($column, 'LIKE', $pattern);
    }

    /**
     * WHERE EXISTS methods.
     */
    public function whereExists(Closure|SqlSelectQueryBuilderInterface $subquery): static
    {
        return $this->where('EXISTS', $subquery);
    }

    public function whereNotExists(Closure|SqlSelectQueryBuilderInterface $subquery): static
    {
        return $this->where('NOT EXISTS', $subquery);
    }

    public function getWhereConditions(): array
    {
        return $this->conditionsMap ?? [];
    }

    protected function ensureFromExists(): void
    {
        if (!ArrayUtils::hasValue($this->queryFlow, 'from') && ArrayUtils::hasValue($this->queryFlow, 'select')) {
            if ($this instanceof SqlSelectQueryBuilderInterface) {
                $this->from();
            }
        }
    }

    protected function ensureSetExists(): void
    {
        if (!ArrayUtils::hasValue($this->queryFlow, 'set') && ArrayUtils::hasValue($this->queryFlow, 'update')) {
            if ($this instanceof SqlUpdateQueryBuilderInterface) {
                $this->set();
            }
        }
    }

    /**
     * Helper to check if a string is an SQL operator.
     */
    private function isOperator(string $value): bool
    {
        $operators = ['=', '!=', '<>', '>', '<', '>=', '<=',
            'LIKE', 'NOT LIKE', 'ILIKE', 'IN', 'NOT IN',
            'IS NULL', 'IS NOT NULL', 'BETWEEN', 'NOT BETWEEN',
            'EXISTS', 'NOT EXISTS'];

        return in_array(strtoupper($value), $operators);
    }

    /**
     * Standardize conditions using the appropriate standardizer.
     */
    private function standardize(string $method, array $conditions): array
    {
        $standardizer = $this->getClauseStandardizer('where'); // Always use 'where' standardizer

        if (!$standardizer) {
            throw new RuntimeException('No standardizer found for WHERE clause');
        }

        if (!$standardizer instanceof WhereDataStandardizer) {
            throw new RuntimeException(get_class($standardizer) . ' is not a valid instance of WhereDataStandardizer');
        }

        // Set the specific method context (where, orWhere, whereIn, etc.)
        $standardizer->setMethod($method);

        $result = $standardizer->standardize($conditions);

        return $result->getData();
    }
}