<?php

declare(strict_types=1);

enum BulkUpdateType: string
{
    public function getRowBuilderClass(): string
    {
        return match($this) {
            self::DERIVED_TABLE => BulkRowUnionAll::class,
            self::ROW_CONSTRUCTOR => BulkRowRowConstructor::class,
            self::TEMP_TABLE => BulkRowTempTable::class,
            self::BATCH => BulkRowBatchUpdates::class,
            self::UPSERT, self::AUTO => throw new LogicException(
                "{$this->name} doesn't map directly to a row builder. Use factory instead.",
            ),
        };
    }

    public function isSupported(EntityManagerInterface $em): bool
    {
        return match($this) {
            self::DERIVED_TABLE => true,
            self::ROW_CONSTRUCTOR => BulkRowRowConstructor::supports($em),
            self::TEMP_TABLE => BulkRowTempTable::supports($em),
            self::BATCH => true,
            self::UPSERT => $this->supportsUpsert($em),
            self::AUTO => true,
        };
    }

    private function supportsUpsert(EntityManagerInterface $em): bool
    {
        $driver = $em->getDriverName();
        return in_array($driver, ['mysql', 'mariadb', 'pgsql']);
    }
    // Core bulk update strategies
    case DERIVED_TABLE = 'derived';      // UNION ALL derived table (most compatible)
    case ROW_CONSTRUCTOR = 'row';        // VALUES ROW() syntax (MySQL 8.0.19+)
    case TEMP_TABLE = 'temp';            // Temporary table (large datasets)
    case PARAM_VALUES = 'param';         // VALUES (:p1, :p2) syntax (universal fallback)
    case BATCH = 'batch';                // Individual updates in transaction

    // Special strategies
    case UPSERT = 'upsert';              // INSERT ... ON DUPLICATE KEY UPDATE
    case AUTO = 'auto';                  // Auto-detect best strategy

    // @deprecated - Use ROW_CONSTRUCTOR instead
    case VALUES_CONSTRUCTOR = 'values';
}