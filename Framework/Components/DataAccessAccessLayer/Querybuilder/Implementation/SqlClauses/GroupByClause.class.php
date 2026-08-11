<?php

declare(strict_types=1);
class GroupByClause extends SqlComponent implements RegularClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::GROUP_BY;

    private ?QueryRulesInterface $groupByRule;

    public function __construct(
        EntityManagerInterface $em,
        private array $groupByConfig,
        ?string $table,
    ) {
        parent::__construct(em: $em);
        $this->method = $groupByConfig['method'];
        $this->table = $table;
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
            $this->groupByRule = $registry->getRule($this->method, $this->groupByConfig, $this->customAlias);
        }
    }
}