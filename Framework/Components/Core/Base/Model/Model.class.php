<?php

declare(strict_types=1);

abstract class Model
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager->setEntity($this->entity());
    }

    /**
     * Get the value of entityManager.
     *
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    private function entity() : Entity
    {
        $entityName = str_replace('Model', '', $this::class);
        return App::getInstance()->get($entityName);
    }
}