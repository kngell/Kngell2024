<?php

declare(strict_types=1);

class OrderByRule extends AbstractRules implements QueryRulesInterface
{
    public function __construct(
        private array $limitMap,
        EntityManagerInterface $em,
        ?string $method,
        QueryState $state,
        ?string $customAlias = null,
    ) {
        parent::__construct($em, $method, $customAlias, $state);
    }

    public function getRule(array $conditions): string
    {
        throw new Exception('Not implemented');
    }

    protected function normalize(array $arrayInput): array
    {
        throw new Exception('Not implemented');
    }
}