<?php

declare(strict_types=1);

class CaseExpressionCollector extends SqlQuery implements CaseExpressionBuilderInterface
{
    use SqlWhereConditionTrait;

    private array $caseMap = [];
    private int $caseCount = 0;
    private bool $hasCase = false;
    private bool $hasEnd = false;
    private bool $hasElse = false;

    public function __construct(
        ?EntityManagerInterface $em = null,
        ?SqlStatement $statement = null,
    ) {
        parent::__construct(null, $statement, $em);
        $this->initializeComponents();
        $this->initializeWithDependencies(
            $em?->getTableAliasHelper(),
            $this->getState(),
            $this->customAlias,
            $this->queryMap,
        );
    }

    public function build(): string
    {
        $this->buildCaseBlock();
        return parent::build();
    }

    public function case(mixed $expression): static
    {
        if ($this->hasCase) {
            throw new QueryBuildException('CASE can only be called once');
        }
        $this->caseMap[__FUNCTION__] = $expression;
        $this->queryFlow[] = [__FUNCTION__];
        $this->hasCase = true;

        return $this;
    }

    public function when(mixed ...$conditions): static
    {
        if ($this->hasEnd) {
            throw new QueryBuildException('Cannot add WHEN after END');
        }

        if ($this->hasElse) {
            throw new QueryBuildException('Cannot add WHEN after ELSE');
        }

        $this->queryFlow[] = [__FUNCTION__];
        return $this->where(...$conditions);
    }

    public function then(mixed ...$result): static
    {
        $whenConditions = $this->getWhereConditions();
        if (empty($whenConditions)) {
            throw new QueryBuildException('THEN must follow a WHEN clause');
        }

        $this->caseMap['when_then'][] = [
            'when' => $whenConditions,
            'then' => $result,
        ];
        $this->conditionsMap = [];
        $this->queryFlow[] = __FUNCTION__;
        return $this;
    }

    public function else(mixed ...$sqlExpression): static
    {
        if ($this->hasElse) {
            throw new QueryBuildException('ELSE can only be called once');
        }

        if (empty($this->caseMap['when_then'])) {
            throw new QueryBuildException('ELSE must be after at least one WHEN-THEN');
        }

        $this->caseMap[__FUNCTION__] = $sqlExpression;
        $this->queryFlow[] = __FUNCTION__;
        $this->hasElse = true;
        return $this;
    }

    public function end(?string $as = null): static
    {
        if ($this->hasEnd) {
            throw new QueryBuildException('END can only be called once');
        }

        $this->caseMap[__FUNCTION__] = $as;
        $this->queryFlow[] = __FUNCTION__;
        $this->hasEnd = true;
        return $this;
    }

    private function buildCaseBlock(): void
    {
        $caseblock = new CaseExpressionBlock(
            $this->caseMap,
            $this->queryFlow,
            $this->sqlStatement,
            $this->em,
        );
        $this->add($caseblock);
    }
}