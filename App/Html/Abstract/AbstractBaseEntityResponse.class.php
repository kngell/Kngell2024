<?php

declare(strict_types=1);
abstract class AbstractBaseEntityResponse extends AbstractFTOBaseResponse implements EntityResponseInterface
{
    public function __construct(
        array $image,
        protected readonly ?Entity $entity,
        bool $isDefault = false,
    ) {
        parent::__construct($image, $isDefault);
    }

    public function getEntity(): ?Entity
    {
        return $this->entity;
    }

    public function hasEntity(): bool
    {
        return $this->entity !== null && !$this->isDefault;
    }
}