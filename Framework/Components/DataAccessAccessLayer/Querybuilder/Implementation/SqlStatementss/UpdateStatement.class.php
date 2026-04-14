<?php

declare(strict_types=1);

class UpdateStatement extends AbstractStatement
{
    private const StatementType TYPE = StatementType::SIMPLE_UPDATE;

    public function __construct(
        array $updateMap = [],
        array $queryFlow = [],
        ?EntityManagerInterface $em = null,
    ) {
        parent::__construct(self::TYPE, $queryFlow, $em);
        $this->map = $updateMap;
        $this->table = $updateMap['table'] ?? null;
        $this->initialize();
    }

    public function build(): string
    {
        if ($this->table === null) {
            throw new QueryBuildException('UPDATE statement requires a table');
        }
        list($table, $alias) = $this->helper->get($this->table, $this->state->tableAlias, $this->state->aliasCheck);

        if (!empty($this->customAlias)) {
            $alias = $this->customAlias;
        }

        $parts = [$table . ' AS ' . $alias];
        $this->state->tables[$table] = $this->getColumns();
        $this->state->isUpdate = true;
        $this->state->statementContext = self::TYPE;

        $parts[] = parent::build();
        $this->query = implode(' ', $parts);
        return $this->query;
    }

    protected function buildSpecificClause(string $clause): void
    {
        match($clause) {
            'set' => $this->buildSetClause(),
            'where' => $this->buildWhereClauseFromMap(),
            default => null
        };
    }

    private function getColumns(): array
    {
        $columns = [];
        $setData = $this->map['set']->getData();
        foreach ($setData as $column => $value) {
            $columns[] = $column;
        }
        return $columns;
    }

    private function buildSetClause(): void
    {
        $setClause = new SetClause(
            $this->map['set']->getData(),
            false,
            true,
            'set',
            $this->em,
        );
        $this->add($setClause);
    }
}