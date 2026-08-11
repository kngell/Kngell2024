<?php

declare(strict_types=1);

/**
 * Generates UNION ALL derived table.
 *
 * Output: SELECT :val1 AS col1, :val2 AS col2
 *         UNION ALL
 *         SELECT :val3, :val4
 *
 * Most compatible strategy - works on all databases.
 */
class BulkRowUnionAll extends AbstractBulkRow  // Renamed from BulkRowSelectUnionAllConstructor
{
    protected function buildBulkSql(array $data): string
    {
        $selects = [];
        $tableHelper = $this->createTableHelper();

        foreach ($data as $rowIndex => $item) {
            $columns = [];
            foreach ($item as $column => $value) {
                $entity = $this->getEntityForRow($rowIndex);
                $literalValue = $this->createParameter(
                    $value,
                    $column,
                    $tableHelper,
                    $rowIndex,
                    $entity,
                );

                if ($rowIndex === 0) {
                    $columns[] = $literalValue . ' AS `' . $column . '`';
                } else {
                    $columns[] = $literalValue;
                }
            }

            $selects[] = 'SELECT ' . implode(', ', $columns);
        }

        return implode("\nUNION ALL\n", $selects);
    }

    public static function supports(EntityManagerInterface $em): bool
    {
        return true;
    }
}