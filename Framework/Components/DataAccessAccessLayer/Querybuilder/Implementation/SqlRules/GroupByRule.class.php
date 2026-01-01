<?php

declare(strict_types=1);

class GroupByRule extends AbstractRules implements QueryRulesInterface
{
    public function __construct(
        private array $groupByMap,
        EntityManagerInterface $em,
        ?string $method,
        QueryState $state,
    ) {
        parent::__construct($em, $method, $state);
    }

    public function getRule(array $groupByMap): string
    {
        $normalized = $this->normalize($groupByMap);
        if (empty($normalized)) {
            return '';
        }
        $parts = [];
        $tableHelper = $this->em->getTableAliasHelper();
        $tableAlias = $this->state->tableAlias;
        $aliasCheck = $this->state->aliasCheck;
        foreach ($normalized as $key => $field) {
            list($table, $column) = $tableHelper->mapTableColumn($field);
            list($table, $alias) = $tableHelper->get($table, $tableAlias, $aliasCheck);

            $parts[] = !empty($alias) ? $alias . '.' . $column : $column;
        }
        return implode(', ', $parts);
    }

    protected function normalize(array $arrayInput): array
    {
        // Remove method key if present
        if (isset($arrayInput['method'])) {
            unset($arrayInput['method']);
        }

        // Extract limit value
        if (isset($arrayInput['groupBy'])) {
            return $arrayInput['groupBy'];
        }
        return [];
    }
}