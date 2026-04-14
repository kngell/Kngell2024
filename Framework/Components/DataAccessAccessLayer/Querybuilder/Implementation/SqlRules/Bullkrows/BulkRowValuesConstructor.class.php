<?php

declare(strict_types=1);

class BulkRowValuesConstructor extends AbstractBulkRow
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
            return version_compare($version, '10.3.0', '>=');
        }

        if ($driver === 'mysql') {
            return version_compare($version, '8.0.19', '>=');
        }

        return false;
    }
}
