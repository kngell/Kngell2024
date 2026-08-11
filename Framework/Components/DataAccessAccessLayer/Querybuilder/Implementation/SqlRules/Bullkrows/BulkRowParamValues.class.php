<?php

declare(strict_types=1);

/**
 * Generates simple VALUES clause with named parameters.
 *
 * Output: VALUES (:bulk_0_col1, :bulk_0_col2), (:bulk_1_col1, :bulk_1_col2)
 *
 * Note: This only generates the VALUES part - needs JOIN/UPDATE wrapper.
 */
class BulkRowParamValues extends AbstractBulkRow
{
    private ?string $columnList = null;

    public function getColumnList(): ?string
    {
        return $this->columnList;
    }

    protected function buildBulkSql(array $data): string
    {
        $rows = [];

        foreach ($data as $rowIndex => $item) {
            $orderedRow = [];

            foreach ($item as $column => $value) {
                $paramName = $this->generateParameterName($rowIndex, $column);
                $this->state->addParameter($paramName, $value);
                $orderedRow[] = ':' . $paramName;
            }

            $rows[] = '(' . implode(', ', $orderedRow) . ')';
        }

        // Also store column list for reference
        if (!empty($data)) {
            $this->columnList = '`' . implode('`, `', array_keys($data[0])) . '`';
        }

        return "VALUES\n        " . implode(",\n        ", $rows);
    }

    private function generateParameterName(int $rowIndex, string $column): string
    {
        $cleanColumn = preg_replace('/[^a-z0-9_]/i', '_', $column);
        return 'param_' . $rowIndex . '_' . $cleanColumn;
    }

    public static function supports(EntityManagerInterface $em): bool
    {
        return true;
    }
}