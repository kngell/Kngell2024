<?php

declare(strict_types=1);

abstract class AbstractStatement extends SqlQuery implements SqlStatementInterface
{
    protected array $map;
    private StatementType $statementType;
    private array $builtClauses = [];

    public function __construct(StatementType $type, array $queryFlow = [], ?EntityManagerInterface $em = null)
    {
        parent::__construct(null, $type->toSqlStatement(), $em);
        $this->queryFlow = $queryFlow;
        $this->statementType = $type;
    }

    abstract protected function buildSpecificClause(string $clause): void;

    protected function initialize(): void
    {
        $this->statementType->validate($this->queryFlow, $this->map ?? []);

        $buildOrder = $this->getStatement()->getBuildOrder();
        if (empty($buildOrder)) {
            $buildOrder = $this->statementType->getBuildOrder();
        }

        if (empty($buildOrder)) {
            throw new QueryBuildException('No build order defined for ' . get_class($this));
        }
        foreach ($buildOrder as $clause) {
            if (!$clause instanceof SqlMethodCategory) {
                throw new QueryBuildException("Invalid clause type {$clause}");
            }
            if ($clause->isInitial()) {
                continue;
            }
            $methods = $clause->getMethods();
            $clauseKey = ($clause instanceof SqlMethodCategory) ? $clause->value : $clause;

            if ($this->shouldBuildClause($clauseKey, $methods)) {
                $this->buildSpecificClause($clauseKey);
            }
        }
    }

    protected function shouldBuildClause(string $clause, array $methods): bool
    {
        if (in_array($clause, $this->builtClauses, true)) {
            return false;
        }

        $this->builtClauses[] = $clause;

        return (bool) array_intersect(
            $this->queryFlow,
            $methods,
        );
    }

    protected function buildWhereClauseFromMap(array $where = []): void
    {
        $where = $this->getWhereConditions($where);
        if (empty($where)) {
            return;
        }

        $helper = new ConditionBuilderHelper(
            $this->em,
            $where,
        );
        $groupedElements = $helper->getBuilder()->getGroupedElements();
        if ($groupedElements->isEmpty()) {
            return;
        }
        foreach ($groupedElements->all() as $element) {
            $this->add($element);
        }
    }

    private function getWhereConditions(array $where): array
    {
        if (!empty($where)) {
            return $where;
        }
        if (empty($this->map['where'])) {
            return [];
        }
        return $this->map['where'];
    }
}