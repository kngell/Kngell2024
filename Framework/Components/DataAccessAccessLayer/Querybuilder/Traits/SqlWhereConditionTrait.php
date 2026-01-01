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

        if (!empty(ArrayUtils::flattenArrayRecursive($conditions))) {
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

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];
        return $this;
    }

    public function whereEqualTo(string $column, mixed $value): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function whereNotEqualTo(string $column, mixed $value): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function whereLessThan(string $column, mixed $value): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function whereGreaterThan(string $column, mixed $value): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function whereLike(string $column, string $pattern): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function whereNotLike(string $column, string $pattern): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function whereNull(string $column): SqlSelectQueryBuilderInterface
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $column,
        ];
        $this->queryFlow[self::FLOW] = true;
        return $this;
    }

    public function whereNotNull(string $column): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function whereBetween(string $column, mixed $min, mixed $max): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }
}