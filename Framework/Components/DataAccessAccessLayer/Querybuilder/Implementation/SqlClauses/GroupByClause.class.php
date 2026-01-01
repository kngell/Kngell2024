<?php

declare(strict_types=1);
class GroupByClause extends SqlComponent implements RegularClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::GROUP_BY;

    private ?QueryRulesInterface $groupByRule;

    public function __construct(
        private EntityManagerInterface $em,
        private array $groupByConfig,
    ) {
        $this->method = $groupByConfig['method'];
    }

    public function build(): string
    {
        $this->initializeRule();
        $this->query = $this->groupByRule->getRule($this->groupByConfig);
        return $this->query;
    }

    public function getSqlClause(): SqlClause
    {
        return self::CLAUSE;
    }

    private function initializeRule(): void
    {
        if (!isset($this->groupByRule)) {
            $registry = new SqlFactoryRegistry($this, $this->em, $this->state);
            $this->groupByRule = $registry->getRule($this->method, $this->groupByConfig);
        }
    }
}