<?php

declare(strict_types=1);

abstract class AbstractModelBaseOperations implements ModelOperationsInterface, ModelStrategyInterface
{
    public function __construct(protected ModelUtilityInterface $utils)
    {
    }

    protected function getQueryResult(EntityManagerInterface $em): QueryResult
    {
        return $em->persist()->getQueryResult()->setLastOperationId($em->getLastOperationId());
    }
}