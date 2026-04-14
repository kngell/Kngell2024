<?php

declare(strict_types=1);

class BulkUpdateStatement extends AbstractStatement
{
    private const StatementType TYPE = StatementType::BULK_UPDATE;

    private null|string $joinTable = null;

    public function __construct(
        array $updateMap = [],
        private array $joinMap = [],
        private array $onMap = [],
        array $queryFlow = [],
        ?EntityManagerInterface $em = null,
        private null|BulkUpdateType $bulkType = null,
    ) {
        parent::__construct(self::TYPE, $queryFlow, $em);
        $this->map = $updateMap;
        $this->table = $updateMap['table'] ?? null;
        $this->method = $updateMap['method'];
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
        $this->state->tables[$table] = $table;
        $this->state->isUpdate = true;
        $this->state->statementContext = self::TYPE;

        $parts[] = parent::build();

        $this->query = implode(' ', $parts);
        return $this->query;
    }

    protected function buildSpecificClause(string $clause): void
    {
        match($clause) {
            'from' => $this->buildFromGroup(),
            'set' => $this->buildSetClause(),
            'where' => $this->buildWhereClauseFromMap(),
            default => null
        };
    }

    private function buildFromGroup(): void
    {
        $fromGroup = new FromGroup(StatementType::BULK_UPDATE);
        $data = fn () => $this->map['bulkData'] ?? [];
        $from = new FromClause(
            table: $this->table,
            data: $data,
            em: $this->em,
            method: $this->method,
            type: BulkUpdateType::SELECT_UNION_ALL,
        );
        $from->setMethod(SqlClause::FROM->value);
        $fromGroup->add($from);

        foreach ($this->joinMap as $joinKey => $joinConfig) {
            $joinClause = $this->createJoinClause($joinKey, $joinConfig);
            $fromGroup->add($joinClause);
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
            $joinConfig['query'] ?? $joinConfig['closure'] ?? null,
            $this->em,
            $this->method,
        );
        $join->setMethod($joinType->name)->setJoinContext($tableName);
        $this->joinTable = $tableName;

        if (isset($this->onMap[$tableName])) {
            $onClause = $this->createOnClause($tableName);
            $join->add($onClause);
        }

        return $join;
    }

    private function createOnClause(string $tableName): ConditionClause
    {
        $onData = $this->onMap[$tableName];
        $onClause = new ConditionClause(
            $onData['onConditions'],
            'on',
            $this->em,
        );
        $onClause->setJoinContext($onData['joinContext']);
        return $onClause;
    }

    private function buildSetClause(): void
    {
        $setClause = new SetClause(
            $this->map['set']->getData(),
            false,
            true,
            'set',
            $this->em,
            $this->joinTable,
        );
        $this->add($setClause);
    }
}