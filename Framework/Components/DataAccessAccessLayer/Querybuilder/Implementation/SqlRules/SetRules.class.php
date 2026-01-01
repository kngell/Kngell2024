<?php

declare(strict_types=1);

class SetRules extends AbstractRules implements QueryRulesInterface
{
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
        $setParts = [];
        $setData = $this->normalize($setData);

        foreach ($setData as $row) {
            $setParts[] = $this->buildSetCondition($row);
        }

        return implode(', ', $setParts);
    }

    protected function normalize(array $setData): array
    {
        $newRows = [];
        $setData = !empty($setData) ? $setData : $this->em->getEntityData();
        $dirtyData = $this->em->getDirtyData();
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

    private function buildSetCondition(array $condition): string
    {
        $tableHelper = $this->em->getTableAliasHelper();

        list($table, $column) = $tableHelper->mapTableColumn($condition['left']);
        list($table, $alias) = $tableHelper->get($table, $this->parameterManager->getTableAlias(), $this->parameterManager->getAliasCheck());

        $leftSide = !empty($alias) ? $alias . '.' . $column : $column;
        $rightSide = $this->buildSetValue($condition, $tableHelper);

        return $leftSide . ' ' . $condition['operator'] . ' ' . $rightSide;
    }

    private function buildSetValue(array $condition, TablesAliasHelper $tableHelper): string
    {
        $rawValue = $condition['right'];
        $dbFieldName = $tableHelper->extractColumnName($condition['left']);
        $entity = $this->em->getEntity();

        // Normalize the value using your type system
        $normalizedValue = $this->normalizer->normalizeValueForDatabase(
            $dbFieldName,
            $rawValue,
            $entity,
        );

        // Generate parameter name
        $parameterName = $tableHelper->generateUniqueParameterName(
            $condition['left'],
            $this->parameterManager->getParameters(),
        );

        // Store normalized parameter
        $this->parameterManager->addParameter($parameterName, $normalizedValue);

        return ':' . $parameterName;
    }
}