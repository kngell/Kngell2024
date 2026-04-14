<?php

declare(strict_types=1);

class SqlCteSelectQuery extends SqlQuery implements SqlCteSelectQueryBuilderInterface
{
    private const SqlStatement TYPE = SqlStatement::SELECT;

    private array $cteMap = [];
    private ?bool $isRecursive = false;
    private SqlSelectQuery|Closure $mainQuery;
    private ?string $cycleCulumn = null;
    private ?string $cteTableName = null;

    public function __construct(
        EntityManagerInterface $em,
    ) {
        $this->method = self::TYPE->value;
        parent::__construct(null, self::TYPE, $em);
        $this->initializeWithDependencies($em->getTableAliasHelper(), $this->getState());
    }

    public function build(): string
    {
        $this->buildCte();
        return parent::build();
    }

    public function with(string $cteTableName): self
    {
        return $this->withDataCollector($cteTableName, __FUNCTION__);
    }

    public function withRecursive(string $cteTableName): self
    {
        $this->isRecursive = true;
        return $this->withDataCollector($cteTableName, __FUNCTION__);
    }

    public function body(SqlSelectQueryBuilderInterface|Closure $cteBody): self
    {
        $cteBody->setParent($this);
        $this->cteMap[] = [
            'cteTable' => $this->table,
            'cteBody' => $cteBody,
            'method' => __FUNCTION__,
            'isRecursive' => $this->isRecursive,
        ];
        $this->queryFlow[__FUNCTION__] = true;
        return $this;
    }

    public function mainQuery(SqlSelectQueryBuilderInterface|Closure $mainQuery): self
    {
        $mainQuery->setParent($this);
        $this->mainQuery = $mainQuery;
        $this->queryFlow[__FUNCTION__] = true;
        return $this;
    }

    public function cycle(?string $cycleColumn = null): self
    {
        $this->cycleCulumn = $cycleColumn;
        $this->queryFlow[__FUNCTION__] = true;
        return $this;
    }

    private function withDataCollector(string $cteTableName, string $method): self
    {
        if (!empty($this->cteMap)) {
            throw new LogicException("The WITH block has already been initiated. Use the 'add()' method to chain subsequent CTEs.");
        }
        [$uniqueTableName, $key] = $this->getUniqueTableName(__FUNCTION__, $cteTableName, $this->queryMap);

        $this->table = $uniqueTableName;
        $this->queryMap[] = $uniqueTableName;
        $this->queryFlow['with'] = true;
        $this->method = $method;
        $this->state->statementContext = StatementType::CTE;
        return $this;
    }

    private function buildCte(): void
    {
        $cteMap = $this->cteMap;
        if (empty($cteMap)) {
            return;
        }

        $isRecursive = $this->isRecursive;
        $withClause = new WithClause($isRecursive);

        foreach ($cteMap as $cteData) {
            $cteBody = $cteData['cteBody'];
            if ($cteBody instanceof Closure) {
                $cteBodyBuilder = new SqlSelectQuery($this->em);
                $cteBody($cteBodyBuilder);
                $cteBody = $cteBodyBuilder;
            }

            $cte = new cteQuery($cteData['cteTable'], $cteBody);
            $withClause->add($cte);
        }

        $this->add($withClause);
        if ($this->cycleCulumn !== null) {
            $cycleClause = new CycleClause(
                $this->em,
                $this->cycleCulumn,
            );
            $this->add($cycleClause);
        }
        $this->add($this->mainQuery);
    }
}