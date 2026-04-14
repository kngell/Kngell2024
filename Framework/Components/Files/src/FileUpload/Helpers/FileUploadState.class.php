<?php

declare(strict_types=1);

class FileUploadState
{
    private array $existingPaths = [];
    private array $keptPaths = [];
    private array $newPaths = [];
    private array $removedPaths = [];
    private bool $isTemporary = false;
    private bool $isPermanent = false;
    private array $metadata = [];

    public function __construct(
        array $existingPaths = [],
        array $keptPaths = [],
        array $newPaths = [],
        array $removedPaths = [],
        bool $isTemporary = false,
        bool $isPermanent = false,
        array $metadata = [],
    ) {
        $this->existingPaths = $existingPaths;
        $this->keptPaths = $keptPaths;
        $this->newPaths = $newPaths;
        $this->removedPaths = $removedPaths;
        $this->isTemporary = $isTemporary;
        $this->isPermanent = $isPermanent;
        $this->metadata = $metadata;
    }

    // State transition methods
    public function withPathsRemoved(array $pathsToRemove): self
    {
        $newKeptPaths = array_values(array_diff($this->keptPaths, $pathsToRemove));
        $newRemovedPaths = array_merge($this->removedPaths, $pathsToRemove);

        return new self(
            existingPaths: $this->existingPaths,
            keptPaths: $newKeptPaths,
            newPaths: $this->newPaths,
            removedPaths: $newRemovedPaths,
            isTemporary: $this->isTemporary,
            isPermanent: $this->isPermanent,
            metadata: $this->metadata,
        );
    }

    public function withNewPathsAdded(array $newPaths): self
    {
        return new self(
            existingPaths: $this->existingPaths,
            keptPaths: $this->keptPaths,
            newPaths: array_merge($this->newPaths, $newPaths),
            removedPaths: $this->removedPaths,
            isTemporary: $this->isTemporary,
            isPermanent: $this->isPermanent,
            metadata: $this->metadata,
        );
    }

    public function markAsTemporary(): self
    {
        return new self(
            existingPaths: $this->existingPaths,
            keptPaths: $this->keptPaths,
            newPaths: $this->newPaths,
            removedPaths: $this->removedPaths,
            isTemporary: true,
            isPermanent: false,
            metadata: $this->metadata,
        );
    }

    public function markAsPermanent(): self
    {
        return new self(
            existingPaths: $this->existingPaths,
            keptPaths: $this->keptPaths,
            newPaths: $this->newPaths,
            removedPaths: $this->removedPaths,
            isTemporary: false,
            isPermanent: true,
            metadata: $this->metadata,
        );
    }

    // Getters
    public function getAllPaths(): array
    {
        return array_merge($this->keptPaths, $this->newPaths);
    }

    public function getKeptPaths(): array
    {
        return $this->keptPaths;
    }

    public function getNewPaths(): array
    {
        return $this->newPaths;
    }

    public function getRemovedPaths(): array
    {
        return $this->removedPaths;
    }

    public function getExistingPaths(): array
    {
        return $this->existingPaths;
    }

    public function isTemporary(): bool
    {
        return $this->isTemporary;
    }

    public function isPermanent(): bool
    {
        return $this->isPermanent;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function withMetadata(array $metadata): self
    {
        return new self(
            existingPaths: $this->existingPaths,
            keptPaths: $this->keptPaths,
            newPaths: $this->newPaths,
            removedPaths: $this->removedPaths,
            isTemporary: $this->isTemporary,
            isPermanent: $this->isPermanent,
            metadata: $metadata,
        );
    }

    // Validation
    public function isValid(): bool
    {
        // Check for duplicates
        $allPaths = $this->getAllPaths();
        $uniquePaths = array_unique($allPaths);

        return count($allPaths) === count($uniquePaths);
    }

    public function hasChanges(): bool
    {
        return !empty($this->newPaths) || !empty($this->removedPaths);
    }

    // Serialization
    public function toArray(): array
    {
        return [
            'existing_paths' => $this->existingPaths,
            'kept_paths' => $this->keptPaths,
            'new_paths' => $this->newPaths,
            'removed_paths' => $this->removedPaths,
            'is_temporary' => $this->isTemporary,
            'is_permanent' => $this->isPermanent,
            'metadata' => $this->metadata,
            'all_paths' => $this->getAllPaths(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }

    // Factory methods for different states
    public static function createInitial(array $existingPaths = []): self
    {
        return new self(
            existingPaths: $existingPaths,
            keptPaths: $existingPaths, // Initially all are kept
            isTemporary: false,
            isPermanent: false,
        );
    }

    public static function createFromFormData(array $formData): self
    {
        $existingPaths = $formData['existing_paths'] ?? [];
        $removedPaths = $formData['removed_paths'] ?? [];

        // Filter out removed paths from kept paths
        $keptPaths = array_values(array_diff($existingPaths, $removedPaths));

        return new self(
            existingPaths: $existingPaths,
            keptPaths: $keptPaths,
            removedPaths: $removedPaths,
            newPaths: $formData['new_paths'] ?? [],
            isTemporary: $formData['is_temporary'] ?? false,
            isPermanent: $formData['is_permanent'] ?? false,
            metadata: $formData['metadata'] ?? [],
        );
    }
}