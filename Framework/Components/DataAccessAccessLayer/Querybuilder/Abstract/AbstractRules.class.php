<?php

declare(strict_types=1);

abstract class AbstractRules
{
    protected EntityManagerInterface $em;
    protected ?string $method;
    protected array $tables;
    protected QueryState $state;
    protected TypeNormalizerInterface $normalizer;

    public function __construct(EntityManagerInterface $em, ?string $method, QueryState $state)
    {
        $this->em = $em;
        $this->method = $method;
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

    abstract protected function normalize(array $arrayInput): array;

    protected function createParameter(mixed $value, string $field, TablesAliasHelper $tableHelper, ?int $index, Entity $entity): string
    {
        $dbFieldName = $tableHelper->extractColumnName($field);

        $normalizedValue = $this->normalizer->normalizeValueForDatabase(
            $dbFieldName,
            $value,
            $entity,
        );

        $baseName = $field;
        if ($index !== null) {
            $baseName .= '_' . $index;
        }

        $parameterName = $tableHelper->generateUniqueParameterName($baseName, $this->state->parameters);

        $this->state->parameters[$parameterName] = $normalizedValue;

        return $parameterName;
    }

    protected function getOperation(array &$conditions): string
    {
        $method = $this->method;

        // Check for explicit operator
        if (isset($conditions[1]) && is_string($conditions[1]) && Operator::exists(trim($conditions[1]))) {
            $op = $conditions[1];
            unset($conditions[1]);
            $conditions = array_values($conditions);
            return $op;
        }

        // Use enum mapping
        $operator = Operator::getOp($method);
        if (!$operator) {
            throw new BadQueryArgumentException(
                "The query method '{$method}' does not have a mapped operator",
            );
        }

        return $operator->value;
    }

    protected function getConditionLink(int $currentIndex): string
    {
        $link = SqlConditionLink::getFrom($this->method);

        if ($currentIndex === 0) {
            return '';
        }
        return ' ' . $link->name . ' ';
    }

    protected function prepareSubQueryState(SqlQueryComponent $component): void
    {
        $subqueryState = new QueryState(
            tableAlias: $this->state->tableAlias,
            aliasCheck: $this->state->aliasCheck,
            parameters: $this->state->parameters,
            bindArr: $this->state->bindArr,
            logicalToPhysicalMap: $this->state->logicalToPhysicalMap,
            tables: $component->getTables(),
            table: $this->state->table,
            isSubquery: true,
            subqueryMainTable: array_key_first($component->getTables()),
        );

        $component->initializeWithDependencies($this->em->getTableAliasHelper(), $subqueryState);
    }

    protected function mergeSubQueryState(SqlQueryComponent $component): void
    {
        $this->state = $this->state->merge($component->getState());
    }
}