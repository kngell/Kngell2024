<?php

declare(strict_types=1);

class InsertClauseBuilder extends AbstractClauseBuilder implements ClauseBuilderInterface
{
    private ProcessedInsertData $processedData;

    public function __construct(
        private SqlInsertQuery $query,
    ) {
    }

    protected function buildStatement(?SqlStatement $type = null): void
    {
        $statement = new InsertStatement(
            $this->query->getInsertMap(),
            $this->query->getQueryFlow(),
            $this->query->getEntityManager(),
            $this->processedData,
        );
        $this->query->add($statement);
    }

    protected function initializeProperties(): void
    {
        $processor = new InsertDataProcessor($this->query, $this->query->getInsertMap());
        $this->processedData = $processor->process();
    }

    protected function ensureMinimalFlow(): void
    {
        // If user called insert() but no into(), assume current table
        if ($this->query->hasInsert() && !$this->query->hasInto()) {
            $this->query->assumeInsertIntoCurrentTable();
        }

        if ($this->query->hasInsert() && !$this->query->hasValues()) {
            $insertMap = $this->query->getInsertMap();
            // if (array_key_exists('insert', $insertMap)) {
            //     if (!ArrayUtils::isStringList($insertMap['insert'])) {
            //         $this->query->assumeInsertDataHasInsertValues();
            //     } else {
            //         throw new InvalidArgumentException('No values are defined for comlumns :' . implode(', ', $insertMap['insert']));
            //     }
            // }
        }

        // Validate minimal requirements
        if (!$this->query->isClosure() && !$this->query->hasInto()) {
            throw new QueryFlowException('INSERT query requires INTO clause or entity with table definition.');
        }
    }

    protected function validateClauseOrder(): void
    {
        $userFlow = array_keys($this->query->getQueryFlow());
        $statementType = $this->query->getStatement();
        $categoryOrder = $statementType->getCategoryBuildOrder();

        $this->validateAllowedMethods($userFlow, $statementType);
        $this->validateCategoryOrder($userFlow, $categoryOrder);
    }
}
