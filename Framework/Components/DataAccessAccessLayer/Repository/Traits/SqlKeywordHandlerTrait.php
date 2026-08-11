<?php

declare(strict_types=1);

trait SqlKeywordHandlerTrait
{
    protected function applySqlKeywords(
        array $conditions,
        SqlSelectQueryBuilderInterface $anchor,
    ): array {
        $Keywords = [
            'GROUP BY' => 'groupBy',
            'HAVING' => 'having',
            'ORDER BY' => 'orderBy',
            'LIMIT' => 'limit',
            'OFFSET' => 'offset',
        ];

        $whereConditions = $conditions;

        foreach ($Keywords as $keyword => $method) {
            if (isset($conditions[$keyword])) {
                $this->applyKeywordToBuilder($anchor, $method, $conditions[$keyword]);
                unset($whereConditions[$keyword]);
            }
        }
        return $whereConditions;
    }

    protected function applySqlKeywordsForSelect(
        array $conditions,
        SqlSelectQueryBuilderInterface $queryBuilder,
    ): array {
        $allKeywords = [
            'ORDER BY' => 'orderBy',
            'LIMIT' => 'limit',
            'OFFSET' => 'offset',
            'GROUP BY' => 'groupBy',
            'HAVING' => 'having',
        ];

        $whereConditions = $conditions;

        foreach ($allKeywords as $keyword => $method) {
            if (isset($conditions[$keyword])) {
                $this->applyKeywordToBuilder($queryBuilder, $method, $conditions[$keyword]);
                unset($whereConditions[$keyword]);
            }
        }

        return $whereConditions;
    }

    protected function applySqlKeywordsFlexible(
        array $conditions,
        ?SqlSelectQueryBuilderInterface $anchor = null,
    ): array {
        if ($anchor !== null) {
            return $this->applySqlKeywords($conditions, $anchor);
        }
        return $conditions;
    }

    protected function applyPagination(
        SqlSelectQueryBuilderInterface $qb,
        array $conditions,
    ): array {
        $remaining = $conditions;

        if (isset($conditions['page']) && isset($conditions['per_page'])) {
            $page = max(1, (int) $conditions['page']);
            $perPage = max(1, (int) $conditions['per_page']);

            $qb->limit($perPage);
            $qb->offset(($page - 1) * $perPage);

            unset($remaining['page']);
            unset($remaining['per_page']);
        }

        return $remaining;
    }

    private function applyKeywordToBuilder(
        SqlSelectQueryBuilderInterface $qb,
        string $method,
        mixed $value,
    ): void {
        switch ($method) {
            case 'orderBy':
                $this->applyOrderBy($qb, $value);
                break;
            case 'groupBy':
                $this->applyGroupBy($qb, $value);
                break;
            case 'limit':
                $qb->limit((int) $value);
                break;
            case 'offset':
                $qb->offset((int) $value);
                break;
            case 'having':
                $qb->having($value);
                break;
        }
    }

    private function applyOrderBy(SqlSelectQueryBuilderInterface $qb, mixed $value): void
    {
        if (is_array($value)) {
            $orderByParams = [];

            foreach ($value as $key => $val) {
                if (is_int($key)) {
                    $orderByParams[] = $val;
                } else {
                    $orderByParams[] = $key . ' ' . $val;
                }
            }

            $qb->orderBy(...$orderByParams);
        } elseif (is_string($value)) {
            $orders = explode(',', $value);
            $orderByParams = [];

            foreach ($orders as $order) {
                $order = trim($order);
                if (!preg_match('/\s+(ASC|DESC)$/i', $order)) {
                    $order .= ' ASC';
                }
                $orderByParams[] = $order;
            }
            $qb->orderBy(...$orderByParams);
        }
    }

    private function applyGroupBy(SqlSelectQueryBuilderInterface $qb, mixed $value): void
    {
        if (is_array($value)) {
            foreach ($value as $column) {
                $qb->groupBy($column);
            }
        } else {
            $qb->groupBy($value);
        }
    }
}