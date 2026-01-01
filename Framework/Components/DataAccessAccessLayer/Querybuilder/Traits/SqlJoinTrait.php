<?php

declare(strict_types=1);

trait SqlJoinTrait
{
    public function join(
        string|Closure $table,
        null|string|array $params = null,
    ): self {
        return $this->addJoin('join', $table, $params);
    }

    public function leftJoin(string|Closure $table, null|string|array $params = null): self
    {
        return $this->addJoin('leftJoin', $table, $params);
    }

    public function rightJoin(string|Closure $table, null|string|array $params = null): self
    {
        return $this->addJoin('rightJoin', $table, $params);
    }

    public function innerJoin(string|Closure $table, null|string|array $params = null): self
    {
        return $this->addJoin('innerJoin', $table, $params);
    }

    public function on(mixed ...$onConditions): self
    {
        $this->onConditions[$this->currentTable] = [
            'onConditions' => $onConditions,
            'joinContext' => $this->currentTable,
        ];
        $this->queryFlow['on'] = true;
        $this->method = __FUNCTION__;
        return $this;
    }

    public function onEqualTo(string $leftColumn, string $rightColumn): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function onNotEqualTo(string $leftColumn, string $rightColumn): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    private function addJoin(string $type, string|Closure $table, null|string|array $params = null): self
    {
        if (empty($table)) {
            throw new QueryFlowException('The joined table cannot be null');
        }
        [$uniqueTableName, $key] = $this->getUniqueTableName($type, $table, $this->queryMap);

        $this->currentTable = $uniqueTableName;

        $this->joinMap[$key] = [
            'table' => $uniqueTableName,
            'columns' => is_array($params) ? $params : [],
            'withAlias' => $this->withAlias,
            'customAlias' => is_string($params) ? $params : null,
            'method' => $type,
        ];
        $this->queryMap[$this->currentTable] = $type;
        $this->queryFlow[$type] = true;
        return $this;
    }
}