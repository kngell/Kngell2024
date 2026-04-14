<?php

declare(strict_types=1);
class QueryFlowValidatorForDelete implements FlowValidatorInterface
{
    public function __construct(private SqlDeleteQuery $query)
    {
    }

    public function validate(array $queryFlow, array $map, array $conditions = []): void
    {
        $this->validateRequiredClauses($queryFlow);
        $this->validateUpdateMap($map);
    }

    private function validateRequiredClauses(array $queryFlow): void
    {
        if (!isset($queryFlow['delete'])) {
            throw new QueryFlowException('DELETE query requires delete or deleteFrom statement');
        }
    }

    private function validateUpdateMap(array $map): void
    {
        if (ArrayUtils::isDeepEmpty($map)) {
            $this->query->assumeEntityManagerHasData();
            return;
        }

        list($table, $fromData, $where) = $this->query->getMapFragments($map, ['from', 'where'], 'from');

        if ($table === null || $fromData === null) {
            $this->query->assumeDeleteCurrentTable();
        }
    }
}