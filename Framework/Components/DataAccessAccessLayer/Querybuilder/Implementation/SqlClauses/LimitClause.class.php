<?php

declare(strict_types=1);

class LimitClause extends SqlComponent implements RegularClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::LIMIT;

    private ?QueryRulesInterface $limitRule;

    public function __construct(
        EntityManagerInterface $em,
        private array $limitConfig,
    ) {
        parent::__construct(em:$em);
    }

    public function build(): string
    {
        $this->initializeRule();
        $this->query = $this->limitRule->getRule($this->limitConfig);
        return $this->query;
    }

    public function getSqlClause(): SqlClause
    {
        return self::CLAUSE;
    }

    private function initializeRule(): void
    {
        if (!isset($this->limitRule)) {
            $registry = new SqlFactoryRegistry($this, $this->em, $this->state);
            $this->limitRule = $registry->getRule('limit', $this->limitConfig);
        }
    }
}
