<?php

declare(strict_types=1);

class BulkRowRuleMariaDb extends AbstractRules implements QueryRulesInterface
{
    use ChangeDetectionTrait;

    private bool $usePreparedRows = false;
    private null|string $columnList = null;

    public function __construct(
        EntityManagerInterface $em,
        string $method,
        QueryState $state,
        private array|CollectionInterface $rowValueData,
    ) {
        parent::__construct($em, $method, $state);
    }

    public function getRule(array $rowValuesData): string
    {
        $rowValuesData = $this->getConsolidateDataWithIds($rowValuesData, $this->em);

        $normalized = $this->normalize($rowValuesData);

        if (empty($normalized)) {
            return '';
        }

        $allColumns = [];
        $emData = $this->em->getEntity();

        foreach ($normalized as $item) {
            foreach (array_keys($item) as $col) {
                $allColumns[$col] = true;
            }
        }
        $columnNames = array_keys($allColumns);

        $rows = [];

        foreach ($normalized as $rowIndex => $item) {
            $entity = $this->getRelatedEntity($rowIndex, $emData);
            $orderedRow = [];
            foreach ($columnNames as $colIndex => $column) {
                $val = $item[$column] ?? null;
                $orderedRow[] = $this->formatValue($val, $column, $entity);
            }

            $rows[] = 'ROW(' . implode(', ', $orderedRow) . ')';
        }

        $valuesList = implode(",\n        ", $rows);

        $this->columnList = implode(', ', array_map(fn ($c) => "`$c`", $columnNames));
        return "VALUES $valuesList";
    }

    /**
     * @return null|string
     */
    public function getColumnList(): ?string
    {
        return $this->columnList;
    }

    protected function formatValue(mixed $value, string $column, ?Entity $entity): string
    {
        if ($value === null) {
            return 'NULL';
        }

        $normalized = $this->normalizer->normalizeValueForDatabase($column, $value, $entity);

        if (is_numeric($normalized)) {
            return (string) $normalized;
        }

        if (is_string($normalized) && str_starts_with($normalized, '0x')) {
            if (preg_match('/^0x[0-9a-fA-F]+$/', $normalized)) {
                return $normalized;
            }
        }

        return $this->em->quote((string) $normalized);
    }

    protected function normalize(array $arrayInput): array
    {
        if (ArrayUtils::isArrayList($arrayInput) || ArrayUtils::isObjectList($arrayInput)) {
            return $arrayInput;
        } else {
            throw new InvalidParameterException('The row value data must be an array of values');
        }
    }

    private function getRelatedEntity(int $index, array|CollectionInterface $emData): ?Entity
    {
        if (!$this->isCollection($emData)) {
            throw new BadQueryArgumentException('Invalid data Type');
        }
        return $emData[$index];
    }

    private function isCollection(array|CollectionInterface $emData): bool
    {
        return $emData instanceof CollectionInterface || !ArrayUtils::isArrayList($emData) || ArrayUtils::isObjectList($emData);
    }
}
