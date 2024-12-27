<?php

declare(strict_types=1);

class Repository implements RepositoryInterface
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    public function create() : void
    {
        try {
            $this->em->createQueryBuilder()->insert()->build();
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function delete(array $conditions = []) : void
    {
        try {
            $conditions = $this->conditions($conditions);
            $this->em->createQueryBuilder()->delete()->where($conditions)->build();
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function update(array $conditions = []) : void
    {
        try {
            $conditions = $this->conditions($conditions);
            $this->em->createQueryBuilder()->update()->where($conditions)->build();
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function findByID(int|string $id): void
    {
        if ($this->isEmpty($id)) {
            try {
                $fieldId = $this->em->getEntityKeyField();
                $this->em->createQueryBuilder()->select()->where([$fieldId => $id])->build();
            } catch (Throwable $th) {
                throw $th;
            }
        }
    }

    public function findOneBy(array $conditions = []) : void
    {
        if ($this->isArray($conditions)) {
            try {
                $this->em->createQueryBuilder()->select()->where($conditions)->build();
            } catch (Throwable $th) {
                throw $th;
            }
        }
    }

    public function findAll(array $conditions = []): void
    {
        try {
            $this->findBy($conditions);
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function findBy(array $conditions = []) : void
    {
        try {
            $this->em->createQueryBuilder()->select()->where($conditions)->build();
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function showColumns(string $tableName) : void
    {
        try {
            $this->em->createQueryBuilder()->raw("SHOW COLUMNS FROM $tableName")->build();
        } catch (Throwable $th) {
            throw $th;
        }
    }

    private function conditions(array|string $conditions) : array
    {
        if (empty($conditions)) {
            $fieldId = $this->em->getEntityKeyField();
            $value = $this->em->getEntityKeyValue();
            $conditions = [$fieldId => $value];
        }
        return $conditions;
    }

    private function isArray(array $conditions) : bool
    {
        if (! is_array($conditions)) {
            throw new RepositoryInvalidArgumentException('Argument Supplied is not an array');
        }

        return true;
    }

    private function isEmpty(int|string $id) : bool
    {
        if (empty($id)) {
            throw new RepositoryInvalidArgumentException('Argument shuold not be empty');
        }
        return true;
    }
}