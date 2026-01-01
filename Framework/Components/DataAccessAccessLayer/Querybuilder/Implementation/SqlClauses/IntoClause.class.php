<?php

declare(strict_types=1);

class IntoClause extends SqlComponent implements RegularClauseComponentInterface
{
    private const SqlClause CLAUSE = SqlClause::INTO;
    private const String CLAUSE_PREFIX = 'INSERT ';

    public function __construct(
        null|string|Closure $table,
        private EntityManagerInterface $em,
        private ProcessedInsertData $processedData,
        null|string $method,
    ) {
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
        $this->state->table = $table;
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
    // private function resolveColumnss(): string
    // {
    //     $insertData = $this->dataBuilder->getData();
    //     if (empty($this->insertData)) {
    //         if ($this->em->hasData()) {
    //             $insertData = $this->em->getEntityData();
    //         }
    //     }
    //     if (is_array($insertData) && count($insertData) === 1 && isset($insertData[0])) {
    //         $insertData = ArrayUtils::first($insertData);
    //     }
    //     if ($insertData instanceof Entity) {
    //         $insertData = $insertData->toArray();
    //     }
    //     if (ArrayUtils::isObjectList($insertData)) {
    //         $insertData = $insertData[0] instanceof Entity ? $insertData[0]->toArray() : [];
    //     }
    //     $isBatchInsert = ArrayUtils::isMultidimentional($insertData) &&
    //         ArrayUtils::isSequential($insertData) &&
    //         is_array(ArrayUtils::first($insertData));

    //     if (!$isBatchInsert) {
    //         $columns = array_keys($insertData);
    //     } else {
    //         $columns = array_keys(ArrayUtils::first($insertData));
    //     }

    //     return implode(', ', $columns);
    // }
}