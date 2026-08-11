<?php

declare(strict_types=1);

trait BulkUpdateTrait
{
    public function bulkUpdate(
        null|string|Closure $table = null,
        mixed $data = null,
        array $conditions = [],
        ?BulkUpdateType $type = null,
    ): void {
        if (empty($data) && !$this->em->hasData()) {
            throw new RepositoryException('No data to save!');
        }

        $type = $type ?? BulkUpdateType::AUTO;
        $qb = $this->em->createQueryBuilder();

        $keyField = $this->em->getEntityKeyField();

        $update = match ($type) {
            // Standard bulk update strategies
            BulkUpdateType::DERIVED_TABLE => $this->derivedTableUpdate($qb, $table, $data, $keyField),
            BulkUpdateType::ROW_CONSTRUCTOR => $this->rowConstructorUpdate($qb, $table, $data, $keyField),
            BulkUpdateType::TEMP_TABLE => $this->tempTableUpdate($qb, $table, $data, $conditions, $keyField),
            BulkUpdateType::PARAM_VALUES => $this->paramValuesUpdate($qb, $table, $data, $keyField),
            BulkUpdateType::BATCH => $this->batchUpdate($qb, $table, $data, $conditions, $keyField),

            // Special strategies
            BulkUpdateType::UPSERT => $this->upsertUpdate($qb, $table, $data, $keyField),
            BulkUpdateType::AUTO => $this->autoUpdate($qb, $table, $data, $conditions, $keyField),

            // Backward compatibility (deprecated)
            BulkUpdateType::VALUES_CONSTRUCTOR => $this->rowConstructorUpdate($qb, $table, $data, $keyField),
        };

        $update->build();
        // $this->debugSql($qb);
    }

    /**
     * DERIVED TABLE with UNION ALL (Most compatible - MySQL, MariaDB, PostgreSQL).
     *
     * Builds: UPDATE table
     *         INNER JOIN (SELECT ? AS col UNION ALL SELECT ?) AS sub
     *         ON table.id = sub.id
     *         SET table.col = sub.col
     *
     * Best for: 2-1000 rows, different values per row, no WHERE conditions
     */
    protected function derivedTableUpdate(
        QueryBuilder $qb,
        null|string|Closure $table,
        mixed $data,
        string $keyField,
    ): SqlUpdateQueryBuilderInterface {
        return $qb->bulkUpdate($table, BulkUpdateType::DERIVED_TABLE)
            ->innerJoin($data)
            ->on($keyField, 'subquery.' . $keyField)
            ->set();
    }

    /**
     * ROW CONSTRUCTOR (MySQL 8.0.19+ only).
     *
     * Builds: UPDATE table
     *         INNER JOIN (VALUES ROW(?, ?), ROW(?, ?)) AS sub(id, col)
     *         ON table.id = sub.id
     *         SET table.col = sub.col
     *
     * NOT compatible with MariaDB
     * Best for: Small to medium datasets on MySQL 8.0.19+
     */
    protected function rowConstructorUpdate(
        QueryBuilder $qb,
        null|string|Closure $table,
        mixed $data,
        string $keyField,
    ): SqlUpdateQueryBuilderInterface {
        return $qb->bulkUpdate($table, BulkUpdateType::ROW_CONSTRUCTOR)
            ->innerJoin($data)  // Uses VALUES ROW() syntax
            ->on($keyField, 'subquery.' . $keyField)
            ->set();
    }

    /**
     * PARAM VALUES (Universal fallback).
     *
     * Builds: UPDATE table
     *         INNER JOIN (VALUES (:p1, :p2), (:p3, :p4)) AS sub(id, col)
     *         ON table.id = sub.id
     *         SET table.col = sub.col
     *
     * Best for: When other strategies don't work
     */
    protected function paramValuesUpdate(
        QueryBuilder $qb,
        null|string|Closure $table,
        mixed $data,
        string $keyField,
    ): SqlUpdateQueryBuilderInterface {
        return $qb->bulkUpdate($table, BulkUpdateType::PARAM_VALUES)
            ->paramValues($data)  // Uses VALUES (:p1, :p2) syntax
            ->on($keyField, 'subquery.' . $keyField)
            ->set();
    }

