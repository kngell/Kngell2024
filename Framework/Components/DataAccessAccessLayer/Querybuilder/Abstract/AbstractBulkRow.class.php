<?php

declare(strict_types=1);

abstract class AbstractBulkRow extends AbstractRules implements QueryRulesInterface
{
    use ChangeDetectionTrait;

    private ?string $columnList = null;
    private array $allColumns = [];

    public function __construct(
        EntityManagerInterface $em,
        string $method,
        QueryState $state,
        private array|CollectionInterface $rowValueData,
        ?string $customAlias = null,
    ) {
        parent::__construct($em, $method, $customAlias, $state);
    }

    public function getRule(array $rowValuesData): string
    {
        $normalizedUserData = $this->normalize($rowValuesData);
        $consolidatedData = $this->getConsolidateDataWithIds($normalizedUserData);

        if (empty($consolidatedData)) {
            return '';
        }
        $this->extractColumnList($consolidatedData);
        return $this->buildBulkSql($consolidatedData);
    }

    public function getColumnList(): ?string
    {
        return $this->columnList;
    }

    public function getParameters(): array
    {
        return $this->state->parameters;
    }

    // public function getBulkTableHelper(): TablesAliasHelper
    // {
    //     $tableHelper = $this->em->getTableAliasHelper();
    //     $tableHelper->setTables(['bulk_update']);
    //     $tableHelper->setTable('bulk_update');
    //     return $tableHelper;
    // }

    /**
     * Get entity for a specific row index.
     */
    public function getEntityForRow(int $rowIndex): ?Entity
    {
        $entities = $this->em->getEntity(); // Should be getEntities() plural
        return $entities[$rowIndex] ?? null;
    }

    abstract protected function buildBulkSql(array $data): string;

    protected function extractColumnList(array $data): void
    {
        $this->allColumns = [];

        foreach ($data as $row) {
            foreach (array_keys($row) as $column) {
                $this->allColumns[$column] = true;
            }
        }

        $this->allColumns = array_keys($this->allColumns);

        if (!empty($this->allColumns)) {
            $this->columnList = '`' . implode('`, `', $this->allColumns) . '`';
        }
    }

    protected function getColumnListWithoutPrimaryKey(): string
    {
        $keyField = $this->em->getEntityKeyField();
        $columns = array_filter(
            $this->allColumns,
            fn ($col) => $col !== $keyField,
        );

        return '`' . implode('`, `', $columns) . '`';
    }

    protected function normalize(array $arrayInput): array
    {
        if (empty($arrayInput)) {
            return [];
        }

        if ($arrayInput instanceof CollectionInterface) {
            return $this->normalizeEntityCollection($arrayInput);
        }
        if (is_array($arrayInput)) {
            if (ArrayUtils::isArrayList($arrayInput)) {
                return $this->normalizeArrayList($arrayInput);
            }
            if (ArrayUtils::isObjectList($arrayInput)) {
                return $this->normalizeEntityObjectList($arrayInput);
            }
            if (ArrayUtils::isAssoc($arrayInput)) {
                return [$this->validateAndEnsureId($arrayInput)];
            }
        }

        throw new InvalidArgumentException(
            'Bulk update data must be: ' .
            '1) Array of associative arrays (ArrayList), ' .
            '2) Array of Entity objects (ObjectList), or ' .
            '3) Single associative array. ' .
            'Received: ' . gettype($arrayInput) .
            (is_array($arrayInput) ? ' with ' . count($arrayInput) . ' elements' : ''),
        );
    }

    protected function setColumnList(?string $columnList): void
    {
        $this->columnList = $columnList;
    }

    private function normalizeEntityCollection(CollectionInterface $collection): array
    {
        $normalized = [];

        foreach ($collection as $item) {
            if (!$item instanceof Entity) {
                throw new InvalidArgumentException(
                    'CollectionInterface for bulk update must contain only Entity objects. ' .
                    'Found: ' . (is_object($item) ? get_class($item) : gettype($item)),
                );
            }
            $normalized[] = $this->entityToArray($item);
        }

        return $normalized;
    }

    private function normalizeEntityObjectList(array $objectList): array
    {
        $normalized = [];

        foreach ($objectList as $object) {
            if (!$object instanceof Entity) {
                throw new InvalidArgumentException(
                    'ObjectList for bulk update must contain only Entity objects. ' .
                    'Found: ' . (is_object($object) ? get_class($object) : gettype($object)),
                );
            }

            // Check if entity has changes
            if (!$object->hasChanges()) {
                continue;
            }

            $normalized[] = $this->entityToArray($object);
        }

        return $normalized;
    }

    private function normalizeArrayList(array $arrayList): array
    {
        $normalized = [];
        $keyField = $this->em->getEntityKeyField();

        foreach ($arrayList as $index => $row) {
            if (!is_array($row) || !ArrayUtils::isAssoc($row)) {
                throw new InvalidArgumentException(
                    'Bulk update data must be an array of associative arrays. ' .
                    "Element at index {$index} is: " . gettype($row),
                );
            }

            // Validate row has the exact key field
            $normalizedRow = $this->validateAndEnsureId($row, $index);
            $normalized[] = $normalizedRow;
        }

        return $normalized;
    }

    private function entityToArray(Entity $entity): array
    {
        $keyField = $this->em->getEntityKeyField();
        $id = $entity->getEntityPrimarykeyValue();

        if ($id === null || $id === '') {
            throw new InvalidArgumentException(
                'Entity for bulk update must have a valid non-empty primary key value. ' .
                'Entity: ' . get_class($entity),
            );
        }
        $dirtyData = $entity->getDirtyData();

        if (!is_array($dirtyData)) {
            $dirtyData = [];
        }

        $dirtyData[$keyField] = $id;

        return $dirtyData;
    }

    private function validateAndEnsureId(array $row, ?int $index = null): array
    {
        $keyField = $this->em->getEntityKeyField();

        if (!isset($row[$keyField])) {
            $indexInfo = $index !== null ? " at index $index" : '';
            throw new InvalidArgumentException(
                "Bulk update row{$indexInfo} must contain the primary key field '{$keyField}'. " .
                'Available fields: ' . implode(', ', array_keys($row)),
            );
        }

        if ($row[$keyField] === null || $row[$keyField] === '') {
            $indexInfo = $index !== null ? " at index $index" : '';
            throw new InvalidArgumentException(
                "Bulk update row{$indexInfo} primary key '{$keyField}' cannot be null or empty.",
            );
        }

        return $row;
    }

    private function findIdInRow(array $row): mixed
    {
        $keyField = $this->em->getEntityKeyField();

        // Try common variations
        $possibleFields = [
            $keyField,
            'id', 'ID', 'Id',
            'uuid', 'UUID', 'Uuid',
        ];

        foreach ($possibleFields as $field) {
            if (isset($row[$field])) {
                return $row[$field];
            }
        }

        return null;
    }
}