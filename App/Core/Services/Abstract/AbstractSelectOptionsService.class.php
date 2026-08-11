<?php

declare(strict_types=1);

abstract class AbstractSelectOptionsService implements SelectOptionsServiceInterface
{
    use EntityDisplayTrait;

    private ?SelectOptionConfig $config = null;
    private ?array $cachedOptions = null;

    public function getAllOptions(): array
    {
        return $this->getActiveOptions(false);
    }

    public function getActiveOptions(bool $active = true): array
    {
        if ($this->cachedOptions !== null) {
            return $this->cachedOptions;
        }

        try {
            $options = $this->fetchOptions($active);
            $this->cachedOptions = $options;
            return $options;
        } catch (QueryResultException $e) {
            $this->logError($e);
            return $this->getDefaultOptions();
        }
    }

    public function clearCache(): void
    {
        $this->cachedOptions = null;
    }

    protected function getSelectLabel(): string
    {
        return defined('static::SELECT_LABEL') ? static::SELECT_LABEL : '-- Select an option --';
    }

    protected function getEntityClass(): ?string
    {
        return $this->entityClass ?? null;
    }

    protected function getIdMethod(): string
    {
        return $this->idMethod ?? 'getId';
    }

    protected function getLabelMethod(): ?string
    {
        return $this->labelMethod ?? null;
    }

    protected function getDefaultOptionsArray(): array
    {
        return $this->defaultOptions ?? [];
    }

    protected function getSelectLabelFromConfig(): string
    {
        return $this->getConfig()->selectLabel;
    }

    protected function getEntityClassFromConfig(): ?string
    {
        return $this->getConfig()->entityClass;
    }

    protected function getDefaultOptions(): array
    {
        $config = $this->getConfig();

        if (!empty($config->defaultOptions)) {
            return $config->defaultOptions;
        }

        return ['' => $config->selectLabel];
    }

    protected function processEntities(iterable $entities): array
    {
        $config = $this->getConfig();
        $options = ['' => $config->selectLabel];
        $entityClass = $config->entityClass;

        foreach ($entities as $entity) {
            if ($entityClass !== null && !($entity instanceof $entityClass)) {
                continue;
            }

            if (!is_object($entity)) {
                continue;
            }
            $pkField = (string) $entity->getEntityKeyProperty();
            $id = $this->show($entity, $pkField);
            if ($id !== '' && $id !== null) {
                $options[$id] = $this->formatLabel($entity);
            }
        }

        return $options;
    }

    protected function formatLabel(object $entity): string
    {
        $config = $this->getConfig();

        if ($config->labelMethod && method_exists($entity, $config->labelMethod)) {
            return $entity->{$config->labelMethod}();
        }

        if (method_exists($entity, 'getName')) {
            return $entity->getName();
        }

        if (method_exists($entity, '__toString')) {
            return (string) $entity;
        }

        return '';
    }

    protected function logError(Throwable $e): void
    {
        $serviceClass = static::class;
        error_log("{$serviceClass}: Failed to load options - " . $e->getMessage());
    }

    abstract protected function fetchOptions(bool $active = true): array;

    /**
     * Get configuration from attribute or fallback to class constants/properties.
     */
    private function getConfig(): SelectOptionConfig
    {
        if ($this->config !== null) {
            return $this->config;
        }

        // Try to get from attribute first
        $reflection = new ReflectionClass($this);
        $attributes = $reflection->getAttributes(SelectOptionConfig::class);

        if (!empty($attributes)) {
            $this->config = $attributes[0]->newInstance();
        } else {
            // Fallback to traditional constants/properties
            $this->config = new SelectOptionConfig(
                selectLabel: $this->getSelectLabel(),
                entityClass: $this->getEntityClass(),
                idMethod: $this->getIdMethod(),
                labelMethod: $this->getLabelMethod(),
                defaultOptions: $this->getDefaultOptionsArray(),
            );
        }

        return $this->config;
    }

    // private function getEntityId(object $entity, ?string $idMethod): string|int
    // {
    //     if ($idMethod && method_exists($entity, $idMethod)) {
    //         return $entity->$idMethod();
    //     }

    //     return method_exists($entity, 'getId') ? $entity->getId() : '';
    // }
}