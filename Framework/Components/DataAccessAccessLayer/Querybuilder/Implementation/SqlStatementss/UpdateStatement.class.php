<?php

declare(strict_types=1);

class UpdateStatement extends SqlQuery implements SqlStatementInterface
{
    private const SqlStatementType TYPE = SqlStatementType::UPDATE;

    private array $updateMap;

    public function __construct(
        array $updateMap = [],
        array $queryFlow = [],
        ?EntityManagerInterface $em = null,
    ) {
        parent::__construct(null, self::TYPE, $em);
        $this->updateMap = $updateMap;
        $this->queryFlow = $queryFlow;
        $this->table = $updateMap['table'] ?? null;
    }

    public function build(): string
    {
        if ($this->table === null) {
            throw new QueryBuildException('UPDATE statement requires a table');
        }

        $parts = ['UPDATE', $this->table];

        foreach (self::TYPE->getBuildOrder() as $clause) {
            if ($this->shouldBuildClause($clause)) {
                $this->buildClause($clause);
            }
        }
        $parts[] = parent::build();
        $this->query = implode(' ', $parts);
        return $this->query;
    }

    public function getsqlStatementType(): SqlStatementType
    {
        return self::TYPE;
    }

    protected function shouldBuildClause(string $clause): bool
    {
        $userFlow = array_keys($this->queryFlow);
        return in_array($clause, $userFlow);
    }

    private function buildClause(string $clause): void
    {
        match($clause) {
            'set' => $this->buildSetClause(),
            'where' => $this->buildWhereClause(),
            default => null
        };
    }

    private function buildSetClause(): void
    {
        $setClause = new SetClause(
            $this->updateMap['set'],
            false,
            true,
            'set',
            $this->em,
        );
        $this->add($setClause);
    }

    private function buildWhereClause(): void
    {
        $conditionsMap = $this->updateMap;
        if (!isset($conditionsMap['where']) || empty($conditionsMap['where'])) {
            return;
        }

        $builder = new ConditionGroupBuilder($this->em);

        foreach ($conditionsMap['where'] as $index => $conditionData) {
            $builder->addCondition(
                $conditionData->getMethod(),
                $conditionData->getUpdateData(),
            );
        }

        $groupedElements = $builder->getGroupedElements();

        if ($groupedElements->isEmpty()) {
            return;
        }

        foreach ($groupedElements->all() as $element) {
            $this->add($element);
        }
    }
}