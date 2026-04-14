<?php

declare(strict_types=1);

abstract class AbstractRuleFactory extends AbstractRulesFactory implements RuleFactoryInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected QueryState $state,
        protected BulkRowFactory $bulkRowFactory,
    ) {
        parent::__construct($em, $state, $bulkRowFactory);
    }
}
