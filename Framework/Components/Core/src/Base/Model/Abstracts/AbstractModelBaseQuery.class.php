<?php

declare(strict_types=1);

abstract class AbstractModelBaseQuery implements ModelQueryInterface, ModelStrategyInterface
{
    public function __construct(protected ModelUtilityInterface $utils)
    {
    }

    protected function getQueryResult(EntityManagerInterface $em, ?string $operation = null): QueryResult
    {
        $result = $em->persist()->getQueryResult();
        if ($operation !== null) {
            $result->setOperation($operation)->setLastOperationId($em->getLastOperationId());
        }
        return $result;
    }
}