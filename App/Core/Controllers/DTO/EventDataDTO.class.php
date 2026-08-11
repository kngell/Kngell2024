<?php

declare(strict_types=1);

final class EventDataDTO implements ArrayAccess
{
    public function __construct(
        private ?string $eventName = null,
        private ?int $entityId = null,
        private ?Entity $record = null,
        private array $formData = [],
        private array $identifier = [],
        private ?string $publicId = null,
        private array $eventData = [],
        private ?string $deleteOption = null,
        private ?string $operation = null,
        private ?int $timestamp = null,
        private bool $wasSkipped = false,
        private array $media = [],
        private array $modelData = [],
        private array $context = [],
        private ?string $pageTarget = null,
        private ?string $blockType = null,
        private ?int $keyId = null,
    ) {
    }

    // ===== Existing Getters =====

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function getRecord(): ?Entity
    {
        return $this->record;
    }

    public function getFormData(): array
    {
        return $this->formData;
    }

    public function getIdentifier(): array
    {
        return $this->identifier;
    }

    public function getPublicId(): ?string
    {
        return $this->publicId;
    }

    public function getEventData(): array
    {
        return $this->eventData;
    }

    public function getDeleteOption(): ?string
    {
        return $this->deleteOption;
    }

    public function getOperation(): ?string
    {
        return $this->operation;
    }

    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    public function wasSkipped(): bool
    {
        return $this->wasSkipped;
    }

    public function getMedia(): array
    {
        return $this->media;
    }

    public function getModelData(): array
    {
        return $this->modelData;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    // ===== New Helper Methods for Common Checks =====

    public function isInsert(): bool
    {
        return $this->operation === SqlStatement::INSERT->value;
    }

    public function isUpdate(): bool
    {
        return $this->operation === SqlStatement::UPDATE->value;
    }

    public function isDelete(): bool
    {
        return $this->operation === SqlStatement::DELETE->value;
    }

    public function hasRecord(): bool
    {
        return $this->record !== null;
    }

    public function hasMedia(): bool
    {
        return !empty($this->media);
    }

    public function hasFormData(): bool
    {
        return !empty($this->formData);
    }

    public function hasIdentifier(): bool
    {
        return !empty($this->identifier);
    }

    // ===== Type-safe Context Access =====

    public function getContextValue(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }

    public function hasContextKey(string $key): bool
    {
        return array_key_exists($key, $this->context);
    }

    /**
     * @param string[] $keys
     */
    public function hasAllContextKeys(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->hasContextKey($key)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string[] $keys
     *
     * @return array<string, mixed>
     */
    public function getContextValues(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->getContextValue($key);
        }
        return $result;
    }

    // ===== Fluent Setters (Immutable) =====

    public function withContext(array $context): self
    {
        $clone = clone $this;
        $clone->context = array_merge($this->context, $context);
        return $clone;
    }

    public function withEventData(array $eventData): self
    {
        $clone = clone $this;
        $clone->eventData = array_merge($this->eventData, $eventData);
        return $clone;
    }

    public function withFormData(array $formData): self
    {
        $clone = clone $this;
        $clone->formData = array_merge($this->formData, $formData);
        return $clone;
    }

    public function withRecord(?Entity $record): self
    {
        $clone = clone $this;
        $clone->record = $record;
        return $clone;
    }

    public function withMedia(array $media): self
    {
        $clone = clone $this;
        $clone->media = array_merge($this->media, $media);
        return $clone;
    }

    public function markAsSkipped(bool $skipped = true): self
    {
        $clone = clone $this;
        $clone->wasSkipped = $skipped;
        return $clone;
    }

    // ===== ArrayAccess Implementation =====

    public function offsetExists(mixed $offset): bool
    {
        if (property_exists($this, $offset)) {
            return $this->$offset !== null;
        }
        return $this->hasContextKey($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        // Try getter method first
        $method = 'get' . str_replace('_', '', ucwords($offset, '_'));
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        // Try direct property
        if (property_exists($this, $offset)) {
            return $this->$offset;
        }

        // Fall back to context
        return $this->getContextValue($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException('EventDataDTO is immutable');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('EventDataDTO is immutable');
    }

    // ===== Debug Helpers =====

    /**
     * Get a summary of the DTO for logging.
     */
    public function toSummary(): array
    {
        return [
            'event_name' => $this->eventName,
            'operation' => $this->operation,
            'page_target' => $this->getPageTarget(),
            'has_record' => $this->hasRecord(),
            'has_media' => $this->hasMedia(),
            'has_form_data' => $this->hasFormData(),
            'timestamp' => $this->timestamp,
            'was_skipped' => $this->wasSkipped,
            'context_keys' => array_keys($this->context),
        ];
    }

    /**
     * @return null|int
     */
    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function getPageTarget(): ?string
    {
        $pageTarget = null;
        if ($this->record !== null) {
            if ($this->record->hasProperty('page_target')) {
                $pageTarget = $this->record->getFieldValue('page_target');
            }
        }
        return $pageTarget;
    }

    /**
     * @return null|string
     */
    public function getBlockType(): ?string
    {
        return $this->blockType;
    }

    /**
     * @return null|int
     */
    public function getKeyId(): ?int
    {
        return $this->keyId;
    }

    // ===== Factory Method =====

    /**
     * Create a new EventDataDTO instance.
     *
     * @param string|null $eventName Name of the event (e.g., 'product.saved')
     * @param Entity|null $record The entity being operated on
     * @param array $identifier Record identifier(s)
     * @param array $formData Original form submission data
     * @param string|null $publicId Public identifier
     * @param array $eventData Additional event-specific data
     * @param string|null $deleteOption Delete option for delete operations
     * @param string|null $operation Operation type (insert/update/delete)
     * @param int|null $timestamp Unix timestamp
     * @param bool $wasSkipped Whether the operation was skipped
     * @param array $media Array of media file paths
     * @param array $modelData Raw model data
     * @param array $context Additional dynamic context
     */
    public static function from(
        ?string $eventName = null,
        ?int $entityId = null,
        ?Entity $record = null,
        array $identifier = [],
        array $formData = [],
        ?string $publicId = null,
        array $eventData = [],
        ?string $deleteOption = null,
        ?string $operation = null,
        ?int $timestamp = null,
        bool $wasSkipped = false,
        array $media = [],
        array $modelData = [],
        array $context = [],
        ?string $pageTarget = null,
        ?string $blockType = null,
        ?int $keyId = null,
    ): self {
        if ($timestamp === null) {
            $timestamp = time();
        }

        return new self(
            eventName: $eventName,
            entityId: $entityId,
            record: $record,
            formData: $formData,
            identifier: $identifier,
            publicId: $publicId,
            eventData: $eventData,
            deleteOption: $deleteOption,
            operation: $operation,
            timestamp: $timestamp,
            wasSkipped: $wasSkipped,
            media: $media,
            modelData: $modelData,
            context: $context,
            pageTarget: $pageTarget,
            blockType: $blockType,
            keyId: $keyId,
        );
    }
}