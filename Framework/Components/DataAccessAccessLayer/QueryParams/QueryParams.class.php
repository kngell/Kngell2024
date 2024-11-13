<?php

declare(strict_types=1);

class QueryParams extends AbstractQueryParams
{
    public function __construct(string $tableSchema)
    {
        $this->tableSchema = $tableSchema;
    }

    public function table(?string $tbl = null, mixed $columns = null) : self
    {
        $this->reset();
        $tbl = $this->parseTable($tbl);
        $this->query_params['table_join'] = [$tbl != null ? $tbl : $this->tableSchema => $columns != null ? $columns : ['*']];
        $this->addTableToOptions($tbl);

        return $this;
    }

    public function params(?string $repositoryMethod = null) : array
    {
        $this->getSelectors();

        return match ($repositoryMethod) {
            'findOneBy' => [$this->query_params['conditions'] ?? [],  $this->query_params['options'] ?? []],
            'findBy','findBySearch' => [$this->query_params['selectors'] ?? [], $this->query_params['conditions'] ?? [], $this->query_params['parameters'] ?? [], $this->query_params['options'] ?? []],
            'delete','update' => [$this->query_params['conditions'] ?? []],
            'delete','update' => [$this->query_params['conditions'] ?? []],
        };
    }

    public function join(?string $tbl = null, mixed $columns = null, string $joinType = 'INNER JOIN') : self
    {
        $tbl = $this->parseTable($tbl);
        $this->key('table_join');
        if (!array_key_exists($tbl, $this->query_params['table_join'])) {
            $this->query_params['table_join'] += [$tbl != null ? $tbl : $this->tableSchema => $columns != null ? $columns : ['*']];
            $this->key('options');
            $this->query_params['options']['join_rules'][] = $joinType;
            $this->addTableToOptions($tbl);

            return $this;
        }
        throw new Exception('Cannot join the same table ' . $tbl);
    }

    public function leftJoin(?string $tbl = null, mixed $columns = null) : self
    {
        return $this->join($tbl, $columns, 'LEFT JOIN');
    }

    public function rightJoin(?string $tbl = null, mixed $columns = null) : self
    {
        return $this->join($tbl, $columns, 'RIGHT JOIN');
    }

    public function on(...$params) : self
    {
        $this->key('options');
        $tableIndex = 0;
        foreach ($params as $key => $join_params) {
            if (is_array($join_params) && !empty($join_params)) {
                foreach ($join_params as $k => $arg) {
                    if (is_array($arg)) {
                        $this->getParams($k, $arg);
                    } else {
                        $this->getJoinOptions($k, $arg, $key, $k + $tableIndex);
                    }
                }
                $tableIndex++;
            }
        }

        return $this;
    }

    public function where(array $conditions, ?string $op = null, ?string $whereType = null) : self
    {
        if (isset($conditions) && !empty($conditions)) {
            foreach ($conditions as $key => $value) {
                $whereParams = $this->whereParams($conditions, $key, $value);
                if (is_string($key)) {
                    if (!is_null($whereType)) {
                        list($key, $tbl) = $this->getField($key);
                        $key = $key . '|' . 'IN';
                        $whereParams['tbl'] = $tbl == '' ? $this->tableSchema : $tbl;
                    }
                    list($whereParams['field'], $whereParams['operator']) = $this->fieldOperator($key);
                    list($whereParams['value'], $table) = $this->fieldValue($whereParams['value'], $whereType);
                    $whereParams['tbl'] = !array_key_exists('tbl', $whereParams) && !empty($table) ? $table : $whereParams['tbl'];
                    is_null($op) ? $this->query_params['conditions'] += $this->condition($whereParams) : $this->query_params['conditions'][$op] += $this->condition($whereParams);
                    $this->conditionBreak = [];
                } else {
                    $this->conditionBreak = $whereParams;
                }
            }

            return $this;
        }
    }

    public function whereIn(array $conditions, ?string $op = null) : self
    {
        return $this->where($conditions, null, 'IN');
        // if (isset($conditions) && !empty($conditions)) {
        //     foreach ($conditions as $key => $value) {
        //         $whereParams = $this->whereParams($conditions, $key, $value);
        //         if (is_string($key)) {
        //             list($whereParams['field'], $whereParams['operator']) = $this->fieldOperator($key);
        //             is_null($op) ? $this->query_params['conditions'] += $this->condition($whereParams) : $this->query_params['conditions'][$op] += $this->condition($whereParams);
        //             $this->conditionBreak = [];
        //         } else {
        //             $this->conditionBreak = $whereParams;
        //         }
        //     }
        //     return $this;
        // }
    }

    public function and(array $cond, string $op = 'and') : self
    {
        if (isset($cond) && !empty($cond)) {
            if (!array_key_exists($op, $this->query_params['conditions'])) {
                $this->query_params['conditions'][$op] = [];
            }

            return $this->where($cond, $op);
        }
    }

    public function or(array $cond) : self
    {
        return $this->and($cond, 'or');
    }

    public function build() : array
    {
        return $this->query_params;
    }

    public function groupBy(...$groupByAry) : self
    {
        $this->key('options');
        foreach ($groupByAry as $param) {
            if (is_string($param)) {
                $this->query_params['options']['group_by'][] = $param;
            } elseif (is_array($param)) {
                $this->query_params['options']['group_by'][] = $param[key($param)] . '.' . key($param);
            }
        }

        return $this;
    }

    public function orderBy(array $orderByAry) : self
    {
        $this->key('options');
        foreach ($orderByAry as $tbl => $field) {
            $tbl = is_numeric($tbl) ? $this->query_params['options']['table'][0] : $tbl;
            if (str_contains($field, '|')) {
                $parts = explode('|', $field);
                if (is_array($parts)) {
                    $this->query_params['options']['orderby'][] = is_numeric($tbl) ? $this->current_table . '.' . $parts[0] . ' ' . $parts[1] : $tbl . '.' . $parts[0] . ' ' . $parts[1];
                }
            } else {
                $this->query_params['options']['orderby'][] = $tbl . '.' . $field;
            }
        }

        return $this;
    }

    public function return(string $str) : self
    {
        $this->key('options');
        $this->query_params['options']['return_mode'] = $str;

        return $this;
    }

    public function parameters(array $params) : self
    {
        if (!array_key_exists('parameters', $this->query_params)) {
            $this->query_params['parameters'] = [];
        }

        return $this->aryParams($params, 'parameters');
    }

    public function recursiveQuery(self $query_recursive)
    {
        list($selectors, $conditions, $parameters, $options) = $query_recursive->params('findBy');
        $this->query_params['options']['recursive'] = $query_recursive->build();
        $this->query_params['options']['recursive']['selectors'] = $selectors;
        $this->query_params['options']['recursive']['conditions'] = $conditions;
        $this->query_params['options']['recursive']['parameters'] = $parameters;
        $this->query_params['options']['recursive']['options'] = $options;

        return $this;
    }

    public function recursive(string $parentID, string $id, array $tbl_recursive = []) : self
    {
        $this->key('options');
        $this->query_params['options']['recursive']['parentID'] = $parentID;
        $this->query_params['options']['recursive']['id'] = $id;
        $this->recursiveCount();

        return $this;
    }

    private function aryParams(array $params, string $name) : self
    {
        if (isset($params) && !empty($params)) {
            $this->query_params[$name] = $params;
        }

        return $this;
    }
}
