<?php

declare(strict_types=1);

trait BulkUpdateTrait
{
    public function bulkUpdate(
        null|string|Closure $table = null,
        mixed $data = null,
        array $conditions = [],
        ?BulkUpdateType $type = null,
    ): void {
        if (empty($data) && !$this->em->hasData()) {
            throw new RepositoryException('No data to save!');
        }

        $type = $type ?? BulkUpdateType::AUTO;
        $qb = $this->em->createQueryBuilder();

        $keyField = $this->em->getEntityKeyField();

        $update = match ($type) {
            BulkUpdateType::VALUES_CONSTRUCTOR => $this->valuesConstructorUpdate($qb, $table, $data, $keyField),
            BulkUpdateType::TEMP_TABLE => $this->tempTableUpdate($qb, $table, $data, $conditions, $keyField),
            BulkUpdateType::UPSERT => $this->upsertUpdate($qb, $table, $data, $conditions, $keyField),
            BulkUpdateType::BATCH => $this->BatchUpdate($qb, $data, $conditions, $keyField),
            BulkUpdateType::AUTO => $this->autoUpdate($qb, $table, $data, $conditions, $keyField),
        };

        $update->build();
    }

    protected function valuesConstructorUpdate(QueryBuilder $qb, mixed $data, string $keyField, null|string|Closure $table = null): SqlUpdateQueryBuilderInterface
    {
        return $qb->bulkUpdate($table, BulkUpdateType::VALUES_CONSTRUCTOR)
            ->innerJoin($data)
            ->on($keyField, 'subquery.' . $keyField)
            ->set();
    }

    protected function tempTableUpdate(QueryBuilder $qb, null|string|Closure $table, mixed $data, array $conditions, string $keyField): SqlUpdateQueryBuilderInterface
    {
        $update = $qb->bulkUpdate($table, BulkUpdateType::TEMP_TABLE)
            ->innerJoin($qb->select()->from($data))
            ->on($keyField, 'subquery.' . $keyField)
            ->set();

        if (!empty($conditions)) {
            $update->where($conditions);
        }

        return $update;
    }

    protected function autoUpdate(QueryBuilder $qb, null|string|Closure $table, mixed $data, array $conditions, string $keyField): SqlUpdateQueryBuilderInterface
    {
        return empty($conditions) && count($data) <= 1000
            ? $this->valuesConstructorUpdate($qb, $table, $data, $keyField)
            : $this->tempTableUpdate($qb, $table, $data, $conditions, $keyField);
    }

    // protected function upsertUpdate(QueryBuilder $qb, mixed $data, string $keyField): SqlUpdateQueryBuilderInterface
    // {
    //     // Implementation depends on your ORM
    //     return $qb->upsert()
    //         ->data($data)
    //         ->onConflict($keyField)
    //         ->doUpdate();
    // }

    // protected function batchUpdate(QueryBuilder $qb, mixed $data, array $conditions): SqlUpdateQueryBuilderInterface
    // {
    //     return $qb->transaction(function () use ($data, $conditions) {
    //         foreach ($data as $row) {
    //             $this->update(array_merge($conditions, ['id' => $row['id']]));
    //         }
    //     });
    // }
}