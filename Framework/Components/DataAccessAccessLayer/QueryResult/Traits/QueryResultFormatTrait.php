<?php

declare(strict_types=1);

trait QueryResultFormatTrait
{
    public function asArray(): mixed
    {
        $this->initialize();
        return $this->formatter->asArray();
    }

    public function asClass(?string $entityClass = null): mixed
    {
        $this->initialize();
        return $this->formatter->asClass($entityClass);
    }

    public function asClassWithRelations(?string $entityClass = null): mixed
    {
        $this->initialize();
        return $this->formatter->asClassWithRelations($entityClass);
    }

    public function asColumn(int $columnIndex = 0): array
    {
        $this->initialize();
        return $this->formatter->asColumn($columnIndex);
    }

    public function asKeyPairs(): array
    {
        $this->initialize();
        return $this->formatter->asKeyPairs();
    }

    public function asEntity(): mixed
    {
        $this->initialize();
        return $this->formatter->asClassWithRelations();
    }

    public function asObject(): mixed
    {
        $this->initialize();
        return $this->formatter->asObject();
    }
}
