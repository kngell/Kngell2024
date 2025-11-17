<?php

declare(strict_types=1);

class IntoClause extends SqlQueryComponent implements ClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::INTO;
    private const String CLAUSE_PREFIX = 'INSERT ';

    public function __construct(
        null|string|Closure $table,
        private EntityManagerInterface $em,
        private InsertDataBuilder $dataBuilder,
    ) {
        $this->table = $table;
    }

    public function build(): string
    {
        $into = [];
        $table = $this->resolveTable();
        if ($table === null) {
            throw new InvalidArgumentException('No table specified for INSERT');
        }
        $into[] = $table;
        $columns = $this->resolveColumns();

        $this->state->table = $table;

        if (!empty($columns)) {
            $into[] = "($columns)";
        }{
            $this->query = implode(' ', $into);
            return $this->query;
        }
        $this->query = implode(' ', $into);

        return $this->query;
    }

    public function getSqlClause(): SqlClause
    {
        return self::CLAUSE;
    }

    public function getPrefix(): string
    {
        return self::CLAUSE_PREFIX;
    }

    private function resolveTable(): ?string
    {
        if (is_null($this->table)) {
            $entity = $this->em->getEntity();
            if ($entity instanceof Entity) {
                return $entity->table();
            }
            if ($entity instanceof CollectionInterface) {
                return $entity->first()->table();
            }
        } elseif (is_string($this->table)) {
            return $this->table;
        } elseif ($this->table instanceof Closure) {
            // TODO: Handle closure
        }
        return null;
    }

    private function resolveColumns(): string
    {
        $columns = $this->dataBuilder->getColumns();

        if (empty($columns)) {
            return '';
        }

        return implode(', ', $columns);
    }
}