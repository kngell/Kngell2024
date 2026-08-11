<?php

declare(strict_types=1);

abstract class AbstractModelBaseOperations implements ModelOperationsInterface, ModelStrategyInterface
{
    public function __construct(protected ModelUtilityInterface $utils, protected Model $md)
    {
    }

    protected function getQueryResult(EntityManagerInterface $em, bool $skipped = false, string $reason = 'Operation Already done'): QueryResult
    {
        if (!$skipped) {
            return $em->persist()->getQueryResult()->setLastOperationId($em->getLastOperationId());
        }
        $em->reset();
        $result = $em->getQueryResult()->setSkipped(true, $reason);
        return match (true) {
            $this instanceof ModelOperationUpdate => $result->setSqlOperation(SqlStatement::UPDATE),
            $this instanceof ModelOperationDelete => $result->setSqlOperation(SqlStatement::DELETE),
            default => $result
        };
    }
}