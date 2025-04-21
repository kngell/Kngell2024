<?php

declare(strict_types=1);

class Conditions extends MainQuery
{
    protected array $tables = [];
    protected mixed $conditions;
    protected ?QueryBuilder $builder;

    public function __construct(EntityManagerInterface $em, ?QueryBuilder $builder = null, array $tables = [], mixed ...$conditions)
    {
        $this->em = $em;
        $this->builder = $builder;
        $this->tables = $tables;
        $this->conditions = $conditions;
        $this->methodList = MethodList::getInstance();
    }

    public function getSql(): array
    {
        $rule = '';
        $conditions = ArrayUtils::first($this->conditions);
        $ConditionRule = $this->conditionRuleFactory()->create($this->method, $conditions);
        $rule .= $ConditionRule->getRule($conditions);
        $this->bind_arr = $ConditionRule->getBindArr();
        $this->tableAlias = $ConditionRule->getTableAlias();
        $this->aliasCheck = $ConditionRule->getAliasCheck();
        $this->parameters = $ConditionRule->getParameters();
        return [
            $this->braceOpen() . $rule . $this->braceClose(),
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->bind_arr,
        ];
    }

    /**
     * Set the value of tables.
     */
    public function setTables($tables): self
    {
        $this->tables = $tables;
        return $this;
    }

    private function conditionRuleFactory() : ConditionRulesFactory
    {
        return new ConditionRulesFactory(
            $this->em,
            $this->builder,
            $this->bind_arr,
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->tables
        );
    }

    private function braceOpen() : string
    {
        if ($this->method === 'set') {
            return '';
        }
        return '(';
    }

    private function braceClose(): string
    {
        if ($this->method === 'set') {
            return '';
        }
        return ')';
    }
}
