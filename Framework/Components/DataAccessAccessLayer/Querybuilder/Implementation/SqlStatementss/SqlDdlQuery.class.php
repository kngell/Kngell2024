<?php

declare(strict_types=1);
class SqlDdlQuery extends SqlQuery implements SqlDdlQueryBuilderInterface
{
    private const SqlStatementType TYPE = SqlStatementType::DELETE;

    public function __construct(null|EntityManagerInterface $em = null, array $initialParameters = [])
    {
        $this->method = self::TYPE->value;
        parent::__construct(self::TYPE, $em, $initialParameters);
    }

    public function createTable(string $table): self
    {
        return $this;
        throw new Exception('Not implemented');
    }

    public function dropTable(string $table): SqlDdlQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function alterTable(string $table): SqlDdlQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function truncateTable(string $table): SqlDdlQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function addColumn(string $column, string $type): SqlDdlQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function dropColumn(string $column): SqlDdlQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function modifyColumn(string $column, string $newType): SqlDdlQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function addPrimaryKey(string ...$columns): SqlDdlQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function addForeignKey(string $column, string $referencesTable, string $referencesColumn): SqlDdlQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function addIndex(string ...$columns): SqlDdlQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function execute(): array
    {
        throw new Exception('Not implemented');
    }

    public function getStatementType(): SqlStatementType
    {
        throw new Exception('Not implemented');
    }
}