<?php

declare(strict_types=1);

/**
 * Generates INSERT ... ON DUPLICATE KEY UPDATE (UPSERT) statement.
 *
 * Output: INSERT INTO table (col1, col2) VALUES (:val1, :val2), (:val3, :val4)
 *         ON DUPLICATE KEY UPDATE col1 = VALUES(col1), col2 = VALUES(col2)
 *
 * Best for: Insert or update existing rows in one statement.
 */
class BulkUpsertRule extends AbstractBulkRow
{
    private ?string $columnList = null;
    private ?string $updateColumns = null;
    private array $primaryKeys = [];

    public function getColumnList(): ?string
    {
        return $this->columnList;
    }

    protected function buildBulkSql(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $columns = array_keys($data[0]);
        $this->columnList = '`' . implode('`, `', $columns) . '`';

        // Build update columns (exclude primary key from update)
        $primaryKey = $this->extractPrimaryKey($data);
        $updateColumns = array_filter($columns, fn ($col) => $col !== $primaryKey);
        $this->updateColumns = implode(', ', array_map(fn ($col) => "`$col` = VALUES(`$col`)", $updateColumns));

        // Build VALUES rows
        $rows = [];
        foreach ($data as $rowIndex => $row) {
            $orderedRow = [];
            foreach ($columns as $column) {
                $value = $row[$column] ?? null;
                $paramName = $this->generateParameterName($rowIndex, $column);
                $this->state->addParameter($paramName, $value);
                $orderedRow[] = ':' . $paramName;
            }
            $rows[] = '(' . implode(', ', $orderedRow) . ')';
        }

        $tableName = $this->extractTableName();

        return sprintf(
            "INSERT INTO `%s` (%s) VALUES \n        %s\n        ON DUPLICATE KEY UPDATE %s",
            $tableName,
            $this->columnList,
            implode(",\n        ", $rows),
            $this->updateColumns,
        );
    }

    private function extractPrimaryKey(array $data): string
    {
        try {
            $keyField = $this->em->getEntityKeyField();
            if ($keyField) {
                return $keyField;
            }
        } catch (Throwable $e) {
            // fall through
        }

        $commonKeys = ['id', 'ID', 'Id', 'uid', 'uuid', 'pk'];
        foreach ($commonKeys as $key) {
            if (isset($data[0][$key])) {
                return $key;
            }
        }

        return 'id';
    }

    private function extractTableName(): string
    {
        try {
            $entity = $this->em->getEntity();
            if ($entity && method_exists($entity, 'getTableName')) {
                return $entity->getTableName();
            }
        } catch (Throwable $e) {
            // fall through
        }

        return 'table';
    }

    private function generateParameterName(int $rowIndex, string $column): string
    {
        $cleanColumn = preg_replace('/[^a-z0-9_]/i', '_', $column);
        return 'upsert_' . $rowIndex . '_' . $cleanColumn;
    }

    public static function supports(EntityManagerInterface $em): bool
    {
        $driver = $em->getDriverName();

        // MySQL, MariaDB, PostgreSQL support this syntax
        if (in_array($driver, ['mysql', 'mariadb', 'pgsql'])) {
            // MariaDB uses different syntax for VALUES() in ON DUPLICATE
            if ($driver === 'mariadb') {
                $version = $em->getServerVersion();
                return version_compare($version, '10.3.0', '>=');
            }
            return true;
        }

        return false;
    }
}