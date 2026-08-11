<?php

declare(strict_types=1);

abstract class AbstractEvent implements EventInterface
{
    private array $results = [];
    private bool $propagationStopped = false;

    public function __construct(
        private readonly EventDataDTO $data,
    ) {
    }

    // Delegate data access to the DTO
    public function getName(): string
    {
        return $this->data->getEventName() ?? static::class;
    }

    public function getObject(): ?object
    {
        return $this->data->getRecord();
    }

    public function getParams(): array
    {
        // Flatten DTO data into params format for backward compatibility
        return [
            'id' => $this->data->getIdentifier(),
            'public_id' => $this->data->getPublicId(),
            'data' => $this->data->getEventData(),
            'operation' => $this->data->getOperation(),
            'timestamp' => $this->data->getTimestamp(),
            'deletion_option' => $this->data->getDeleteOption(),
            'form_data' => $this->data->getFormData(),
            'media' => $this->data->getMedia(),
            'model_data' => $this->data->getModelData(),
            'context' => $this->data->getContext(),
        ];
    }

    // Direct DTO access for modern listeners
    public function getData(): EventDataDTO
    {
        return $this->data;
    }

    // Event behavior methods
    public function setResults(mixed $results): static
    {
        $this->results = is_array($results) ? $results : [$results];
        return $this;
    }

    public function addResult(string $key, mixed $value): static
    {
        $this->results[$key] = $value;
        return $this;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function hasDatabaseChanges(): bool
    {
        foreach ($this->results as $result) {
            if ($result instanceof QueryResult && $result->getAffectedRows() > 0) {
                return true;
            }
            if (is_object($result) && $result->changed) {
                return true;
            }
            if ($result === true) {
                return true;
            }
        }
        return false;
    }

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    // Factory methods (now create DTO + wrap in event)
    public static function forDeletion(
        string $eventName,
        array $id,
        array $eventData,
        object $record,
        string $deleteOption,
        string $operationType,
    ): static {
        $dto = EventDataDTO::from(
            eventName: $eventName,
            record: $record instanceof Entity ? $record : null,
            identifier: $id,
            publicId: $eventData['public_id'] ?? null,
            eventData: $eventData,
            deleteOption: $deleteOption,
            operation: $operationType,
            timestamp: time(),
            context: [
                'deletion_type' => $deleteOption === 'permanent' ? 'permanent' : 'soft',
            ],
        );

        return new static($dto);
    }

    public static function forSave(
        string $eventName,
        array $id,
        array $eventData,
        object $record,
        string $operationType,
    ): static {
        $dto = EventDataDTO::from(
            eventName: $eventName,
            record: $record instanceof Entity ? $record : null,
            identifier: $id,
            publicId: $eventData['public_id'] ?? null,
            eventData: $eventData,
            operation: $operationType,
            timestamp: time(),
        );

        return new static($dto);
    }
}