<?php

declare(strict_types=1);
trait MetadataTrait
{
    private array $blockMetadata = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->blockMetadata[$key] ?? $default;
    }

    public function set(string $key, mixed $value): self
    {
        $this->blockMetadata[$key] = $value;
        return $this;
    }

    public function has(string $key): bool
    {
        return isset($this->blockMetadata[$key]);
    }

    public function remove(string $key): self
    {
        unset($this->blockMetadata[$key]);
        return $this;
    }

    /**
     * @return array
     */
    public function getBlockMetadata(): array
    {
        return $this->blockMetadata;
    }

    /**
     * @param array $blockMetadata
     *
     * @return self
     */
    public function setBlockMetadata(array $blockMetadata): self
    {
        $this->blockMetadata = $blockMetadata;

        return $this;
    }
}