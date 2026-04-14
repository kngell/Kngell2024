<?php

declare(strict_types=1);

class CycleClause extends SqlComponent implements RegularClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::CYCLE;

    public function __construct(
        ?EntityManagerInterface $em = null,
        private ?string $column = null,
        private ?string $cycleMarkColumn = null,
        private ?string $cycleValue = null,
        private ?string $nonCycleValue = null,
        private ?string $pathColumn = null,
    ) {
        parent::__construct(null, $em);
    }

    public function build(): string
    {
        if ($this->column === null) {
            return '';
        }

        $driverName = $this->em->getDriverName();
        $isMariaDB = $this->em->isMariaDB();
        $version = $this->em->getDatabaseVersion();

        if ($driverName === 'mysql' && $isMariaDB) {
            if ($version >= 10.5) {
                return $this->column . ' RESTRICT';
            }
            return '';
        }

        if ($driverName === 'mysql' && !$isMariaDB) {
            return '';
        }

        if ($driverName === 'pgsql') {
            return $this->buildPostgreSQLCycle();
        }

        if ($driverName === 'sqlite') {
            return '';
        }

        return $this->column . ' RESTRICT';
    }

    public function getSqlClause(): ?SqlClause
    {
        if ($this->state->statementContext === StatementType::BULK_UPDATE) {
            return null;
        }
        return self::CLAUSE;
    }

    private function buildPostgreSQLCycle(): string
    {
        $parts = ['CYCLE ' . $this->column];

        if ($this->cycleMarkColumn) {
            $parts[] = 'SET ' . $this->cycleMarkColumn;
            $parts[] = 'TO ' . ($this->cycleValue ?? "'1'");
            $parts[] = 'DEFAULT ' . ($this->nonCycleValue ?? "'0'");
        }

        if ($this->pathColumn) {
            $parts[] = 'USING ' . $this->pathColumn;
        }

        return implode(' ', $parts);
    }

    public static function forMariaDB(string $column): self
    {
        return new self(column: $column);
    }

    /**
     * Factory method for PostgreSQL (full standard).
     */
    public static function forPostgreSQL(
        string $column,
        string $cycleMarkColumn,
        string $pathColumn,
        string $cycleValue = "'1'",
        string $nonCycleValue = "'0'",
    ): self {
        return new self(
            column: $column,
            cycleMarkColumn: $cycleMarkColumn,
            cycleValue: $cycleValue,
            nonCycleValue: $nonCycleValue,
            pathColumn: $pathColumn,
        );
    }
}