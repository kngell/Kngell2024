<?php

declare(strict_types=1);

abstract class AbstractModelBaseOperations implements ModelOperationsInterface, ModelStrategyInterface
{
    public function __construct(protected ModelUtilityInterface $utils, protected Model $md)
    {
    }

    protected function getQueryResult(EntityManagerInterface $em, bool $skipped = false): QueryResult
    {
        if (!$skipped) {
            return $em->persist()->getQueryResult()->setLastOperationId($em->getLastOperationId());
        }
        $em->reset();
        return $em->getQueryResult();
    }
}
