<?php

declare(strict_types=1);
final class EntityConfiguration
{
    private TypeNormalizerInterface $normalizer;
    private ChangeTrackerInterface $changeTracker;
    private EntityMapperInterface $mapper;
    private EntityRelationManagerInterface $relationManager;
    private EntityHydratorInterface $hydrator;

    public function __construct(private TypeHandlerFactory $factory)
    {
        $this->normalizer = new DefaultTypeNormalizer($this->factory);
        $this->mapper = new EntityMapper();
        $this->changeTracker = new ChangeTracker($this->mapper);
        $this->hydrator = new EntityHydrator(
            $this->normalizer,
            $this->changeTracker,
            $this->mapper,
        );
        $this->relationManager = new EntityRelationManager(
            new EntityFactory($this->mapper, $this->normalizer, $this->changeTracker),
        );
    }

    /**
     * @return ChangeTrackerInterface
     */
    public function getChangeTracker(): ChangeTrackerInterface
    {
        return $this->changeTracker;
    }

    /**
     * @return EntityMapperInterface
     */
    public function getMapper(): EntityMapperInterface
    {
        return $this->mapper;
    }

    /**
     * @return EntityRelationManagerInterface
     */
    public function getRelationManager(): EntityRelationManagerInterface
    {
        return $this->relationManager;
    }

    /**
     * @return EntityHydratorInterface
     */
    public function getHydrator(): EntityHydratorInterface
    {
        return $this->hydrator;
    }
}