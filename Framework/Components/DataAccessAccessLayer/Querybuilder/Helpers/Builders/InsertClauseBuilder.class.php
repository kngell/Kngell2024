<?php

declare(strict_types=1);

class InsertClauseBuilder extends AbstractClauseBuilder implements ClauseBuilderInterface
{
    private mixed $insertData;
    private EntityManagerInterface $em;

    public function __construct(
        private SqlInsertQuery $query,
    ) {
    }

    public function buildAllClauses(): void
    {
        $this->initializeProperties();

        $this->ensureMinimalFlow();
        $this->validateClauseOrder();

        foreach (SqlStatementType::INSERT->getBuildOrder() as $clause) {
            if ($this->shouldBuildClause($clause)) {
                $this->buildClause($clause);
            }
        }
    }

    private function initializeProperties(): void
    {
        $this->em = $this->query->getEntityManager();
        $insertMap = $this->query->getInsertMap();
        $this->insertData = new InsertDataBuilder($this->query, $insertMap);
    }

    private function shouldBuildClause(string $clause): bool
    {
        $userFlow = array_keys($this->query->getQueryFlow());
        return in_array($clause, $userFlow);
    }

    private function ensureMinimalFlow(): void
    {
        // If user called insert() but no into(), assume current table
        if ($this->query->hasInsert() && !$this->query->hasInto()) {
            $this->query->assumeInsertIntoCurrentTable();
        }

        if ($this->query->hasInsert() && !$this->query->hasValues()) {
            $insertMap = $this->query->getInsertMap();
            if (array_key_exists('insert', $insertMap) && !ArrayUtils::isDeepEmpty($insertMap['insert'])) {
                if (!ArrayUtils::isStringList($insertMap['insert'])) {
                    $this->query->assumeInsertDataHasInsertValues();
                } else {
                    throw new InvalidArgumentException("No values are defined for comlumns : {implode(', ', $insertMap)} ");
                }
            }
        }

        // If user has into() but no insert(), assume all columns
        // if ($this->query->hasInto() && !$this->query->hasColumns()) {
        //     $this->query->assumeAllColumns();
        // }

        // Validate we have at least the minimal required
        if (!$this->query->isClosure() && (!$this->query->hasInsert() || !$this->query->hasInto())) {
            throw new QueryFlowException(
                'Query must have at least INSERT and INTO clauses. ' .
                'Called insert(): ' . ($this->query->hasInsert() ? 'yes' : 'no') . ', ' .
                'Called Into(): ' . ($this->query->hasInto() ? 'yes' : 'no'),
            );
        }
    }

    private function validateClauseOrder(): void
    {
        $userFlow = array_keys($this->query->getQueryFlow());
        $statementType = $this->query->getStatementType();
        $categoryOrder = $statementType->getCategoryBuildOrder();

        $this->validateAllowedMethods($userFlow, $statementType);
        $this->validateCategoryOrder($userFlow, $categoryOrder);
    }

    private function buildClause(string $clause): void
    {
        match($clause) {
            'into' => $this->buildInto(),
            'values' => $this->buildValues(),
            default => null
        };
    }

    private function buildInto(): void
    {
        $table = $this->query->getTable();
        $into = new IntoClause(
            $table,
            $this->em,
            $this->insertData->getData(),
        );
        $into->setMethod('into');
        $this->query->add($into);
    }

    private function buildValues(): void
    {
        $values = new ValuesClause(
            $this->em,
            $this->insertData->getData(),
        );
        $values->setMethod('values');
        $this->query->add($values);
    }
}