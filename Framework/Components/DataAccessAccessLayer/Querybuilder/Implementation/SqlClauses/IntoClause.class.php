<?php

declare(strict_types=1);

class IntoClause extends SqlComponent implements RegularClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::INTO;

    public function __construct(
        null|string|Closure $table,
        EntityManagerInterface $em,
        private ProcessedInsertData $processedData,
        null|string $method,
    ) {
        parent::__construct(em: $em);
        $this->table = $table;
        $this->method = $method;
    }

    public function build(): string
    {
        $into = [];
        $table = $this->resolveTable();
        $into[] = $table;
        $columns = $this->processedData->getFinalColumns();

        if (empty($columns)) {
            throw new QueryFlowException('No columns available for INSERT');
        }

        $into[] = '(' . implode(', ', $columns) . ')';
        $this->query = implode(' ', $into);

        return $this->query;
    }

    public function getSqlClause(): SqlClause
    {
        return self::CLAUSE;
    }

    private function resolveTable(): string
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
        }

        throw new InvalidArgumentException('No table specified for INSERT');
    }
}