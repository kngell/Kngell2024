<?php

declare(strict_types=1);

abstract class AbstractRulesFactory
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected QueryState $state,
        protected BulkRowFactory $bulkRowFactory,
        protected ?SqlComponent $component = null,
    ) {
    }

    protected function initialize(QueryRulesInterface $rule): QueryRulesInterface
    {
        if (method_exists($rule, 'initialize')) {
            $rule->initialize($this->state);
        }
        return $rule;
    }
}