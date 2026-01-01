<?php

declare(strict_types=1);
// EntityServiceBuilder.php

class EntityServiceBuilder
{
    // The Builder accepts ALL non-circular dependencies needed by Factory/Manager
    public function __construct(
        private EntityMapperInterface $mapper,
        private TypeNormalizerInterface $normalizer,
        private ChangeTrackerInterface $changeTracker,
        private EntityHydratorInterface $hydrator,
        // ... any other dependencies needed by either service ...
    ) {
    }

    /**
     * Builds and returns the fully initialized EntityFactory.
     */
    public function buildFactory(): EntityFactoryInterface
    {
        // 1. Create the Manager first (it doesn't need the Factory in its constructor)
        $relationManager = new EntityRelationManager(/* Inject other deps here if necessary */);

        // 2. Create the Factory, injecting the Manager
        $factory = new EntityFactory(
            mapper: $this->mapper,
            normalizer: $this->normalizer,
            changeTracker: $this->changeTracker,
            hydrator: $this->hydrator,
            relationManager: $relationManager, // Inject the Manager here
        );

        // 3. Close the loop using the setter injection
        // This is the CRITICAL step that makes the circular dependency work.
        $relationManager->setFactory($factory);

        // 4. Return the fully wired Factory
        return $factory;
    }
}