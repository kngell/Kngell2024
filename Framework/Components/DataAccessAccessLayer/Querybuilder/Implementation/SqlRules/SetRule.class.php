<?php

declare(strict_types=1);

class SetRule extends AbstractRules implements QueryRulesInterface
{
    use ChangeDetectionTrait;

    public function __construct(
        EntityManagerInterface $em,
        string $method,
        QueryState $state,
        private array $setData,
    ) {
        parent::__construct($em, $method, $state);
    }

    public function getRule(array $setData): string
    {
        if (!$this->hasActionableChanges($setData, $this->em)) {
            return '';
        }

        $consolidated = $this->getConsolidatedData($setData, $this->em);

        $setParts = [];
        $normalized = $this->normalize($consolidated);

        foreach ($normalized as $row) {
            $setParts[] = $this->buildSetRow($row);
        }

        return implode(', ', $setParts);
    }

    protected function normalize(array $setData): array
    {
        $newRows = [];

        if (ArrayUtils::isAssoc($setData)) {
            return $this->normalizeAssociative($setData);
        }
        if (ArrayUtils::isArrayList($setData)) {
            foreach ($setData as $index => $batch) {
                foreach ($batch as $key => $value) {
                    $row = [$key => $value];
                    $operator = $this->getOperation($row);
                    $newRows[] = [
                        'left' => $key,
                        'right' => $value,
                        'operator' => empty($operator) ? ' = ' : $operator,
                    ];
                }
            }
            return $newRows;
        }
        $setData = ArrayUtils::fromAssocToSequential($setData);
        foreach ($setData as $row) {
            if ($row instanceof Closure) {
                $newRows[] = $row;
                continue;
            }

            $operator = $this->getOperation($row);

            $newRows[] = [
                'left' => $row[0],
                'right' => isset($row[1]) ? $row[1] : null,
                'operator' => empty($operator) ? ' = ' : $operator,
            ];

            unset($setData[0], $setData[1]);
            $remainingRows = array_values($setData);

            if (!empty($remainingRows)) {
                $newRows = array_merge($newRows, $this->normalize($remainingRows));
            }

            break;
        }

        return $newRows;
    }

    private function buildSetRow(array $row): string
    {
        $tableHelper = $this->createTableHelper();
        $tableAlias = $this->state->tableAlias;
        $aliasCheck = $this->state->aliasCheck;

        list($table, $column) = $tableHelper->mapTableColumn($row['left']);
        list($table, $alias) = $tableHelper->get($table, $tableAlias, $aliasCheck);

        $this->state->tableAlias = $tableAlias;
        $this->state->aliasCheck = $aliasCheck;

        $leftSide = !empty($alias) ? $alias . '.' . $column : $column;
        // $leftSide = $column;
        $rightSide = $this->buildSetValue($row, $tableHelper);

        return $leftSide . ' ' . $row['operator'] . ' ' . $rightSide;
    }

    private function buildSetValue(array $row, TablesAliasHelper $tableHelper): string
    {
        $rawValue = $row['right'];
        $dbFieldName = $tableHelper->extractColumnName($row['left']);
        $entityContext = $this->em->getEntityContext();

        return $this->createParameter($rawValue, $dbFieldName, $tableHelper, null, $entityContext);
    }
}
