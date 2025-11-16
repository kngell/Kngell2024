<?php

// OnConditionRule.php

declare(strict_types=1);

// You can extend WhereConditionRule and only override the parts that differ.
final class OnConditionRule extends WhereConditionRule
{
    private ?string $currentJoinLogicalKey;

    public function __construct(
        EntityManagerInterface $em,
        QueryBuilder $builder,
        array $bind_arr,
        array $tableAlias,
        array $aliasCheck,
        array $parameters,
        array $tables,
        string $method,
        TypeNormalizerInterface $normalizer,
        // *** NEW CONSTRUCTOR PARAMETER ***
        ?string $currentJoinLogicalKey,
    ) {
        parent::__construct(
            $em,
            $builder,
            $bind_arr,
            $tableAlias,
            $aliasCheck,
            $parameters,
            $tables,
            $method,
            $normalizer,
        );
        $this->currentJoinLogicalKey = $currentJoinLogicalKey;
    }

    /**
     * Overrides the parent method to apply special alias resolution for the RHS of ON clauses.
     */
    private function buildColumnReference(string $rightCondition, TablesAliasHelper $tableHelper): string
    {
        list($table, $column) = $tableHelper->mapTableColumn($rightCondition);

        // 1. Resolve the PHYSICAL table name from the current unique logical key
        //    (e.g., 'stock_status' from 'stock_status_join_1').
        $physicalTable = $tableHelper->getPhysicalTableFromLogicalKey($this->currentJoinLogicalKey); // Requires new helper method!

        // 2. Check if the table name used in the ON condition (RHS) matches the physical table name
        //    of the join we are currently defining.
        if ($table === $physicalTable) {
            // If they match, we MUST use the alias associated with the unique logical key.

            // We need a helper method to get the alias directly from the logical key.
            list($physicalTable, $alias) = $tableHelper->getAliasFromLogicalKey(
                $this->currentJoinLogicalKey,
                $this->tableAlias,
            );

            // This is the fix: It ensures 's4' is used, not 's'.
            return !empty($alias) ? $alias . '.' . $column : $column;
        }

        // 3. Otherwise, fall back to the generic resolution (e.g., p3.stock_status_id is fine)
        list($table, $alias) = $tableHelper->get($table, $this->tableAlias, $this->aliasCheck);

        return !empty($alias) ? $alias . '.' . $column : $column;
    }
}