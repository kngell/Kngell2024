<?php

declare(strict_types=1);
class SetKeyValuesRules extends AbstractConditionRules
{
    /**
     * @param EntityManagerInterface $em
     * @param ?QueryBuilder $builder
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
        ?QueryBuilder $builder,
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

    public function getRule(array $fieldsValues): string
    {
        $tblh = $this->em->getTableAliasHelper()->setTables($this->tables);
        $fieldsValues = $this->normalize($fieldsValues);
        $strKeyValue = '';
        foreach ($fieldsValues as $field => $value) {
            $prefix = $this->paramPrefix($field);
            // list($table, $alias) = $tblh->get($this->builder->getCurrentTableName(), $this->tableAlias, $this->aliasCheck);
            // $alias = ! empty($alias) ? $alias . '.' : '';
            $end = $field !== array_key_last($fieldsValues) ? ', ' : '';
            $strKeyValue .= $field . ' = :' . $prefix . '_' . $field . $end;
            $this->parameters[$prefix . '_' . $field] = $value;
        }

        return $strKeyValue;
    }

    protected function normalize(array $fieldsValues) : array
    {
        $normalizedFv = [];
        if (empty($fieldsValues)) {
            $normalizedFv = $this->em->getEntityProperties();
            unset($normalizedFv[$this->em->getEntityKeyField()]);
        } elseif (isset($fieldsValues[0]) && is_string($fieldsValues[0])) {
            $normalizedFv[$fieldsValues[0]] = $fieldsValues[1];
            unset($fieldsValues[0]);
            unset($fieldsValues[1]);
            $fieldsValues = $this->initValues($fieldsValues);
            $normalizedFv = array_merge($normalizedFv, $this->normalize($fieldsValues));
        } else {
            $currentArr = array_splice($fieldsValues, 0, 1);
            if (ArrayUtils::isMultidimentional($currentArr)) {
                $currentArr = ArrayUtils::first($currentArr);
            }
            $numerickeys = array_filter(array_keys($currentArr), 'is_int');
            if (ArrayUtils::isAssoc($currentArr) && empty($numerickeys)) {
                foreach ($currentArr as $field => $value) {
                    $normalizedFv[$field] = $value;
                    unset($currentArr[$field]);
                }
            }
            if (! empty($currentArr)) {
                $normalizedFv = array_merge($normalizedFv, $this->normalize($currentArr));
            }
        }

        if (! empty($fieldsValues)) {
            $normalizedFv = array_merge($normalizedFv, $this->normalize($fieldsValues));
        }
        return $normalizedFv;
    }

    private function initValues(array $array) : array
    {
        $numerickeys = array_filter(array_keys($array), 'is_int');
        if (ArrayUtils::isAssoc($array) && ! empty($numerickeys)) {
            $array = array_values($array);
        }
        return $array;
    }
}
