<?php

declare(strict_types=1);

class BulkRowMultipleUpdates extends AbstractBulkRow
{
    private array $updateStatements = [];

    public function getRule(
        array $rowValuesData,
        TypeNormalizerInterface $normalizer,
        EntityManagerInterface $em,
        SqlTypeHandlerFactory $sqlTypeHandlerFactory,
    ): string {
        $this->parameters = [];
        $this->updateStatements = [];

        $primaryKey = $this->extractPrimaryKey($rowValuesData, $em);

        foreach ($rowValuesData as $rowIndex => $row) {
            $updateClauses = [];
            $whereClause = null;

            foreach ($row as $column => $value) {
                if ($column === $primaryKey) {
                    // Primary key goes in WHERE clause
                    $paramName = $this->generateParameterName($rowIndex, $column . '_where');
                    $this->parameters[$paramName] = $value;
                    $whereClause = "`$column` = :$paramName";
                } else {
                    // Regular columns go in SET clause
                    $paramName = $this->generateParameterName($rowIndex, $column);
                    $this->parameters[$paramName] = $value;
                    $updateClauses[] = "`$column` = :$paramName";
                }
            }

            if ($whereClause && !empty($updateClauses)) {
                $table = $em->table();
                $setClause = implode(', ', $updateClauses);
                $this->updateStatements[] = "UPDATE `$table` SET $setClause WHERE $whereClause;";
            }
        }

        return implode("\n", $this->updateStatements);
    }

    public function supports(EntityManagerInterface $em): bool
    {
        return true;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getColumnList(): ?string
    {
        return null;
    }

    /**
     * Get individual UPDATE statements (for debugging or batch execution).
     */
    public function getUpdateStatements(): array
    {
        return $this->updateStatements;
    }

    private function extractPrimaryKey(array $rowValuesData, EntityManagerInterface $em): string
    {
        // Try to get from entity
        try {
            $entity = $em->getEntity();
            if ($entity instanceof Entity) {
                return $entity->getEntityKeyField() ?: 'id';
            }
        } catch (Throwable $e) {
            // fall through
        }

        // Look for common primary key names in data
        $commonKeys = ['id', 'ID', 'Id', 'uid', 'uuid', 'pk'];
        foreach ($commonKeys as $key) {
            if (isset($rowValuesData[0][$key])) {
                return $key;
            }
        }

        // Default to first column
        $firstRow = $rowValuesData[0] ?? [];
        $firstColumn = array_key_first($firstRow);

        return $firstColumn ?: 'id';
    }

    private function extractTableName(EntityManagerInterface $em): string
    {
        try {
            $entity = $em->getEntity();
            if ($entity instanceof Entity) {
                return $entity->getTableName();
            }
        } catch (Throwable $e) {
            // fall through
        }

        // Fallback - you might want to pass table name differently
        return 'table';
    }
}
