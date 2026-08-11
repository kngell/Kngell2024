<?php

declare(strict_types=1);

class BulkRowFactory
{
    private const STRATEGY_MAP = [
        BulkUpdateType::DERIVED_TABLE->value => BulkRowUnionAll::class,
        BulkUpdateType::ROW_CONSTRUCTOR->value => BulkRowRowConstructor::class,
        BulkUpdateType::TEMP_TABLE->value => BulkRowTempTable::class,
        BulkUpdateType::PARAM_VALUES->value => BulkRowParamValues::class,
        BulkUpdateType::BATCH->value => BulkRowBatchUpdates::class,
    ];

    public function create(
        EntityManagerInterface $em,
        string $method,
        QueryState $state,
        array|CollectionInterface $data,
        BulkUpdateType $strategy,
        ?string $tableName = null,
    ): ?QueryRulesInterface {
        if (empty($strategy)) {
            throw new InvalidArgumentException("No Row strategy defined for method $method");
        }
        // Handle AUTO strategy
        if ($strategy === BulkUpdateType::AUTO) {
            $strategy = $this->detectBestStrategy($em, $data);
        }

        // Handle UPSERT (different flow)
        if ($strategy === BulkUpdateType::UPSERT) {
            return new BulkUpsertRule($em, $method, $state, $data);
        }

        // Handle BATCH (needs table name)
        if ($strategy === BulkUpdateType::BATCH) {
            return new BulkRowBatchUpdates($em, $method, $state, $data, $tableName);
        }

        // Standard row builders
        $builderClass = self::STRATEGY_MAP[$strategy->value] ?? null;

        if (!$builderClass) {
            throw new InvalidArgumentException(
                "No row builder found for strategy: {$strategy->value}",
            );
        }

        return new $builderClass($em, $method, $state, $data);
    }

    private function detectBestStrategy(
        EntityManagerInterface $em,
        array|CollectionInterface $data,
    ): BulkUpdateType {
        $rowCount = is_countable($data) ? count($data) : 0;

        if ($rowCount > 5000) {
            return BulkUpdateType::TEMP_TABLE;
        }

        if (BulkUpdateType::ROW_CONSTRUCTOR->isSupported($em)) {
            return BulkUpdateType::ROW_CONSTRUCTOR;
        }

        if ($rowCount > 100) {
            return BulkUpdateType::PARAM_VALUES;
        }

        return BulkUpdateType::DERIVED_TABLE;
    }
}