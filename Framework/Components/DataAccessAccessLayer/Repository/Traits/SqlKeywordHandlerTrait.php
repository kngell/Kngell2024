<?php

declare(strict_types=1);

trait SqlKeywordHandlerTrait
{
    protected function applySqlKeywords(
        array $conditions,
        SqlSelectQueryBuilderInterface $anchor,
        SqlSelectQueryBuilderInterface $recursive,
        SqlSelectQueryBuilderInterface $mainQuery,
    ): array {
        $recursiveKeywords = [
            'GROUP BY' => 'groupBy',
            'HAVING' => 'having',
        ];

        $mainQueryKeywords = [
            'ORDER BY' => 'orderBy',
            'LIMIT' => 'limit',
            'OFFSET' => 'offset',
        ];

        $whereConditions = $conditions;

        foreach ($recursiveKeywords as $keyword => $method) {
            if (isset($conditions[$keyword])) {
                $this->applyKeywordToBuilder($anchor, $method, $conditions[$keyword]);
                $this->applyKeywordToBuilder($recursive, $method, $conditions[$keyword]);
                unset($whereConditions[$keyword]);
            }
        }

        foreach ($mainQueryKeywords as $keyword => $method) {
            if (isset($conditions[$keyword])) {
                $this->applyKeywordToBuilder($mainQuery, $method, $conditions[$keyword]);
                unset($whereConditions[$keyword]);
            }
        }

        return $whereConditions;
    }

    private function applyKeywordToBuilder(
        SqlSelectQueryBuilderInterface $qb,
        string $method,
        $value,
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

    private function applyOrderBy(SqlSelectQueryBuilderInterface $qb, $value): void
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

    private function applyGroupBy(SqlSelectQueryBuilderInterface $qb, $value): void
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