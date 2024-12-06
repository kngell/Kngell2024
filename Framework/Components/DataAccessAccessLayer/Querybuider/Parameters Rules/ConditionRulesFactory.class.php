<?php

declare(strict_types=1);

final class ConditionRulesFactory
{
    protected EntityManagerInterface $em;
    protected ?QueryBuilder $builder;
    protected array $bind_arr = [];
    protected array $tableAlias = [];
    protected array $aliasCheck = [];
    protected array $parameters = [];
    protected string $method;
    protected array $tables;

    /**
     * @param EntityManagerInterface $em
     * @param QueryBuilder $builder
     * @param array $bind_arr
     * @param array $tableAlias
     * @param array $aliasCheck
     * @param array $parameters
     * @param array $tables
     */
    public function __construct(
        EntityManagerInterface $em,
        ?QueryBuilder $builder,
        array $bind_arr,
        array $tableAlias,
        array $aliasCheck,
        array $parameters,
        array $tables
    ) {
        $this->em = $em;
        $this->builder = $builder;
        $this->bind_arr = $bind_arr;
        $this->tableAlias = $tableAlias;
        $this->aliasCheck = $aliasCheck;
        $this->parameters = $parameters;
        $this->tables = $tables;
    }

    public function create(string $method, array|null $conditions) : AbstractConditionRules
    {
        $rule = ConditionRuleType::getRuleType($method);
        return match (true) {
            $rule->value === 'in' || (null !== $conditions && isset($conditions[1]) && is_string($conditions[1]) && in_array(strtolower($conditions[1]), ['in', 'notin'])) => new InNotInRules(
                $this->em,
                $this->builder,
                $this->bind_arr,
                $this->tableAlias,
                $this->aliasCheck,
                $this->parameters,
                $this->tables,
                $method
            ),
            in_array($rule->value, ['where', 'on']) => new whereConditionRule(
                $this->em,
                $this->builder,
                $this->bind_arr,
                $this->tableAlias,
                $this->aliasCheck,
                $this->parameters,
                $this->tables,
                $method
            ),
            $rule->value === 'set' => new SetKeyValuesRules(
                $this->em,
                $this->builder,
                $this->bind_arr,
                $this->tableAlias,
                $this->aliasCheck,
                $this->parameters,
                $this->tables,
                $method
            ),
            $rule->value === 'values' => new InsertRuleValues(
                $this->em,
                $this->builder,
                $this->bind_arr,
                $this->tableAlias,
                $this->aliasCheck,
                $this->parameters,
                $this->tables,
                $method
            ),
        };
    }
}
