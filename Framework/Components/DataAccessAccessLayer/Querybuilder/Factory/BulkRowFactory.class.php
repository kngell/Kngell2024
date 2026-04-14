<?php

declare(strict_types=1);

class BulkRowFactory
{
    public function create(
        EntityManagerInterface $em,
        string $method,
        QueryState $state,
        array|CollectionInterface $data,
        ?string $strategyType = null,
    ): QueryRulesInterface {
        $strategyType = $strategyType ?? $this->detectBestStrategy($em, $method);

        return match ($strategyType) {
            'unionAll' => new BulkRowSelectUnionAllConstructor($em, $method, $state, $data),
            'values' => new BulkRowValuesConstructor($em, $method, $state, $data),
            'temp' => new BulkRowTempTable($em, $method, $state, $data),
            'prepared' => new BulkRowPreparedValues($em, $method, $state, $data),
            'batch' => new BulkRowMultipleUpdates($em, $method, $state, $data),
            default => throw new InvalidArgumentException("Unknown bulk row strategy: $strategyType"),
        };
    }

    private function detectBestStrategy(EntityManagerInterface $em, string $method): string
    {
        if (BulkRowValuesConstructor::supports($em)) {
            return 'values';
        }

        if (BulkRowSelectUnionAllConstructor::supports($em)) {
            return 'unionAll';
        }

        return 'batch';
    }
}
