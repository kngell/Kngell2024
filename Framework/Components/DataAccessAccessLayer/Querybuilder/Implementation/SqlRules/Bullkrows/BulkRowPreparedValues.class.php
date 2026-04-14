<?php

declare(strict_types=1);

class BulkRowPreparedValues extends AbstractBulkRow
{
    public function getRule(
        array $rowValuesData,
        TypeNormalizerInterface $normalizer,
        EntityManagerInterface $em,
        SqlTypeHandlerFactory $sqlTypeHandlerFactory,
    ): string {
        $this->parameters = [];
        $rows = [];

        foreach ($rowValuesData as $rowIndex => $item) {
            $orderedRow = [];

            foreach ($item as $column => $value) {
                $paramName = $this->generateParameterName($rowIndex, $column);
                $this->parameters[$paramName] = $value;
                $orderedRow[] = ':' . $paramName;
            }

            $rows[] = '(' . implode(', ', $orderedRow) . ')';
        }

        return "VALUES\n        " . implode(",\n        ", $rows);
    }

    public function supports(EntityManagerInterface $em): bool
    {
        return true;
    }

    private function generateParameterName(int $rowIndex, string $column): string
    {
        $cleanColumn = preg_replace('/[^a-z0-9_]/i', '_', $column);
        return 'bulk_' . $rowIndex . '_' . $cleanColumn;
    }
}
