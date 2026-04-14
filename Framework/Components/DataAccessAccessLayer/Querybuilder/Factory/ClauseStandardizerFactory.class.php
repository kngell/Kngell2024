<?php

declare(strict_types=1);

class ClauseStandardizerFactory
{
    private array $standardizerMap = [];

    public function __construct()
    {
        $this->registerStandardizers();
    }

    public function create(string $method): ?DataStandardizerInterface
    {
        $clause = SqlBuilderMethodRegistry::getClauseContext($method);

        if (!$clause) {
            return null;
        }

        $standardizerClass = $this->standardizerMap[$clause->value] ?? null;

        if (!$standardizerClass) {
            return null;
        }

        return new $standardizerClass();
    }

    public function registerStandardizer(string $clause, string $standardizerClass): self
    {
        $this->standardizerMap[$clause] = $standardizerClass;
        return $this;
    }

    private function registerStandardizers(): void
    {
        $this->standardizerMap[SqlClause::FROM->value] = OnDataStandardizer::class;
        $this->standardizerMap[SqlClause::WHERE->value] = WhereDataStandardizer::class;
    }
}