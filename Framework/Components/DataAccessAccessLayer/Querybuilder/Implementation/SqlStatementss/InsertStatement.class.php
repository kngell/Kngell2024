<?php

declare(strict_types=1);

class InsertStatement extends AbstractStatement
{
    private const StatementType TYPE = StatementType::SIMPLE_INSERT;

    public function __construct(
        array $insertMap,
        array $queryFlow,
        ?EntityManagerInterface $em,
        private ProcessedInsertData $processedData,
    ) {
        parent::__construct(self::TYPE, $queryFlow, $em);
        $this->map = $insertMap;
        $this->table = $insertMap['into'];
        $this->initialize();
    }

    public function build(): string
    {
        if ($this->table === null) {
            throw new QueryBuildException('INSERT statement requires a table');
        }
        $parts = [];

        // $this->state->tables[$this->table] = $this->table;

        $restOfQuery = parent::build();
        if (!empty($restOfQuery)) {
            $parts[] = $restOfQuery;
        }

        $this->query = implode(' ', $parts);
        return $this->query;
    }

    protected function buildSpecificClause(string $clause): void
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
            table: $this->table,
            em: $this->em,
            processedData: $this->processedData,
            method: 'into',
        );
        $this->add($into);
    }

    private function buildValues(): void
    {
        if ($this->processedData->hasData()) {
            $valuesClause = new ValuesClause(
                em: $this->em,
                processedData: $this->processedData,
                method: 'values',
            );
            $this->add($valuesClause);
        }
    }
}