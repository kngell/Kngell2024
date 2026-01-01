<?php

declare(strict_types=1);

trait RegionContextTrait
{
    /**
     * Indicate if this context provides an explicit user choice
     * Default implementation returns false for automatic detection contexts.
     */
    public function providesExplicitChoice(): bool
    {
        return false;
    }

    /**
     * Get context name for debugging/logging.
     */
    public function getName(): string
    {
        return static::class;
    }

    /**
     * Check if context has explicit region (for contexts that store regions).
     */
    public function hasExplicitRegion(): bool
    {
        return $this->resolveRegion() !== null && $this->providesExplicitChoice();
    }
}