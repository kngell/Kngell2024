<?php

declare(strict_types=1);

class JsonRepository extends AbstractRepository implements RepositoryInterface
{
    public function __construct(private ?string $file = null)
    {
    }

    public function entity(Entity $entity): RepositoryInterface
    {
        return $this;
    }

    public function create(array $fields = []): int
    {
        return 0;
    }

    public function update(array $conditions): ?int
    {
        return 0;
    }

    public function delete(array $conditions = []): int
    {
        return 0;
    }

    public function findAndReturn(int $id, array $selectors = []): RepositoryInterface
    {
        return $this;
    }

    public function findAll(): mixed
    {
        $file = file_get_contents($this->file);

        return json_decode($file, true);
    }

    public function findByID(int $id): DataMapperInterface
    {
        /** @var DataMapperInterface */
        $obj = '';
        if ($obj instanceof DataMapperInterface) {
            return $obj;
        }
    }

    public function findBy(array $selectors = [], array $conditions = [], array $parameters = [], array $options = []): mixed
    {
    }

    public function findObjectBy(array $conditions = [], array $selectors = []): object
    {
        return new stdClass;
    }

    public function findOneBy(array $conditions, array $options): mixed
    {
    }

    public function findBySearch(array $selectors = [], array $conditions = [], array $parameters = [], array $options = []): array
    {
        return [];
    }

    public function findByIDAndDelete(array $conditions): bool
    {
        return false;
    }

    public function findByIdAndUpdate(array $fields = [], int $id = 0): bool
    {
        return false;
    }

    public function findWithSearchAndPagin(array $args, object $request): array
    {
        return [];
    }

    public function get_tableColumn(array $options): object
    {
        return new stdClass;
    }
}
