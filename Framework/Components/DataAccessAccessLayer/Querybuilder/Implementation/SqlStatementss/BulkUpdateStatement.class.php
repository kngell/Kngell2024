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
        $builder = new FromGroupBuilder(
            em: $this->em,
            context: self::TYPE,
            map: $this->map,
            table: $this->table,
            method: $this->method,
            bulkType: $this->bulkType,
            data: fn () => $this->map['bulkData'] ?? null,
            joinMap: $this->joinMap,
            onMap: $this->onMap,
        );
        $fromGroup = $builder->getFromGroup();
        $this->add($fromGroup);
        $this->joinTable = $builder->getJoinTable();

        // $fromGroup = new FromGroup(self::TYPE);
        // $data = fn () => $this->map['bulkData'] ?? [];
        // $from = new FromClause(
        //     table: $this->table,
        //     data: $data,
        //     em: $this->em,
        //     method: $this->method,
        //     type: $this->bulkType,
        // );
        // $from->setMethod(SqlClause::FROM->value);
        // $fromGroup->add($from);

        // foreach ($this->joinMap as $joinKey => $joinConfig) {
        //     $joinClause = $this->createJoinClause($joinKey, $joinConfig);
        //     $fromGroup->add($joinClause);
        // }

        // $this->add($fromGroup);
    }

    private function createJoinClause(string $joinKey, array $joinConfig): JoinClause
    {
        $joinType = JoinMethod::getJoinTypeFromMethod($joinConfig['method']);

        if ($joinType === null) {
            throw new QueryFlowException("Invalid join type in key: {$joinKey}");
        }

        $tableName = is_string($joinConfig['table']) ? $joinConfig['table'] : $joinKey;

        $join = new JoinClause(
            customAlias: $joinConfig['customAlias'],
            table: $joinConfig['table'],
            withAlias: $joinConfig['withAlias'],
            selectQuery: $joinConfig['query'] ?? $joinConfig['closure'] ?? null,
            em: $this->em,
            method: $this->method,
            type: $this->bulkType,
        );
        $join->setMethod($joinType->name)->setJoinContext($tableName);
        $join->setContext(self::TYPE);
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
            $onData,
            'on',
            $this->em,
        );
        $onClause->setJoinContext($onData['joinContext']);
        return $onClause;
    }

    private function buildSetClause(): void
    {
        $setClause = new SetClause(
            setData: $this->map['set']->getData(),
            hasMultiple: false,
            hasSingle: true,
            method: 'set',
            em: $this->em,
            sourceTable: $this->joinTable,
        );
        $setClause->setBulkUpdateType($this->bulkType)
        ->setContext(self::TYPE);
        $this->add($setClause);
    }
}