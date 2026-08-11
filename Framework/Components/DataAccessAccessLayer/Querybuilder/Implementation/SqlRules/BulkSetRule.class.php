<?php

declare(strict_types=1);

class BulkSetRule extends AbstractRules implements QueryRulesInterface
{
    use ChangeDetectionTrait;

    public function __construct(
        EntityManagerInterface $em,
        string $method,
        QueryState $state,
        private array $setData,
        ?string $customAlias = null,
    ) {
        parent::__construct($em, $method, $customAlias, $state);
    }

    public function getRule(array $data): string
    {
        $setParts = [];

        $setData = $this->getConsolidateDataWithIds($data);

        $normalized = $this->normalize($setData);

        if (empty($normalized)) {
            return '';
        }

        $helper = $this->createTableHelper();

        $allColumns = [];
        $emData = $this->em->getEntity();

        foreach ($normalized as $item) {
            foreach (array_keys($item) as $col) {
                $allColumns[$col] = true;
            }
        }
        $columnNames = array_keys($allColumns);

        $keyField = $this->em->getEntityKeyField();

        foreach ($columnNames as $column) {
            if ($column === $keyField) {
                continue;
            }
            $setParts[] = $this->buildSetColumn($column);
        }

        return implode(', ', $setParts);
    }

    protected function normalize(array $arrayInput): array
    {
        if (ArrayUtils::isArrayList($arrayInput) || ArrayUtils::isObjectList($arrayInput)) {
            return $arrayInput;
        } else {
            throw new InvalidParameterException('The row value data must be an array of values');
        }
    }

    private function buildSetColumn(string $columnName): string
    {
        $tableHelper = $this->createTableHelper();

        list($table, $column) = $tableHelper->mapTableColumn($columnName);

        list($table, $targetAlias) = $tableHelper->get($table, $this->state->tableAlias, $this->state->aliasCheck);
        $sourceTable = $this->state->joinContext;

        list($unused, $sourceAlias) = $tableHelper->get($sourceTable, $this->state->tableAlias, $this->state->aliasCheck);

        return sprintf(
            '%s.`%s` = COALESCE(%s.`%s`, %s.`%s`)',
            $targetAlias,
            $column,
            $sourceAlias,
            $column,
            $targetAlias,
            $column,
        );
    }
}