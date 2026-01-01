<?php

declare(strict_types=1);
class EntityBuilder
{
    private string $entityClass;
    private array $tableAlias = [];
    private array $tableMap = [];
    private array $data = [];
    private array $relationships = [];
    private ?EntityMapperInterface $mapper = null;
    private ?EntityHydratorInterface $hydrator = null;
    private ?EntityRelationManagerInterface $relationManager = null;
    private ?EntityFactoryInterface $factory;
    private ?TypeHandlerFactory $typeFactory;

    public function __construct(?EntityFactoryInterface $factory, ?TypeHandlerFactory $typeFactory)
    {
        $this->factory = $factory;
        $this->typeFactory = $typeFactory;
    }

    public function setEntityClass(string $entityClass): self
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function addRelationship(string $name, array $data): self
    {
        $this->relationships[$name] = $data;
        return $this;
    }

    public function setTableAlias(array $tableAlias): self
    {
        $this->tableAlias = $tableAlias;
        return $this;
    }

    public function setTableMap(array $tableMap): self
    {
        $this->tableMap = $tableMap;
        return $this;
    }

    public function build(): Entity
    {
        if ($this->factory) {
            return $this->factory->createWithRelationships(
                $this->entityClass,
                $this->data,
                $this->relationships,
                $this->tableAlias,
                $this->tableMap,
            );
        }
        $entityClass = $this->entityClass;
        // Fallback to manual creation
        $entity = new $entityClass(
            mapper: $this->mapper ?? new EntityMapper(),
            relationManager: $this->relationManager ?? new EntityRelationManager($this->factory),
            hydrator: $this->hydrator ?? new EntityHydrator(
                new DefaultTypeNormalizer($this->typeFactory),
                new ChangeTracker(new EntityMapper()),
                new EntityMapper(),
            ),
            tableAlias: $this->tableAlias,
            tableMap: $this->tableMap,
        );

        $entity->pdoHydrate($this->data);

        foreach ($this->relationships as $relationName => $relationData) {
            $entity->__set($relationName . '.', $relationData);
        }

        $entity->completeHydration();
        return $entity;
    }
}

// // Simple creation
// $user = $factory->create(User::class);

// // From database
// $user = $factory->createFromDatabase(User::class, [
//     'id' => 1,
//     'name' => 'John Doe',
//     'email' => 'john@example.com'
// ]);

// // From client data
// $user = $factory->createFromClient(User::class, [
//     'name' => 'John Doe',
//     'email' => 'john@example.com'
// ]);

// // Using builder for complex scenarios
// $user = (new EntityBuilder($factory))
//     ->setEntityClass(User::class)
//     ->setData(['name' => 'John Doe'])
//     ->addRelationship('profile', ['bio' => 'Software Developer'])
//     ->addRelationship('roles', [['name' => 'admin'], ['name' => 'user']])
//     ->build();