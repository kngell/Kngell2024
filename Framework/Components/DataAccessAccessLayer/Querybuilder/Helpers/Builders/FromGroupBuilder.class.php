<?php

declare(strict_types=1);

final class FromGroupBuilder
{
    private ?string $joinTable = null;

    public function __construct(
        private EntityManagerInterface $em,
        private ?STatementType $context = null,
        private array $map = [],
        private array $joinMap = [],
        private array $onMap = [],
        private mixed $data = [],
        private ?string $table = null,
        private ?string $method = null,
        private ?string $customAlias = null,
        private null|BulkUpdateType $bulkType = null,
    ) {
    }

    public function getFromGroup(): FromGroup
    {
        $fromGroup = new FromGroup($this->context);

        // 1. Add main FROM clause
        $from = new FromClause(
            table: $this->table,
            data: $this->data,
            em: $this->em,
            method: $this->method,
            customAlias: $this->customAlias,
            type: $this->bulkType,
        );
        $from->setMethod(SqlClause::FROM->value);
        $from->setContext($this->context);
        $fromGroup->add($from);

        // 2. Add all JOIN clauses
        foreach ($this->joinMap ?? [] as $joinKey => $joinConfig) {
            $joinClause = $this->createJoinClause($joinKey, $joinConfig);
            $fromGroup->add($joinClause);
        }

        return $fromGroup;
    }

    /**
     * @return null|string
     */
    public function getJoinTable(): ?string
    {
        return $this->joinTable;
    }

    private function createJoinClause(string $joinKey, array $joinConfig): JoinClause
    {
        $joinType = JoinMethod::getJoinTypeFromMethod($joinConfig['method']);

        if ($joinType === null) {
            throw new QueryFlowException("Invalid join type in key: {$joinKey}");
        }

        $join = new JoinClause(
            customAlias: $joinConfig['customAlias'] ?? null,
            table: $joinConfig['table'],
            withAlias: $joinConfig['withAlias'] ?? null,
            selectQuery: $joinConfig['query'] ?? $joinConfig['closure'] ?? null,
            em: $this->em,
            method: $joinConfig['method'] ?? null,
            type: $this->bulkType,
        );
        $this->joinTable = $this->extractTableName($joinKey, $joinConfig);
        $join->setMethod($joinType->name)
             ->setJoinContext($this->joinTable)
             ->setContext($this->context);

        // Add ON condition if exists
        $tableName = $this->extractTableName($joinKey, $joinConfig);
        if ($this->hasOnConditionsForTable($tableName)) {
            $onDataset = $this->map['on'][$tableName] ?? $this->onMap[$tableName];

            $helper = new ConditionBuilderHelper(
                $this->em,
                $onDataset,
            );
            $groupedElements = $helper->getBuilder()->getGroupedElements();
            foreach ($groupedElements->all() as $element) {
                $join->add($element);
            }
        }
        return $join;
    }

    private function extractTableName(string $joinKey, array $joinConfig): string
    {
        return is_string($joinConfig['table'])
            ? $joinConfig['table']
            : $joinKey;
    }

    private function hasOnConditionsForTable(string $tableName): bool
    {
        return isset($this->map['on'][$tableName]) || !empty($this->onMap);
    }
}