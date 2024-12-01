<?php

declare(strict_types=1);

class InNotInRules extends AbstractConditionRules
{
    /**
     * @param TablesAliasHelper $tblh
     * @param QueryBuilder $builder
     * @param array $bind_arr
     * @param array $tableAlias
     * @param array $aliasCheck
     * @param array $parameters
     * @param array $tables
     */
    public function __construct(
        TablesAliasHelper $tblh,
        QueryBuilder $builder,
        array $bind_arr,
        array $tableAlias,
        array $aliasCheck,
        array $parameters,
        array $tables,
        string $method
    ) {
        $this->tblh = $tblh->setTables($tables);
        $this->builder = $builder;
        $this->bind_arr = $bind_arr;
        $this->tableAlias = $tableAlias;
        $this->aliasCheck = $aliasCheck;
        $this->parameters = $parameters;
        $this->tables = $tables;
        $this->method = $method;
    }

    public function getRule(array $conditions) : string
    {
        $conditions = $this->normalize($conditions);
        $conditions = arrayUtils::first($conditions);
        $op = $this->getOperation($conditions);
        $arrPrx = $this->arrayPrefixer($conditions['list'], null, $conditions['column']);
        list($table, $column) = $this->tblh->mapTableColumn($conditions['column']);
        list($table, $alias) = $this->tblh->get($table, $this->tableAlias, $this->aliasCheck);
        return $alias . '.' . $column . ' ' . $op . ' (' . $arrPrx . ')';
    }

    protected function normalize(array $conditions) : array
    {
        if (isset($conditions[1]) && in_array(strtolower($conditions[1]), ['in', 'notin']) && is_array($conditions[2])) {
            $newConditions = [];
            $newConditions['column'] = $conditions[0];
            $newConditions['list'] = $conditions[2];
            $newConditions['operator'] = $conditions[1];
            return [$newConditions];
        }
        return $conditions;
    }

    private function arrayPrefixer(array $values, mixed $key = null, string $field) : string
    {
        $str = '';
        if (ArrayUtils::isAssoc($values)) {
            $keys = array_keys($values);
            $values = ArrayUtils::valuesFromArray($values);
        }
        foreach ($values as $index => $value) {
            isset($keys) ? $this->arrayPrefixerField($keys, $index, $key, $field) : '';
            $str .= ':' . strval($field . $index) . ',';
            $this->bind_arr[$field . $index] = $value;
        }
        return rtrim($str, ',');
    }

    private function arrayPrefixerField(array $keys, mixed $index, mixed $key = null, string $field) : string
    {
        if (null !== $key) {
            $field = $keys[$index] . $key;
        } else {
            $field = is_numeric($keys[$index]) ? $field . $keys[$index] : $keys[$index];
        }
        return $field;
    }
}