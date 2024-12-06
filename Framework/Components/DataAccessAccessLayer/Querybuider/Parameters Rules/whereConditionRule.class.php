<?php

declare(strict_types=1);

class whereConditionRule extends AbstractConditionRules
{
    /**
     * @param EntityManagerInterface $em
     * @param QueryBuilder $builder
     * @param array $bind_arr
     * @param array $tableAlias
     * @param array $aliasCheck
     * @param array $parameters
     * @param array $tables
     * @param string $method
     * @return void
     */
    public function __construct(
        EntityManagerInterface $em,
        QueryBuilder $builder,
        array $bind_arr,
        array $tableAlias,
        array $aliasCheck,
        array $parameters,
        array $tables,
        string $method
    ) {
        $this->em = $em;
        $this->builder = $builder;
        $this->bind_arr = $bind_arr;
        $this->tableAlias = $tableAlias;
        $this->aliasCheck = $aliasCheck;
        $this->parameters = $parameters;
        $this->tables = $tables;
        $this->method = $method;
    }

    public function getRule(array $conditions): string
    {
        $strCondition = '';
        $conditions = $this->normalize($conditions);
        foreach ($conditions as $condition) {
            if ($condition instanceof Closure) {
                $strCondition .= $this->closure($condition);
            } else {
                $strCondition .= $this->clink($conditions, $condition) . $this->cbOpen($conditions) . $this->condition($condition) . $this->cbClose($conditions);
            }
        }
        return $strCondition;
    }

    protected function normalize(array $conditions) : array
    {
        $newConditions = [];
        $conditions = ArrayUtils::FromAssocToSequential($conditions);
        $op = $this->getOperation($conditions);
        $newConditions[] = [
            'left' => $conditions[0],
            'right' => $conditions[1],
            'operator' => empty($op) ? ' = ' : $op,
        ];
        unset($conditions[0]);
        unset($conditions[1]);
        $conditions = array_values($conditions);
        if (! empty($conditions)) {
            if ($conditions[0] instanceof Closure) {
                return array_merge($newConditions, $conditions);
            }
            $newConditions = array_merge($newConditions, $this->normalize($conditions));
        }
        return $newConditions;
    }

    private function cLink(array $conditions, $condition) : string
    {
        $key = array_key_first($conditions);
        if ($conditions[$key] === $condition) {
            return '';
        }
        return ' AND ';
    }

    private function cbOpen(array $conditions) : string
    {
        if (count($conditions) > 1) {
            return '(';
        }
        return '';
    }

    private function condition(array $condition) : string
    {
        $tblh = $this->em->getTableAliasHelper()->setTables($this->tables);
        list($table1, $column1) = $tblh->mapTableColumn($condition['left']);
        list($table1, $alias1) = $tblh->get($table1, $this->tableAlias, $this->aliasCheck);
        $alias1 = ! empty($alias1) ? $alias1 . '.' : '';
        if (Statement::exists($this->method) && in_array($this->method, array_merge(Statement::getFamily('where'), Statement::getFamily('having')))) {
            $stmt = $tblh->getToken()->generate(2, $condition['left']);
            while (in_array($stmt, array_keys($this->parameters))) {
                $stmt = $tblh->getToken()->generate(2, $condition['left']);
            }
            $right = ':' . $stmt . '_' . $condition['left'];
            $this->parameters[$stmt . '_' . $condition['left']] = $condition['right'];
        } else {
            list($table2, $column2) = $tblh->mapTableColumn($condition['right']);
            list($table2, $alias2) = $tblh->get($table2, $this->tableAlias, $this->aliasCheck);
            $alias2 = ! empty($alias2) ? $alias2 . '.' : '';
            $right = $alias2 . $column2;
        }
        return  $alias1 . $column1 . ' ' . $condition['operator'] . ' ' . $right;
    }

    private function closure(Closure $condition) : string
    {
        $condition->__invoke($this->builder);
        $where = $this->builder->getWhere();
        $where->setTableAlias($this->tableAlias)
            ->setAliasCheck($this->aliasCheck)
            ->setParameters($this->parameters)
            ->setBindArr($this->bind_arr);
        /** @var Conditions $child */
        foreach ($where->getChildren()->all() as $child) {
            $child->setTables($this->tables);
        }
        $results = $where->getSql();
        $this->tableAlias = $results[1];
        $this->aliasCheck = $results[2];
        $this->parameters = $results[3];
        $this->bind_arr = $results[4];
        return str_replace('WHERE', '', $results[0]);
    }

    private function cbClose(array $conditions) : string
    {
        if (count($conditions) > 1) {
            return ')';
        }
        return '';
    }
}