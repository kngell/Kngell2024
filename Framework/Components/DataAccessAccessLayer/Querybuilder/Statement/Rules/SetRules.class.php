<?php

declare(strict_types=1);

class SetRules extends AbstractRules implements QueryRulesInterface
{
    private ParameterManager $parameterManager;
    private TypeNormalizerInterface $normalizer;

    public function __construct(
        string $method,
        private mixed $conditions,
        EntityManagerInterface $em,
        array $tables,
    ) {
        $this->em = $em;
        $this->method = $method;
        $this->tables = $tables;
        $this->parameterManager = new ParameterManager();
        $this->normalizer = $em->getNormalizer();
    }

    public function getRule(array $conditions): string
    {
        $this->parameterManager->clear();

        $setParts = [];
        $normalizedConditions = $this->normalize($conditions);

        foreach ($normalizedConditions as $condition) {
            $setParts[] = $this->buildSetCondition($condition);
        }

        return implode(', ', $setParts);
    }

    // ... implement required interface methods ...
    public function getBindArr(): array
    { /* ... */
    }

    public function getTableAlias(): array
    { /* ... */
    }

    public function getAliasCheck(): array
    { /* ... */
    }

    public function getParameters(): array
    { /* ... */
    }

    protected function normalize(array $conditions): array
    {
        // Handle associative arrays for SET operations
        if (ArrayUtils::isAssoc($conditions)) {
            return $this->normalizeAssociative($conditions);
        }

        // Handle sequential arrays with explicit operators
        return $this->normalizeSequential($conditions);
    }

    private function normalizeAssociative(array $conditions): array
    {
        $normalized = [];

        foreach ($conditions as $field => $value) {
            $normalized[] = [
                'left' => $field,
                'right' => $value,
                'operator' => '=',
            ];
        }

        return $normalized;
    }

    private function normalizeSequential(array $conditions): array
    {
        $normalized = [];

        foreach ($conditions as $condition) {
            if (is_array($condition) && count($condition) >= 2) {
                $normalized[] = [
                    'left' => $condition[0],
                    'right' => $condition[1],
                    'operator' => $condition[2] ?? '=',
                ];
            }
        }

        return $normalized;
    }

    private function buildSetCondition(array $condition): string
    {
        $tableHelper = $this->createTableHelper();

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