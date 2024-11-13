<?php

declare(strict_types=1);

abstract class AbstractQueryBuilder
{
    /** @var array */
    protected const SQL_DEFAULT = [
        'conditions' => [],
        'selectors' => [],
        'replace' => false,
        'distinct' => false,
        'from' => [],
        'where' => null,
        'and' => [],
        'or' => [],
        'orderby' => [],
        'fields' => [],
        'primary_key' => '',
        'table' => '',
        'type' => '',
        'raw' => '',
        'table_join' => '',
        'join_key' => '',
        'join' => [],
        'params' => [],
        'custom' => '',
    ];

    /** @var array */
    protected const QUERY_TYPES = ['insert', 'select', 'update', 'delete', 'custom', 'search', 'join', 'show', 'delete'];
    /** @var array */
    protected array $key;

    /** @var string */
    protected string $sql = '';

    public function baseQuery() : array
    {
        list($sql, $query) = match (true) {
            array_key_exists('join_rules', $this->key['extras']) => $this->recursiveQuery($this->join($this->key['selectors'], $this->key['extras'])),
            default => $this->recursiveQuery($this->mainQuery()),
        };
        $sql .= $this->where();
        $sql .= $this->groupBy();
        $sql .= $this->orderBy();
        $sql .= $this->queryOffset();

        return [$sql, $query];
    }

    protected function isValidQueryType(string $type) : bool
    {
        if (in_array($type, self::QUERY_TYPES)) {
            return true;
        }

        return false;
    }

    protected function mainQuery() : string
    {
        $sql = '';
        if (!array_key_exists('sql', $this->key['extras'])) {
            if (strpos($this->key['table'], 'SELECT') !== false) {
                $sql = $this->key['table'];
            } else {
                $selectors = $this->selectors();
                if (isset($this->key['aggregate']) && $this->key['aggregate']) {
                    $sql = "SELECT {$this->key['aggregate']}({$this->key['aggregate_field']}) FROM {$this->key['table']}";
                } else {
                    $sql = "SELECT {$selectors} FROM {$this->key['table']}";
                }
            }
        } else {
            $sql = $this->key['extras']['sql'];
        }

        return $sql;
    }

    /**
     * Join Table when selecting data
     * =====================================================================.
     * @param mixed $tables
     * @param array $data
     * @return string
     */
    protected function join($selectors, array $options = []) :string
    {
        $sql = '';
        if (array_key_exists('table', $options)) {
            if (!(count($options['join_rules']) === count($options['table']) - 1)) {
                throw new QueryBuilderInvalidArgExceptions('Cannot join tables');
            }
            $columns = (!empty($selectors)) ? implode(', ', $selectors) : '*';
            $sql .= "SELECT {$columns} FROM {$options['table'][0]}";
        }
        $all_tables = $options['table'];
        foreach ($options['join_rules'] as $index => $join_rule) {
            $withParams = array_key_exists('params', $options['join_on']) ? true : false; //[$all_tables[$index + 1]]
            $braceOpen = $withParams ? ' (' : '';
            $braceClose = $withParams ? ') ' : '';
            if (is_numeric($index)) {
                $sql .= ' ' . $join_rule . ' ' . $all_tables[$index + 1];
                // $i = count($options['join_on'][$all_tables[$index]]) - 1;
                $sql .= ' ON ' . $braceOpen . '(' . $options['join_on'][$index][0] . ' = ' . $options['join_on'][$index][1] . ')';
            }
        }
        if (array_key_exists('params', $options['join_on'])) {
            $allParams = array_filter($options['join_on'], function ($rule) {
                return $rule === 'params';
            }, ARRAY_FILTER_USE_KEY);
            foreach ($allParams as $params) {
                foreach ($params as $args) {
                    $sql .= ' ' . $args['separator'] . ' (' . $args[0] . ' ' . $args['operator'] . ' ' . $this->getValue($args[1]) . ')';
                }
            }
            $sql .= $braceClose;
        }

        return $sql;
    }

    protected function recursive(string $query, string $globalQuery) : string
    {
        $sql = 'WITH RECURSIVE cte AS (';
        $sql .= '(' . $globalQuery . ') ';
        $sql .= 'UNION ALL ';
        $sql .= $query;

        return $sql;
    }

    protected function recursiveQuery(string $query) : array
    {
        if (array_key_exists('recursive_query', $this->key) && !empty($this->key['recursive_query'])) {
            return [$query, $this->key['recursive_query']];
        }
        $q = $query;
        $options = $this->key['extras'];
        $selectors = $this->key['selectors'];
        $sql = 'SELECT ';
        if (array_key_exists('recursive', $options)) {
            $recursive = $options['recursive'];
            if (array_key_exists('COUNT', $recursive) && count($selectors) === 1) {
                $sql .= 'p.' . $recursive['field'] . ' FROM ' . $this->key['table'] . ' p ';
                $sql .= 'INNER JOIN cte ON p.' . $recursive['parentID'] . '= cte.' . $recursive['id'] . ')';
                $sql .= 'SELECT COUNT(' . $recursive['field'] . ') AS ' . $recursive['AS'] . ' FROM cte;';

                return [$q, $sql];
            }
            $aryIndex = [];
            foreach ($options['table'] as $index => $tbl) {
                $query = str_replace($tbl . '.', $tbl . $index . '.', $query);
                $query = str_replace(' ' . $tbl . ' ', ' ' . $tbl . ' ' . $tbl . $index . ' ', $query);
                $aryIndex[] = $tbl . $index;
            }
            $sql = $query . ' ' . 'INNER JOIN cte ON ';
            $sql .= $aryIndex[0] . '.' . $recursive['parentID'] . ' = cte' . '.' . $recursive['id'] . ') ';
            $sql .= 'SELECT * FROM cte;';

            return [$q, $sql];
        }

        return [$q, ''];
    }

