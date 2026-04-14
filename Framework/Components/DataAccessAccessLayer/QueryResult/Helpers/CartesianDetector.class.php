<?php

declare(strict_types=1);

class CartesianDetector
{
    public function isSingleEntityWithJoins(string $query, string $operation, ?string $primaryKeyField = null): bool
    {
        // Check for JOINs
        if (!$this->hasJoins($query)) {
            return false;
        }

        // Single entity operations
        if ($this->isSingleEntityOperation($operation)) {
            return true;
        }

        // Check for LIMIT 1
        if ($this->hasLimitOne($query)) {
            return true;
        }

        // Check for WHERE clause with primary key
        if ($primaryKeyField && $this->hasPrimaryKeyInWhere($query, $primaryKeyField)) {
            return true;
        }

        return false;
    }

    public function collectionRequiresCartesianHandling(string $query, string $operation, ?string $primaryKeyField = null): bool
    {
        // Only relevant for 'all' operations
        if ($operation !== 'all') {
            return false;
        }

        // Must have joins
        if (!$this->hasJoins($query)) {
            return false;
        }

        // Check if there are multiple joins (potential cartesian product)
        if ($this->hasMultipleJoins($query)) {
            return true;
        }

        // Check for one-to-many relationships
        if ($this->hasOneToManyRelationships($query)) {
            return true;
        }

        return false;
    }

    public function resultNeedsCartesianHandling(array $rows, string $operation, string $query, ?string $primaryKeyField = null): bool
    {
        // Single entity operations
        if ($this->isSingleEntityOperation($operation)) {
            return true;
        }

        // Collection with joins that might need handling
        if ($operation === 'all' && $this->hasJoins($query)) {
            if ($primaryKeyField && $this->hasMultipleRowsPerEntity($rows, $primaryKeyField)) {
                return true;
            }
        }

        return false;
    }

    private function hasJoins(string $query): bool
    {
        return (bool) preg_match('/(JOIN|LEFT JOIN|RIGHT JOIN|INNER JOIN)/i', $query);
    }

    private function hasMultipleJoins(string $query): bool
    {
        return (bool) preg_match_all('/(JOIN|LEFT JOIN|RIGHT JOIN|INNER JOIN)/i', $query) > 1;
    }

    private function hasOneToManyRelationships(string $query): bool
    {
        $patterns = [
            '/JOIN.*_items\s+ON/i',
            '/JOIN.*_details\s+ON/i',
            '/JOIN.*_attributes\s+ON/i',
            '/JOIN.*_meta\s+ON/i',
            '/JOIN.*_options\s+ON/i',
            '/JOIN.*_images\s+ON/i',
            '/JOIN.*_translations\s+ON/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $query)) {
                return true;
            }
        }

        return false;
    }

    private function isSingleEntityOperation(string $operation): bool
    {
        return in_array($operation, ['first', 'single', 'last']);
    }

    private function hasLimitOne(string $query): bool
    {
        return (bool) preg_match('/LIMIT\s+1\b/i', $query);
    }

    private function hasPrimaryKeyInWhere(string $query, string $primaryKeyField): bool
    {
        $pattern = '/WHERE.*' . preg_quote($primaryKeyField, '/') . '\s*[=<>]/i';
        return (bool) preg_match($pattern, $query);
    }

    private function hasSingleUniqueId(array $rows, string $primaryKeyField): bool
    {
        $uniqueIds = [];

        foreach ($rows as $row) {
            if (isset($row[$primaryKeyField])) {
                $uniqueIds[$row[$primaryKeyField]] = true;
            }
        }

        return count($uniqueIds) === 1;
    }

    private function hasMultipleRowsPerEntity(array $rows, string $primaryKeyField): bool
    {
        $rowCounts = [];

        foreach ($rows as $row) {
            if (isset($row[$primaryKeyField])) {
                $id = $row[$primaryKeyField];
                $rowCounts[$id] = ($rowCounts[$id] ?? 0) + 1;
            }
        }

        // If any entity appears more than once, we have cartesian product
        foreach ($rowCounts as $count) {
            if ($count > 1) {
                return true;
            }
        }

        return false;
    }
}