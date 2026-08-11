<?php

declare(strict_types=1);

/**
 * Generates SQL using MySQL 8.0.19+ ROW() constructor syntax.
 *
 * Output: VALUES ROW(:val1, :val2), ROW(:val3, :val4)
 *
 * @see https://dev.mysql.com/doc/refman/8.0/en/row-subqueries.html
 */
class BulkRowRowConstructor extends AbstractBulkRow
{
    protected function buildBulkSql(array $data): string
    {
        $rows = [];
        $tableHelper = $this->createTableHelper();

        foreach ($data as $rowIndex => $item) {
            $orderedRow = [];

            foreach ($item as $column => $value) {
                $entity = $this->getEntityForRow($rowIndex);
                $orderedRow[] = $this->createLiteralValue(
                    $value,
                    $column,
                    $tableHelper,
                    $rowIndex,
                    $entity,
                );
            }

            $rows[] = 'ROW(' . implode(', ', $orderedRow) . ')';
        }

        return "VALUES\n        " . implode(",\n        ", $rows);
    }

    public static function supports(EntityManagerInterface $em): bool
    {
        $driver = $em->getDriverName();
        $version = $em->getServerVersion();

        if ($driver === 'mariadb') {
            // MariaDB 10.3+ supports ROW constructor but not as VALUES ROW()
            // Actually MariaDB doesn't support this syntax for UPDATE
            return false;
        }

        if ($driver === 'mysql') {
            return version_compare($version, '8.0.19', '>=');
        }

        return false;
    }
}