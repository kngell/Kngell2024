<?php

declare(strict_types=1);

trait SqlKeywordHandlerTrait
{
    /**
     * EXACT COPY OF YOUR WORKING CTE METHOD - DO NOT CHANGE.
     */
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

    /**
     * NEW METHOD FOR REGULAR SELECT QUERIES ONLY.
     */
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

    /**
     * UNIFIED METHOD THAT DETECTS CTE VS REGULAR SELECT
     * If anchor and recursive are provided, it's CTE. Otherwise, it's regular SELECT.
     */
    protected function applySqlKeywordsFlexible(
        array $conditions,
        SqlSelectQueryBuilderInterface $primary,
        ?SqlSelectQueryBuilderInterface $anchor = null,
        ?SqlSelectQueryBuilderInterface $recursive = null,
    ): array {
        if ($anchor !== null && $recursive !== null) {
            // CTE mode - use original method
            return $this->applySqlKeywords($conditions, $anchor, $recursive, $primary);
        }

        // Regular SELECT mode
        return $this->applySqlKeywordsForSelect($conditions, $primary);
    }

    /**
     * SIMPLE PAGINATION HELPER FOR REGULAR SELECTS (OPTIONAL).
     */
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

    /**
     * KEEP THE EXACT SAME HELPER METHODS.
     */
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
