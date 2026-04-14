<?php

declare(strict_types=1);

class DeleteStatement extends AbstractStatement
{
    private const StatementType TYPE = StatementType::SIMPLE_DELETE;

    public function __construct(
        array $deleteMap = [],
        array $queryFlow = [],
        ?EntityManagerInterface $em = null,
    ) {
        parent::__construct(self::TYPE, $queryFlow, $em);
        $this->map = $deleteMap;
        $this->table = $deleteMap['from']->getData()['table'] ?? null;
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
        if ($this->isMariadbDialect()) {
            $parts = [$alias];
        }

        $this->state->tables[$table] = $table;

        $restOfQuery = parent::build();
        if (!empty($restOfQuery)) {
            $parts[] = $restOfQuery;
        }

        $this->query = implode(' ', $parts);
        return $this->query;
    }

    public function isMariadbDialect(): bool
    {
        return DatabaseConfig::isMariadbDialect();
    }

    protected function buildSpecificClause(string $clause): void
    {
        match($clause) {
            'from' => $this->buildFromGroup(),
            'where' => $this->buildWhereClauseFromMap(),
            default => null
        };
    }

    private function buildFromGroup(): void
    {
        $fromGroup = new FromGroup();
        $from = new FromClause(
            $this->table,
            [],
            $this->em,
            $this->method,
        );
        $from->setMethod(SqlClause::FROM->value);
        $fromGroup->add($from);

        if (isset($this->map['join'])) {
            foreach ($this->map['join'] as $joinKey => $joinConfig) {
                $joinClause = $this->createJoinClause($joinKey, $joinConfig);
                $fromGroup->add($joinClause);
            }
        }

        $this->add($fromGroup);
    }

    private function createJoinClause(string $joinKey, array $joinConfig): JoinClause
    {
        $joinType = JoinMethod::getJoinTypeFromMethod($joinConfig['method']);

        if ($joinType === null) {
            throw new QueryFlowException("Invalid join type in key: {$joinKey}");
        }

        $tableName = is_string($joinConfig['table']) ? $joinConfig['table'] : $joinKey;

        $join = new JoinClause(
            $joinConfig['customAlias'],
            $joinConfig['table'],
            $joinConfig['withAlias'],
            null,
            $this->em,
            $joinConfig['method'] ?? null,
        );
        $join->setMethod($joinType->name)->setJoinContext($tableName);

        if (isset($this->map['join'][$tableName])) {
            $onClause = $this->createOnClause($tableName);
            $join->add($onClause);
        }

        return $join;
    }

    private function createOnClause(string $tableName): ConditionClause
    {
        $onData = $this->map['join'][$tableName];
        $onClause = new ConditionClause(
            $onData['onConditions'],
            'on',
            $this->em,
        );
        $onClause->setJoinContext($onData['joinContext']);
        return $onClause;
    }
}
