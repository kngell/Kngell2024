<?php

declare(strict_types=1);

abstract class AbstractRules implements QueryRulesInterface
{
    protected EntityManagerInterface $em;
    protected ?string $method;
    protected array $tables;
    protected QueryState $state;
    protected TypeNormalizerInterface $normalizer;
    protected ?string $customAlias = null;

    public function __construct(EntityManagerInterface $em, ?string $method, ?string $customAlias, QueryState $state)
    {
        $this->em = $em;
        $this->method = $method;
        $this->customAlias = $customAlias;
        $this->tables = $state->tables;
        $this->state = $state;
        $this->normalizer = $em->getNormalizer();
    }

    abstract public function getRule(array $conditions): string;

    public function initialize(QueryState $state): void
    {
        $this->state = $state;
    }

    public function getState(): QueryState
    {
        return $this->state;
    }

    /**
     * @return null|string
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    abstract protected function normalize(array $arrayInput): array;

    protected function createParameter(mixed $value, string $field, TablesAliasHelper $tableHelper, ?int $index, Entity $entity): string
    {
        return $this->createParameterOrLiteralValue($value, $field, $tableHelper, $index, $entity, false);
    }

    protected function createLiteralValue(mixed $value, string $field, TablesAliasHelper $tableHelper, ?int $index, Entity $entity): string
    {
        return $this->createParameterOrLiteralValue($value, $field, $tableHelper, $index, $entity, true);
    }

    protected function createTableHelper(array $keyColumns = []): TablesAliasHelper
    {
        $helper = $this->em->getTableAliasHelper()
            ->setTables($this->tables)
            ->setConditionIndex($keyColumns);
        if ($this->state->joinContext !== null) {
            return $helper->setJoinContext($this->state->joinContext);
        }
        return $helper;
    }

    protected function getOperation(array &$conditions): ?string
    {
        $method = $this->method;

        if (isset($conditions['right']) && is_array($conditions['right'])) {
            // Use enum mapping
            $operator = SqlBuilderMethodRegistry::getDefaultOperator($method);
            if (!$operator) {
                throw new BadQueryArgumentException(
                    "The query method '{$method}' does not have a mapped operator",
                );
            }

            return $operator->value;
        }
        return null;
    }

    protected function getConditionLink(int $currentIndex): string
    {
        $link = SqlConditionLink::getFrom($this->method);

        if ($currentIndex === 0) {
            return '';
        }
        return ' ' . $link->name . ' ';
    }

    protected function prepareSubQueryState(SqlComponent $component): void
    {
        $subqueryState = new QueryState(
            tableAlias: $this->state->tableAlias,
            aliasCheck: $this->state->aliasCheck,
            parameters: $this->state->parameters,
            logicalToPhysicalMap: $this->state->logicalToPhysicalMap,
            tables: $component->getTables(),
            isSubquery: true,
            subqueryMainTable: array_key_first($component->getTables()),
        );

        $component->initializeWithDependencies($this->em->getTableAliasHelper(), $subqueryState);
    }

    protected function mergeSubQueryState(SqlComponent $component): void
    {
        $this->state = $this->state->merge($component->getState());
    }

    protected function normalizeAssociative(array $conditions): array
    {
        $newConditions = [];
        $count = count($conditions);
        if (ArrayUtils::isMultidimentional($conditions)) {
            list($scalarArr, $arrayKeys) = $this->separateScalarFromArrayKeys($conditions);
        } else {
            $scalarArr = $conditions;
            $arrayKeys = [];
        }

        foreach ($scalarArr as $left => $right) {
            $newConditions[] = [
                'left' => $left,
                'right' => $right,
                'operator' => '=',
            ];
        }
        if (!empty($arrayKeys)) {
            if ($this->isInCondition($arrayKeys) && !str_contains(strtolower($this->method), 'in')) {
                $this->method = 'whereIn';
            }
            $operator = $this->getOperation($arrayKeys);
            foreach ($arrayKeys as $key => $condition) {
                $newConditions[] = [
                    'left' => $key,
                    'right' => $condition,
                    'operator' => $operator,
                ];
            }
        }

        return $newConditions;
    }

    protected function isInCondition(array $condition): bool
    {
        return false;
    }

    private function createParameterOrLiteralValue(mixed $value, string $field, TablesAliasHelper $tableHelper, ?int $index, Entity $entity, bool $isLiteral = false): string
    {
        $dbFieldName = $tableHelper->extractColumnName($field);

        $normalizedValue = $this->normalizer->normalizeValueForDatabase(
            $dbFieldName,
            $value,
            $entity,
        );

        if ($isLiteral) {
            $sqlTypeHandler = $this->em->getSqlTypeHandler();
            $handler = $sqlTypeHandler->getForValue($normalizedValue);
            return $handler->toSqlLiteral($normalizedValue, $this->em);
        }

        $baseName = $field;
        if ($index !== null) {
            $baseName .= '_' . $index;
        }

        $parameterName = $tableHelper->generateUniqueParameterName($baseName, $this->state->parameters);

        $this->state->parameters[$parameterName] = $normalizedValue;

        return ':' . $parameterName;
    }

    private function separateScalarFromArrayKeys(array $conditions): array
    {
        $scalars = [];
        $arrays = [];

        foreach ($conditions as $key => $value) {
            if (is_array($value)) {
                $arrays[$key] = $value;
            } else {
                $scalars[$key] = $value;
            }
        }
        return [$scalars, $arrays];
    }
}