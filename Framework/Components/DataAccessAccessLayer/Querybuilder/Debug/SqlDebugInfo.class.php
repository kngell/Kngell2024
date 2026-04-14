<?php

declare(strict_types=1);
final class SqlDebugInfo
{
    public function __construct(
        public readonly string $rawSql,
        public readonly string $interpolatedSql,
        public readonly array $parameters,
        public readonly float $executionTimeMs,
        public readonly array $metadata = [],
    ) {
    }
}