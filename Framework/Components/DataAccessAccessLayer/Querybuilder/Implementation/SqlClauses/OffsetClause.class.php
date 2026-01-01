<?php

declare(strict_types=1);

class OffsetClause extends SqlComponent implements RegularClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::OFFSET;

    private ?QueryRulesInterface $offsetRule;

    public function __construct(
        private EntityManagerInterface $em,
        private array $offsetConfig,
    ) {
    }

    public function build(): string
    {
        $this->initializeRule();
        $this->query = $this->offsetRule->getRule($this->offsetConfig);
        return $this->query;
    }

    public function getSqlClause(): SqlClause
    {
        return self::CLAUSE;
    }

    private function initializeRule(): void
    {
        if (!isset($this->offsetRule)) {
            $registry = new SqlFactoryRegistry($this, $this->em, $this->state);
            $this->offsetRule = $registry->getRule('offset', $this->offsetConfig);
        }
    }
}