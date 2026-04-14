<?php

declare(strict_types=1);

class BulkRowSelectUnionAllConstructor extends AbstractBulkRow
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
