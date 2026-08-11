<?php

declare(strict_types=1);

class ProductEvent extends AbstractEvent
{
    /**
     * Named constructor — self-documenting, type-safe payload. ✅.
     */
    public static function forDeletion(
        string $eventName,
        array $id,
        array $eventData,
        object $record,
        string $deleteOption,
        string $operationType,
    ): static {
        return new static(
            name: $eventName,
            object: $record,
            params: [
                'id' => $id,
                'public_id' => $eventData['public_id'] ?? null,
                'data' => $eventData,
                'deletion_type' => $deleteOption === 'permanent' ? 'permanent' : 'soft',
                'deletion_option' => $deleteOption,
                'operation' => $operationType,
                'timestamp' => time(),
            ],
        );
    }

    public static function forSave(
        string $eventName,
        array $id,
        array $eventData,
        object $record,
        string $operationType,
    ): static {
        return new static(
            name: $eventName,
            object: $record,
            params: [
                'id' => $id,
                'public_id' => $eventData['public_id'] ?? null,
                'data' => $eventData,
                'operation' => $operationType,
                'timestamp' => time(),
            ],
        );
    }
}