<?php

declare(strict_types=1);

class IntoClause extends SqlQueryComponent implements ClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::INTO;

    public function __construct(null|String|Closure $table, private EntityManagerInterface $em, private mixed $insertData)
    {
        $this->table = $table;
    }

    public function build(): string
    {
        $into = [];
        $into[] = $this->resolveTable();
        if ($this->table === null) {
            throw new InvalidArgumentException('No table to insert Data');
        }

        $into[] = $this->resolveColumns();

        $this->state->table = $this->table;
        return implode(' ', $into);
    }

    public function getSqlClause(): ?SqlClause
    {
        $clause = SqlClause::tryFrom($this->method);
        if ($clause === self::CLAUSE) {
            return $clause;
        }
        return null;
    }

    private function resolveTable(): ?string
    {
        if (is_null($this->table)) {
            $entity = $this->em->getEntity();
            if ($entity instanceof Entity) {
                $this->table = $entity->table();
                return  $this->table;
            }
            if ($entity instanceof CollectionInterface) {
                $this->table = $entity->first()->table();
                return  $this->table;
            }
        } elseif (is_string($this->table)) {
            return $this->table;
        } elseif ($this->table instanceof Closure) {
            //Todo;
        }
        return null;
    }

    private function resolveColumns(): string
    {
        $insertData = $this->insertData;
        if (is_null($this->insertData)) {
            if ($this->em->hasData()) {
                $insertData = $this->em->getEntityData();
            }
        }
        $isBatchInsert = ArrayUtils::isMultidimentional($insertData) &&
            ArrayUtils::isSequential($insertData) &&
            is_array(ArrayUtils::first($insertData));

        if (!$isBatchInsert) {
            $columns = array_keys($insertData);
        } else {
            $columns = array_keys(ArrayUtils::first($insertData));
        }

        return implode(', ', $columns);
    }
}