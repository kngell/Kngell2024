<?php

declare(strict_types=1);

class InsertClauseBuilder extends AbstractClauseBuilder implements ClauseBuilderInterface
{
    private ProcessedInsertData $processedData;

    public function __construct(
        private SqlInsertQuery $query,
    ) {
    }

    protected function initializeProperties(): void
    {
        $processor = new InsertDataProcessor($this->query, $this->query->getInsertMap());
        $this->processedData = $processor->process();
    }

    protected function shouldBuildClause(string $clause): bool
    {
        $userFlow = array_keys($this->query->getQueryFlow());
        return in_array($clause, $userFlow);
    }

    protected function ensureMinimalFlow(): void
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
                    throw new InvalidArgumentException('No values are defined for comlumns :' . implode(', ', $insertMap['insert']));
                }
            }
        }

        // Validate minimal requirements
        if (!$this->query->isClosure() && !$this->query->hasInto()) {
            throw new QueryFlowException('INSERT query requires INTO clause or entity with table definition.');
        }
    }

    protected function validateClauseOrder(): void
    {
        $userFlow = array_keys($this->query->getQueryFlow());
        $statementType = $this->query->getStatementType();
        $categoryOrder = $statementType->getCategoryBuildOrder();

        $this->validateAllowedMethods($userFlow, $statementType);
        $this->validateCategoryOrder($userFlow, $categoryOrder);
    }

    protected function buildClause(string $clause): void
    {
        match($clause) {
            'into' => $this->buildInto(),
            'values' => $this->buildValues(),
            default => null
        };
    }

    private function buildInto(): void
    {
        $into = new IntoClause(
            table: $this->query->getTable(),
            em: $this->query->getEntityManager(),
            processedData: $this->processedData,
            method: 'into',
        );
        $this->query->add($into);
    }

    private function buildValues(): void
    {
        if ($this->processedData->hasData()) {
            $valuesClause = new ValuesClause(
                em: $this->query->getEntityManager(),
                processedData: $this->processedData,
                method: 'values',
            );
            $this->query->add($valuesClause);
        }
    }
}