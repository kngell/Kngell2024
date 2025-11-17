<?php

declare(strict_types=1);

abstract class AbstractRuleFactory extends AbstractRulesFactory implements RuleFactoryInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected QueryState $state,
    ) {
        parent::__construct($em, $state);
    }
}