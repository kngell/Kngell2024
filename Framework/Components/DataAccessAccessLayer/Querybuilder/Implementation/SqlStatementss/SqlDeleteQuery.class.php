<?php

declare(strict_types=1);

class SqlDeleteQuery extends SqlQuery implements SqlDeleteQueryBuilderInterface
{
    private const SqlStatementType TYPE = SqlStatementType::DELETE;

    public function __construct(
        EntityManagerInterface $em,
    ) {
        $this->method = self::TYPE->value;
        parent::__construct(self::TYPE, $em);
        $this->initializeWithDependencies($em->getTableAliasHelper(), $this->getState());
    }

    public function deleteFrom(string $table): self
    {
        return $this;
        throw new Exception('Not implemented');
    }

    public function where(mixed ...$conditions): SqlDeleteQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function whereEqualTo(string $column, mixed $value): SqlDeleteQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function andWhere(string $column, mixed $value): SqlDeleteQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function orWhere(string $column, mixed $value): SqlDeleteQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function join(string $table, ?string $alias = null): SqlDeleteQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function on(string $leftColumn, string $rightColumn): SqlDeleteQueryBuilderInterface
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