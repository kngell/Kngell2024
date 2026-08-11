<?php

declare(strict_types=1);

trait ConditionMethodsTrait
{
    /**
     * Apply a callback conditionally.
     *
     * @param bool $condition The condition to evaluate
     * @param callable $callback Callback that receives the current instance
     * @param callable|null $elseCallback Optional callback for false condition
     *
     * @return self
     */
    public function when(bool $condition, callable $callback, ?callable $elseCallback = null): self
    {
        if ($condition) {
            $callback($this);
        } elseif ($elseCallback !== null) {
            $elseCallback($this);
        }

        return $this;
    }

    public function unless(bool $condition, callable $callback, ?callable $elseCallback = null): self
    {
        return $this->when(!$condition, $callback, $elseCallback);
    }

    public function whenNotEmpty(mixed $value, callable $callback, ?callable $elseCallback = null): self
    {
        return $this->when(!empty($value), $callback, $elseCallback);
    }

    public function whenNull(mixed $value, callable $callback, ?callable $elseCallback = null): self
    {
        return $this->when($value === null, $callback, $elseCallback);
    }

    public function whenNotNull(mixed $value, callable $callback, ?callable $elseCallback = null): self
    {
        return $this->when($value !== null, $callback, $elseCallback);
    }

    public function whenContains(string $haystack, string $needle, callable $callback, ?callable $elseCallback = null): self
    {
        return $this->when(str_contains($haystack, $needle), $callback, $elseCallback);
    }

    public function whenStartsWith(string $haystack, string $needle, callable $callback, ?callable $elseCallback = null): self
    {
        return $this->when(str_starts_with($haystack, $needle), $callback, $elseCallback);
    }

    public function whenEndsWith(string $haystack, string $needle, callable $callback, ?callable $elseCallback = null): self
    {
        return $this->when(str_ends_with($haystack, $needle), $callback, $elseCallback);
    }
}