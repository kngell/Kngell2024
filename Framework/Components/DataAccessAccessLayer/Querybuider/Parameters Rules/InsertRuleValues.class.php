<?php

declare(strict_types=1);

class InsertRuleValues extends AbstractConditionRules
{
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

    public function getRule(array|null $values): string
    {
        $rule = '';
        $values = $this->normalize($values);
        foreach ($values as $field => $value) {
            $prefix = $this->paramPrefix($field);
            $end = $field !== array_key_last($values) ? ', ' : '';
            $rule .= ':' . $prefix . '_' . $field . $end;
            $this->parameters[$prefix . '_' . $field] = $value;
        }
        return $rule;
    }

    // public function getSql(): array
    // {
    //     $values = $this->values();
    //     $newColumns = '(' . rtrim(implode(', ', $values), ', ') . ')' . $this->end();
    //     return [
    //         $newColumns,
    //         $this->tableAlias,
    //         $this->aliasCheck,
    //         $this->parameters,
    //         $this->bind_arr,
    //     ];
    // }

    protected function normalize(array|null $arrayInput): array
    {
        $values = $arrayInput;
        if (is_null($arrayInput)) {
            return $this->em->getEntityProperties();
        }
        return $values;
    }

    // private function values() : array
    // {
    //     $values = $this->values;
    //     if (ArrayUtils::isMultidimentional($values)) {
    //         $values = ArrayUtils::flattenArrayRecursive($this->values);
    //     }
    //     $valuesDbleCheck = ArrayUtils::first($values);
    //     if (is_array($valuesDbleCheck) && empty($valuesDbleCheck)) {
    //         $properties = $this->em->getEntityProperties();
    //         return array_values($properties);
    //     }
    //     return $values;
    // }

    private function end() : string
    {
        if ($this->method === 'fields') {
            return '';
        }
        if ($this->method === 'values') {
            return ',';
        }
        return '';
    }
}