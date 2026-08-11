<?php

declare(strict_types=1);

/**
 * Executes individual UPDATE statements per row in a transaction.
 *
 * Output: UPDATE table SET col1 = :val1 WHERE id = :id;
 *         UPDATE table SET col1 = :val2 WHERE id = :id2;
 *
 * Slowest strategy - only for small datasets or last resort.
 */
class BulkRowBatchUpdates extends AbstractBulkRow
{
    private array $updateStatements = [];
    private string $tableName;

    /**
     * Override constructor to capture table name.
     */
    public function __construct(
        EntityManagerInterface $em,
        string $method,
        QueryState $state,
        private array|CollectionInterface $rowValueData,
        ?string $tableName = null,
        ?string $customAlias = null,
    ) {
        parent::__construct($em, $method, $state, $rowValueData);
        $this->tableName = $tableName ?? $this->extractTableName($em);
    }

    /**
     * Get individual UPDATE statements (for debugging or batch execution).
     */
    public function getUpdateStatements(): array
    {
        return $this->updateStatements;
    }

    /**
     * This is the main method that AbstractBulkRow expects.
     */
    protected function buildBulkSql(array $data): string
    {
        $this->updateStatements = [];
        $primaryKey = $this->extractPrimaryKey($data, $this->em);

        foreach ($data as $rowIndex => $row) {
            $updateClauses = [];
            $whereClause = null;

            foreach ($row as $column => $value) {
                if ($column === $primaryKey) {
                    $paramName = $this->generateParameterName($rowIndex, $column . '_where');
                    $this->state->addParameter($paramName, $value);
                    $whereClause = "`$column` = :$paramName";
                } else {
                    $paramName = $this->generateParameterName($rowIndex, $column);
                    $this->state->addParameter($paramName, $value);
                    $updateClauses[] = "`$column` = :$paramName";
                }
            }

            if ($whereClause && !empty($updateClauses)) {
                $setClause = implode(', ', $updateClauses);
                $this->updateStatements[] = "UPDATE `{$this->tableName}` SET $setClause WHERE $whereClause;";
            }
        }

        return implode("\n", $this->updateStatements);
    }

    private function extractPrimaryKey(array $rowValuesData, EntityManagerInterface $em): string
    {
        try {
            $keyField = $em->getEntityKeyField();
            if ($keyField) {
                return $keyField;
            }
        } catch (Throwable $e) {
            // fall through
        }

        $commonKeys = ['id', 'ID', 'Id', 'uid', 'uuid', 'pk'];
        foreach ($commonKeys as $key) {
            if (isset($rowValuesData[0][$key])) {
                return $key;
            }
        }

        $firstRow = $rowValuesData[0] ?? [];
        return array_key_first($firstRow) ?: 'id';
    }

    private function extractTableName(EntityManagerInterface $em): string
    {
        try {
            $entity = $em->getEntity();
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
        return 'batch_' . $rowIndex . '_' . $cleanColumn;
    }

    public static function supports(EntityManagerInterface $em): bool
    {
        return true;
    }
}