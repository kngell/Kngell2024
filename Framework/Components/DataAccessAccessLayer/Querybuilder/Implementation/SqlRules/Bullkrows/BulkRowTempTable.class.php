<?php

declare(strict_types=1);

class BulkRowTempTable extends AbstractBulkRow
{
    private ?string $tempTableName = null;
    private ?string $columnList = null;

    public function getRule(
        array $rowValuesData,
        TypeNormalizerInterface $normalizer,
        EntityManagerInterface $em,
        SqlTypeHandlerFactory $sqlTypeHandlerFactory,
    ): string {
        $this->parameters = [];
        $this->tempTableName = 'temp_bulk_' . uniqid();

        // Extract columns from first row
        $firstRow = $rowValuesData[0] ?? [];
        $columns = array_keys($firstRow);
        $this->columnList = '`' . implode('`, `', $columns) . '`';

        // Build CREATE TEMPORARY TABLE statement
        $columnDefinitions = $this->buildColumnDefinitions($columns, $rowValuesData, $normalizer, $em);
        $createTable = "CREATE TEMPORARY TABLE `{$this->tempTableName}` (\n    " .
                      implode(",\n    ", $columnDefinitions) . "\n)";

        // Build INSERT statement with parameters
        $insertValues = $this->buildInsertValues($rowValuesData, $columns);
        $insertSql = "INSERT INTO `{$this->tempTableName}` ({$this->columnList}) VALUES " .
                    implode(', ', $insertValues);

        return $createTable . ";\n" . $insertSql;
    }

    public function supports(EntityManagerInterface $em): bool
    {
        try {
            $driver = $em->getDriverName();
            return in_array($driver, ['mysql', 'mariadb', 'pgsql', 'sqlite']);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getColumnList(): ?string
    {
        return $this->columnList;
    }

    public function getTempTableName(): ?string
    {
        return $this->tempTableName;
    }

    private function buildColumnDefinitions(
        array $columns,
        array $rowValuesData,
        TypeNormalizerInterface $normalizer,
        EntityManagerInterface $em,
    ): array {
        $definitions = [];

        foreach ($columns as $column) {
            // Analyze sample data to determine column type
            $sampleValues = array_column($rowValuesData, $column);
            $type = $this->inferColumnType($sampleValues, $normalizer, $em);
            $definitions[] = "`$column` $type";
        }

        return $definitions;
    }

    private function inferColumnType(
        array $sampleValues,
        TypeNormalizerInterface $normalizer,
        EntityManagerInterface $em,
    ): string {
        $driver = $em->getDriverName();

        foreach ($sampleValues as $value) {
            if ($value === null) {
                continue;
            }

            if (is_int($value)) {
                return $driver === 'pgsql' ? 'INTEGER' : 'INT';
            }

            if (is_float($value)) {
                return $driver === 'pgsql' ? 'DOUBLE PRECISION' : 'DOUBLE';
            }

            if (is_bool($value)) {
                return $driver === 'pgsql' ? 'BOOLEAN' : 'TINYINT(1)';
            }

            if (is_string($value)) {
                // Check if it's a hex literal
                if (str_starts_with($value, '0x') && ctype_xdigit(substr($value, 2))) {
                    return $driver === 'pgsql' ? 'BYTEA' : 'VARBINARY(255)';
                }

                // Estimate string length
                $maxLength = max(array_map('strlen', array_filter($sampleValues, 'is_string')));
                $length = min($maxLength + 50, 4000); // Add padding

                return $driver === 'pgsql' ? "VARCHAR($length)" : "VARCHAR($length)";
            }
        }

        // Default fallback
        return $driver === 'pgsql' ? 'TEXT' : 'VARCHAR(255)';
    }

    private function buildInsertValues(array $rowValuesData, array $columns): array
    {
        $insertRows = [];

        foreach ($rowValuesData as $rowIndex => $row) {
            $rowPlaceholders = [];

            foreach ($columns as $column) {
                $value = $row[$column] ?? null;
                $paramName = $this->generateParameterName($rowIndex, $column);
                $this->parameters[$paramName] = $value;
                $rowPlaceholders[] = ':' . $paramName;
            }

            $insertRows[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }

        return $insertRows;
    }
}
