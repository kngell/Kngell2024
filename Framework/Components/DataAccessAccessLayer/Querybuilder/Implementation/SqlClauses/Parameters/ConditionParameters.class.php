<?php

declare(strict_types=1);

class ConditionParameters extends SqlComponent
{
    public function __construct(
        private ConditionRuleInterface $rule,
        EntityManagerInterface $em,
    ) {
        parent::__construct(em:$em);
    }

    public function build(): string
    {
        return $this->rule->getRule((array) $this->rule->getConditions());
    }

    public function getBindArr(): array
    {
        return $this->rule->getBindArr();
    }

    public function getTableAlias(): array
    {
        return $this->rule->getTableAlias();
    }

    public function getAliasCheck(): array
    {
        return $this->rule->getAliasCheck();
    }

    public function getParameters(): array
    {
        return $this->rule->getParameters();
    }
}