    /**
     * Where condition
     * =====================================================================.
     *
     * @return string
     */
    protected function where() : string
    {
        $where = '';
        $whereCond = (is_array($this->key['where']) && !empty($this->key['where'])) ? array_merge($this->key['conditions'], $this->key['where']) : $this->key['conditions'];
        if (isset($whereCond) && !empty($whereCond)) {
            $where .= ' WHERE ';
            $i = 0;
            $where .= '(';
            foreach ($whereCond as $field => $aryCond) {
                if ($field != 'or' && $field != 'and') {
                    if (is_array($aryCond) && !empty($aryCond)) {
                        $sep = $i > 0 ? ' ' : '';
                        $where .= $sep . $this->whereConditions($aryCond, $field);
                        $i++;
                        unset($whereCond[$field]);
                    }
                }
            }
            $where .= ')';
            if (count($whereCond) > 0) {
                foreach ($whereCond as $separator => $aryConds) {
                    $where .= ' ' . strtoupper($separator) . ' (';
                    $i = 0;
                    foreach ($aryConds as $field => $AryCond) {
                        $where .= $this->whereConditions($AryCond, $field);
                        $i++;
                    }
                    $where .= ')';
                }
            }
        }

        return $where;
    }

    /**
     * Group By.
     *
     * @return void
     */
    protected function groupBy()
    {
        $groupBy = '';
        if (isset($this->key['extras']) && array_key_exists('group_by', $this->key['extras'])) {
            $groupBy .= ' GROUP BY ' . implode(', ', $this->key['extras']['group_by']);
        }

        return $groupBy . '';
    }

    protected function orderBy() : string
    {
        $sql = '';
        if (isset($this->key['extras']['orderby']) && $this->key['extras']['orderby'] != '') {
            $sql .= is_array($this->key['extras']['orderby']) ? ' ORDER BY ' . implode(', ', $this->key['extras']['orderby']) . ' ' : ' ORDER BY ' . $this->key['extras']['orderby'];
        }

        return $sql;
    }

    protected function queryOffset() : string
    {
        $sql = '';
        if (isset($this->key['params']['limit']) && isset($this->key['params']['offset']) && $this->key['params']['offset'] != -1) {
            $sql .= ' LIMIT :offset, :limit';
        }
        if (isset($this->key['params']['limit']) && !isset($this->key['params']['offset'])) {
            $sql .= ' LIMIT :limit';
        }

        return $sql;
    }

    protected function isQueryTypeValid(string $type) : bool
    {
        if (in_array($type, self::QUERY_TYPES)) {
            return true;
        }

        return false;
    }

    /**
     * Checks whether a key is set. returns true or false if not set.
     *
     * @param string $key
     * @return bool
     */
    protected function has(string $key): bool
    {
        return isset($this->key[$key]);
    }

    private function selectors() : string
    {
        $recursiveCount = isset($this->key['extras']['recursive']['COUNT']) ? $this->key['extras']['recursive']['field'] : false;
        if ($recursiveCount != false) {
            return $recursiveCount;
        }

        return (!empty($this->key['selectors'])) ? implode(', ', $this->key['selectors']) : '*';
    }

    private function getValue(mixed $arg) : mixed
    {
        return match (gettype($arg)) {
            'int' => intval($arg),
            'bool' => boolval($arg),
            default => "'" . $arg . "'"
        };
    }

    private function whereConditions(array $aryCond, string $field) : string
    {
        $separator = isset($aryCond['separator']) ? ' ' . $aryCond['separator'] . ' ' : '';
        $operator = isset($aryCond['operator']) ? ' ' . $aryCond['operator'] . ' ' : '';
        $braceOpen = isset($aryCond['braceOpen']) ? ' ' . $aryCond['braceOpen'] . ' ' : '';
        $braceEnd = isset($aryCond['braceEnd']) ? ' ' . $aryCond['braceEnd'] . ' ' : '';
        switch ($operator) {
            case in_array(trim($operator), ['NOT IN', 'IN']):
                $arr = [];
                $prefixer = $this->arrayPrefixer($field, $aryCond['value'], $arr);
                $this->key['where']['bind_array'] = $arr;

                return $braceOpen . $aryCond['tbl'] . '.' . $field . $operator . ' (' . $prefixer . ')' . $braceEnd . $separator;
                // return "$add" . $tbl . $key . ' ' . $value['operator'] . ' (' . $prefixer . ')';
                break;
            case trim($operator) === 'LIKE':

                break;

            default:
                return $braceOpen . $aryCond['tbl'] . '.' . $field . $operator . ":$field" . $braceEnd . $separator;
                break;
        }
    }

    /**
     * Array prefixer.
     *
     * @param string $prefix
     * @param array $values
     * @param array $bindArray
     * @return string
     */
    private function arrayPrefixer(string $prefix, array $values, array &$bindArray) : string
    {
        $str = '';
        foreach ($values as $index => $value) {
            $str .= ':' . $prefix . $index . ',';
            $bindArray[$prefix . $index] = $value;
        }

        return rtrim($str, ',');
    }
}
