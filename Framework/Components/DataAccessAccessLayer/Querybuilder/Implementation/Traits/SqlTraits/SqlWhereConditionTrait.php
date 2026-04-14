<?php

declare(strict_types=1);

trait SqlWhereConditionTrait
{
    private const string FLOW = 'where';

    public function where(mixed ...$conditions): self
    {
        if (!isset($this->queryFlow['from']) && isset($this->queryFlow['select'])) {
            $this->from();
        }

        $conditions = $this->standardize(__FUNCTION__, $conditions);

        if (is_array($conditions) && !ArrayUtils::isDeepEmpty($conditions)) {
            if (!isset($this->conditionsMap['where'])) {
                $this->conditionsMap['where'] = [];
            }

            $this->conditionsMap['where'][] = [
                'method' => __FUNCTION__,
                'conditions' => $conditions,
            ];
            $this->queryFlow[self::FLOW] = true;
        }

        return $this;
    }

    public function orWhere(mixed ...$conditions): self
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $conditions = $this->standardize(__FUNCTION__, $conditions);

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];

        return $this;
    }

    public function or(mixed ...$conditions): self
    {
        return $this->orWhere(...$conditions);
    }

    public function andWhere(mixed ...$conditions): self
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $conditions = $this->standardize(__FUNCTION__, $conditions);

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];
        $this->queryFlow['andWhere'] = true;

        return $this;
    }

    public function and(mixed ...$conditions): self
    {
        return $this->andWhere(...$conditions);
    }

    public function whereIn(mixed ...$conditions): self
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $conditions = $this->standardize(__FUNCTION__, $conditions);

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];
        $this->queryFlow['whereIn'] = true;

        return $this;
    }

    public function whereNotIn(mixed ...$conditions): self
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $conditions = $this->standardize(__FUNCTION__, $conditions);

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];
        $this->queryFlow['whereNotIn'] = true;

        return $this;
    }

    public function orWhereIn(mixed ...$conditions): self
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $conditions = $this->standardize(__FUNCTION__, $conditions);

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];

        return $this;
    }

    public function orWhereNotIn(mixed ...$conditions): self
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $conditions = $this->standardize(__FUNCTION__, $conditions);

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];

        return $this;
    }

    public function whereEqualTo(string $column, mixed $value): self
    {
        return $this->where($column, '=', $value);
    }

    public function whereNotEqualTo(string $column, mixed $value): self
    {
        return $this->where($column, '!=', $value);
    }

    public function whereLessThan(string $column, mixed $value): self
    {
        return $this->where($column, '<', $value);
    }

    public function whereGreaterThan(string $column, mixed $value): self
    {
        return $this->where($column, '>', $value);
    }

    public function whereLike(string $column, string $pattern): self
    {
        return $this->where($column, 'LIKE', $pattern);
    }

    public function whereNotLike(string $column, string $pattern): self
    {
        return $this->where($column, 'NOT LIKE', $pattern);
    }

    public function whereNull(string $column): self
    {
        return $this->where($column, 'IS NULL');
    }

    public function whereNotNull(string $column): self
    {
        return $this->where($column, 'IS NOT NULL');
    }

    public function whereBetween(string $column, mixed $min, mixed $max): self
    {
        return $this->where($column, 'BETWEEN', [$min, $max]);
    }

    public function whereNotBetween(string $column, mixed $min, mixed $max): self
    {
        return $this->where($column, 'NOT BETWEEN', [$min, $max]);
    }

    private function standardize(string $method, array $conditions): array
    {
        $standardizer = $this->getClauseStandardizer($method); // Always 'where' for the standardizer class

        if (!$standardizer) {
            throw new RuntimeException('No standardizer found for WHERE clause');
        }

        if (!$standardizer instanceof WhereDataStandardizer) {
            throw new RuntimeException(get_class($standardizer) . ' is not a valid instance of WhereDataStandardizer');
        }

        // Set the specific method context (where, orWhere, whereIn, etc.)
        $standardizer->setMethod($method);

        $payload = $standardizer->standardize($conditions);

        return $payload->getData();
    }
}