    /**
     * TEMP TABLE (All databases).
     *
     * Creates temporary table, inserts data, joins, drops table
     *
     * Best for: 1000+ rows or when WHERE conditions needed
     */
    protected function tempTableUpdate(
        QueryBuilder $qb,
        null|string|Closure $table,
        mixed $data,
        array $conditions,
        string $keyField,
    ): SqlUpdateQueryBuilderInterface {
        $update = $qb->bulkUpdate($table, BulkUpdateType::TEMP_TABLE)
            ->createTempTable($data)  // Creates and populates temp table
            ->joinTempTable('temp_table', $keyField)
            ->set();

        if (!empty($conditions)) {
            $update->where($conditions);
        }

        return $update;
    }

    /**
     * UPSERT (INSERT ... ON DUPLICATE KEY UPDATE).
     *
     * Best for: Insert or update rows in one statement
     */
    protected function upsertUpdate(
        QueryBuilder $qb,
        null|string|Closure $table,
        mixed $data,
        string $keyField,
    ): SqlUpdateQueryBuilderInterface {
        return $qb->upsert($table)
            ->data($data)
            ->onConflict($keyField)
            ->doUpdate();
    }

    /**
     * BATCH (Individual updates in transaction).
     *
     * Slowest strategy - only for small datasets or last resort
     * Best for: < 10 rows or when complex per-row logic needed
     */
    protected function batchUpdate(
        QueryBuilder $qb,
        null|string|Closure $table,
        mixed $data,
        array $conditions,
        string $keyField,
    ): SqlUpdateQueryBuilderInterface {
        return $qb->transaction(function (QueryBuilder $qb) use ($data, $conditions, $table) {
            foreach ($data as $row) {
                $qb->update($table)
                    ->set($row)
                    ->where(array_merge($conditions, [$keyField => $row[$keyField] ?? null]))
                    ->build();
            }
        });
    }

    /**
     * AUTO: Smart selection based on conditions and data size.
     */
    protected function autoUpdate(
        QueryBuilder $qb,
        null|string|Closure $table,
        mixed $data,
        array $conditions,
        string $keyField,
    ): SqlUpdateQueryBuilderInterface {
        $rowCount = is_countable($data) ? count($data) : 0;

        // With WHERE conditions, must use TEMP TABLE
        if (!empty($conditions)) {
            return $this->tempTableUpdate($qb, $table, $data, $conditions, $keyField);
        }

        // Very large datasets: TEMP TABLE
        if ($rowCount > 5000) {
            return $this->tempTableUpdate($qb, $table, $data, $conditions, $keyField);
        }

        // Check if ROW CONSTRUCTOR is available (fastest)
        if ($this->isRowConstructorSupported()) {
            return $this->rowConstructorUpdate($qb, $table, $data, $keyField);
        }

        // Default: DERIVED TABLE (works everywhere)
        if ($rowCount <= 1000) {
            return $this->derivedTableUpdate($qb, $table, $data, $keyField);
        }

        // Fallback: TEMP TABLE for larger datasets
        return $this->tempTableUpdate($qb, $table, $data, $conditions, $keyField);
    }

    /**
     * Check if ROW CONSTRUCTOR syntax is supported by current database.
     *
     * ROW constructor is supported by:
     * - MySQL 8.0.19+
     * - NOT supported by MariaDB (any version)
     * - NOT supported by PostgreSQL (different syntax)
     */
    private function isRowConstructorSupported(): bool
    {
        // First check if it's MariaDB (not supported at all)
        if ($this->isMariaDB()) {
            return false;
        }

        $driver = $this->em->getDriverName();

        if ($driver === 'mysql') {
            $version = $this->getCleanVersion($this->em->getServerVersion());
            return version_compare($version, '8.0.19', '>=');
        }

        return false;
    }

    private function isMariaDB(): bool
    {
        $versionString = $this->em->getServerVersion();
        return stripos($versionString, 'mariadb') !== false ||
               stripos($versionString, 'maria') !== false;
    }

    /**
     * Extract clean version number from version string.
     *
     * Examples:
     * - "8.0.36" -> "8.0.36"
     * - "10.11.4-MariaDB" -> "10.11.4"
     * - "5.7.39-log" -> "5.7.39"
     */
    private function getCleanVersion(string $versionString): string
    {
        // Remove suffix after dash or space
        $cleanVersion = preg_replace('/[-\s].*$/', '', $versionString);

        // Also remove 'mariadb' if present (case insensitive)
        $cleanVersion = preg_replace('/mariadb/i', '', $cleanVersion);

        return trim($cleanVersion);
    }
}