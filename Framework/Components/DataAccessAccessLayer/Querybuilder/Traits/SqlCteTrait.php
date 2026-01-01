<?php

declare(strict_types=1);

trait SqlCteTrait
{
    public function with(string $cteTableName, SqlSelectQuery|Closure $cteBody): self
    {
        if (!empty($this->cteMap)) {
            throw new LogicException("The WITH block has already been initiated. Use the 'add()' method to chain subsequent CTEs.");
        }
        [$uniqueTableName, $key] = $this->getUniqueTableName(__FUNCTION__, $cteTableName, $cteBody->getQueryMap());
        $this->queryMap[$key] = __FUNCTION__;
        $this->cteMap[] = [
            'cteTable' => $uniqueTableName,
            'cteBody' => $cteBody,
            'method' => __FUNCTION__,
            'isRecursive' => false,
        ];
        $this->isRecursive = false;
        $this->queryFlow['with'] = true;
        $this->method = __FUNCTION__;
        return $this;
    }

    public function withRecursive(string $cteTableName, SqlSelectQuery|Closure $cteBody): self
    {
        if (!empty($this->cteMap)) {
            throw new LogicException("The WITH block has already been initiated. Use the 'add()' method to chain subsequent CTEs.");
        }
        [$uniqueTableName, $key] = $this->getUniqueTableName(__FUNCTION__, $cteTableName, $cteBody->getQueryMap());

        $this->cteMap[] = [
            'cteTable' => $uniqueTableName,
            'cteBody' => $cteBody,
            'method' => __FUNCTION__,
            'isRecursive' => true,
        ];

        $this->table = $uniqueTableName;

        $this->queryMap[$key] = __FUNCTION__;
        $this->queryFlow['with'] = true;
        $this->isRecursive = true;
        $this->method = __FUNCTION__;
        return $this;
    }

    // public function addCte(string $cteTableName, SqlSelectQueryBuilderInterface|Closure $cteBody): self
    // {
    //     if ($this->isRecursive === null) {
    //         throw new LogicException("Cannot use 'add()' before initializing the WITH block with 'with()' or 'withRecursive()'.");
    //     }

    //     $this->cteMap[] = [
    //         'cteTable' => $cteTableName,
    //         'cteBody' => $cteBody,
    //         'method' => __FUNCTION__,
    //         'isRecursive' => $this->isRecursive,
    //     ];
    //     return $this;
    // }

    private function addCte(string $type, string $cteTableName, SqlSelectQuery|Closure $cteBody): self
    {
        [$uniqueCteName, $key] = $this->getUniqueTableName($type, $cteTableName, $cteBody->getQueryMap());

        $this->selectMap[$key] = [
            'table' => $uniqueCteName,
            'physicalName' => $cteTableName,
            'body' => $cteBody,
            'method' => $type,
        ];

        $this->queryFlow[$type] = true;
        return $this;
    }
}