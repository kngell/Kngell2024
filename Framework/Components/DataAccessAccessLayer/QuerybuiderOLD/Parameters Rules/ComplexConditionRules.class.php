<?php

declare(strict_types=1);

class ComplexConditionRules extends AbstractConditionRules
{
    private const string PARAM_SUFIX = 'complex_azerty';

    public function __construct(
        EntityManagerInterface $em,
        ?QueryBuilder $builder,
        array $bind_arr,
        array $tableAlias,
        array $aliasCheck,
        array $parameters,
        array $tables,
        string $method,
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

        foreach ($conditions as $index => $condition) {
            $isFirst = $index === 0;

            if ($this->isLogicalOperator($condition)) {
                continue;
            }
            if ($this->isConditionGroup($condition)) {
                $strCondition .= (!$isFirst ? ' AND ' : '') . $this->buildConditionGroup($condition);
            } elseif (is_string($condition)) {
                $strCondition .= (!$isFirst ? ' AND ' : '') . $condition;
            } elseif (is_array($condition) && $this->isRawCondition($condition)) {
                $strCondition .= (!$isFirst ? ' AND ' : '') . $condition[0];
            } elseif (is_array($condition) && $this->isTripleCondition($condition)) {
                $strCondition .= (!$isFirst ? ' AND ' : '') . $this->buildTripleCondition($condition);
            } elseif (is_array($condition) && ArrayUtils::isAssoc($condition)) {
                $strCondition .= (!$isFirst ? ' AND ' : '') . $this->buildAssocCondition($condition);
            }
        }

        return $strCondition;
    }

    protected function normalize(array $conditions): array
    {
        if (empty($conditions)) {
            return [];
        }

        $normalized = [];
        $currentGroup = [];

        foreach ($conditions as $key => $condition) {
            if ($this->isLogicalOperator($condition)) {
                if (!empty($currentGroup)) {
                    $normalized[] = $currentGroup;
                    $currentGroup = [];
                }
                $currentGroup['operator'] = strtoupper($condition);
            } elseif ($this->shouldStartNewGroup($condition, $currentGroup)) {
                if (!empty($currentGroup)) {
                    $normalized[] = $currentGroup;
                }
                $currentGroup = ['conditions' => [$condition]];
            } else {
                if (!isset($currentGroup['conditions'])) {
                    $currentGroup['conditions'] = [];
                }
                $currentGroup['conditions'][] = $condition;
            }
        }

        if (!empty($currentGroup)) {
            $normalized[] = $currentGroup;
        }

        return $normalized;
    }

    private function isLogicalOperator(mixed $condition): bool
    {
        return is_string($condition) && in_array(strtoupper($condition), ['AND', 'OR']);
    }

    private function isConditionGroup(mixed $condition): bool
    {
        return is_array($condition) &&
               (isset($condition['operator']) || isset($condition['conditions']));
    }

    private function shouldStartNewGroup(mixed $condition, array $currentGroup): bool
    {
        return isset($currentGroup['operator']) && empty($currentGroup['conditions']);
    }

    private function buildConditionGroup(array $group): string
    {
        $operator = $group['operator'] ?? 'AND';
        $conditions = $group['conditions'] ?? $group;

        if (empty($conditions)) {
            return '';
        }

        $groupBuilder = new self(
            $this->em,
            $this->builder,
            $this->bind_arr,
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->tables,
            $this->method,
        );

        $groupConditions = [];
        foreach ($conditions as $condition) {
            if ($this->isLogicalOperator($condition)) {
                continue;
            }
            $groupConditions[] = $condition;
        }

        $groupRule = $groupBuilder->buildGroupConditions($groupConditions, $operator);

        $this->parameters = array_merge($this->parameters, $groupBuilder->getParameters());
        $this->bind_arr = array_merge($this->bind_arr, $groupBuilder->getBindArr());

        return '(' . $groupRule . ')';
    }

    private function buildGroupConditions(array $conditions, string $operator): string
    {
        $parts = [];

        foreach ($conditions as $condition) {
            if (is_string($condition)) {
                $parts[] = $condition;
            } elseif (is_array($condition) && $this->isRawCondition($condition)) {
                $parts[] = $condition[0];
            } elseif (is_array($condition) && $this->isTripleCondition($condition)) {
                $parts[] = $this->buildTripleCondition($condition);
            } elseif (is_array($condition) && ArrayUtils::isAssoc($condition)) {
                $parts[] = $this->buildAssocCondition($condition);
            }
        }

        return implode(" $operator ", $parts);
    }

    private function isRawCondition(array $condition): bool
    {
        return count($condition) === 1 && is_string($condition[0]) &&
               (str_contains($condition[0], ' ') || str_contains($condition[0], '('));
    }

    private function isTripleCondition(array $condition): bool
    {
        return count($condition) === 3 &&
               is_string($condition[0]) &&
               is_string($condition[1]) &&
               !is_array($condition[2]);
    }

    private function buildTripleCondition(array $condition): string
    {
        list($column, $operator, $value) = $condition;
        $tblh = $this->em->getTableAliasHelper()->setTables($this->tables);

        list($table, $columnName) = $tblh->mapTableColumn($column);
        list($table, $alias) = $tblh->get($table, $this->tableAlias, $this->aliasCheck);
        $alias = !empty($alias) ? $alias . '.' : '';

        $operator = strtoupper(trim($operator));

        if ($value === null) {
            if ($operator === '=') {
                $operator = 'IS';
            }
            if ($operator === '!=') {
                $operator = 'IS NOT';
            }
            return "{$alias}{$columnName} {$operator} NULL";
        }
        if ($value === '?') {
            return "{$alias}{$columnName} {$operator} ?";
        }
        if (is_bool($value)) {
            $boolValue = $value ? 1 : 0;
            $paramName = $this->generateParamName($columnName);
            $this->parameters[$paramName] = $boolValue;
            return "{$alias}{$columnName} {$operator} :{$paramName}";
        } else {
            $paramName = $this->generateParamName($columnName);
            $this->parameters[$paramName] = $value;
            return "{$alias}{$columnName} {$operator} :{$paramName}";
        }
    }

    private function buildAssocCondition(array $condition): string
    {
        $parts = [];
        foreach ($condition as $key => $value) {
            if (is_int($key)) {
                $parts[] = $value;
            } else {
                $parts[] = $this->buildTripleCondition([$key, '=', $value]);
            }
        }
        return implode(' AND ', $parts);
    }

    private function generateParamName(string $column): string
    {
        $tblh = $this->em->getTableAliasHelper();
        $stmt = $tblh->getToken()->generate(3, self::PARAM_SUFIX);

        $baseName = $stmt . '_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $column);
        $paramName = $baseName;
        $counter = 1;

        while (isset($this->parameters[$paramName])) {
            $paramName = $baseName . '_' . $counter;
            $counter++;
        }

        return $paramName;
    }
}