<?php

declare(strict_types=1);
abstract class AbstractConditionRules
{
    protected EntityManagerInterface $em;
    protected ?QueryBuilder $builder;
    protected array $bind_arr = [];
    protected array $tableAlias = [];
    protected array $aliasCheck = [];
    protected array $parameters = [];
    protected string $method;
    protected array $tables;

    abstract public function getRule(array $conditions) : string;

    /**
     * @return array
     */
    public function getBindArr(): array
    {
        return $this->bind_arr;
    }

    /**
     * @return array
     */
    public function getTableAlias(): array
    {
        return $this->tableAlias;
    }

    /**
     * @return array
     */
    public function getAliasCheck(): array
    {
        return $this->aliasCheck;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function getTables(): array
    {
        return $this->tables;
    }

    abstract protected function normalize(array $arrayInput) : array;

    protected function getOperation(array &$conditions) : string
    {
        $method = $this->method;
        if (isset($conditions[1]) && is_string($conditions[1]) && Operator::exists(trim($conditions[1]))) {
            $op = $conditions[1];
            unset($conditions[1]);
            $conditions = array_values($conditions);
            return $op;
        }
        if (0 === count(array_diff(['column', 'list'], array_keys($conditions))) && ! str_contains(strtolower($this->method), 'in')) {
            $method = $this->strMethod($conditions['operator']);
        }
        $op = Operator::getOp($method);
        if (! $op) {
            throw new BadQueryArgumentException("The query '{$this->method}' does not have any specified operator");
        }
        return strtoupper(Operator::getOp($method)->value);
    }

    protected function paramPrefix(string $field) : string
    {
        $tblh = $this->em->getTableAliasHelper()->setTables($this->tables);
        $paramPrefix = $tblh->getToken()->generate(2, $field);
        while (in_array($paramPrefix, array_keys($this->parameters))) {
            $paramPrefix = $tblh->getToken()->generate(2, $field);
        }
        return $paramPrefix;
    }

    private function strMethod(string $method) : string
    {
        if (strtolower($method) === 'notin') {
            return str_replace('i', 'I', $method);
        }
        return $method;
    }
}