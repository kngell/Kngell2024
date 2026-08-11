<?php

declare(strict_types=1);

class BulkRowTempTable extends AbstractBulkRow
{
    private ?string $tempTableName = null;
    private ?string $columnList = null;
    private array $columnDefinitions = [];

    public function getColumnList(): ?string
    {
        return $this->columnList;
    }

    public function getTempTableName(): ?string
    {
        return $this->tempTableName;
    }

    public function getColumnDefinitions(): array
    {
        return $this->columnDefinitions;
    }

    protected function buildBulkSql(array $data): string
    {
        $this->tempTableName = 'temp_bulk_' . uniqid();

        // Extract columns from first row
        $firstRow = $data[0] ?? [];
        $columns = array_keys($firstRow);
        $this->columnList = '`' . implode('`, `', $columns) . '`';

        // Build CREATE TEMPORARY TABLE statement
        $columnDefinitions = $this->buildColumnDefinitions($columns, $data);
        $createTable = "CREATE TEMPORARY TABLE `{$this->tempTableName}` (\n    " .
                      implode(",\n    ", $columnDefinitions) . "\n)";

        // Build INSERT statement with parameters
        $insertValues = $this->buildInsertValues($data, $columns);
        $insertSql = "INSERT INTO `{$this->tempTableName}` ({$this->columnList}) VALUES " .
                    implode(', ', $insertValues);

        return $createTable . ";\n" . $insertSql;
    }

    private function buildColumnDefinitions(array $columns, array $rowValuesData): array
    {
        $definitions = [];

        foreach ($columns as $column) {
            $sampleValues = array_column($rowValuesData, $column);
            $type = $this->inferColumnType($sampleValues);
            $definitions[] = "`$column` $type";
        }

        $this->columnDefinitions = $definitions;
        return $definitions;
    }

    private function inferColumnType(array $sampleValues): string
    {
        $driver = $this->em->getDriverName();

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
                if (str_starts_with($value, '0x') && ctype_xdigit(substr($value, 2))) {
                    return $driver === 'pgsql' ? 'BYTEA' : 'VARBINARY(255)';
                }

                $strings = array_filter($sampleValues, 'is_string');
                if (!empty($strings)) {
                    $maxLength = max(array_map('strlen', $strings));
                    $length = min($maxLength + 50, 4000);
                    return $driver === 'pgsql' ? "VARCHAR($length)" : "VARCHAR($length)";
                }
            }
        }

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
                $this->state->addParameter($paramName, $value);
                $rowPlaceholders[] = ':' . $paramName;
            }

            $insertRows[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }

        return $insertRows;
    }

    private function generateParameterName(int $rowIndex, string $column): string
    {
        $cleanColumn = preg_replace('/[^a-z0-9_]/i', '_', $column);
        return 'temp_' . $this->tempTableName . '_' . $rowIndex . '_' . $cleanColumn;
    }

    public static function supports(EntityManagerInterface $em): bool
    {
        try {
            $driver = $em->getDriverName();
            return in_array($driver, ['mysql', 'mariadb', 'pgsql', 'sqlite']);
        } catch (Throwable $e) {
            return false;
        }
    }
}