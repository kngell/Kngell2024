<?php

declare(strict_types=1);

/**
 * Fluent builder for flash message options.
 *
 * Usage:
 *   FlashOptions::sticky()
 *       ->withTitle('Action Required')
 *       ->showProgress(false)
 *       ->withExtra(['record_id' => 42])
 *       ->toArray();
 *
 *   FlashOptions::quick(3000)->withTitle('Saved')->toArray();
 *
 *   FlashOptions::default()->toArray(); // empty options
 */
final class FlashOptions
{
    private const DEFAULT_DURATION = 5000;

    private function __construct(
        private ?string $title = null,
        private ?int $duration = self::DEFAULT_DURATION,
        private bool $dismissible = true,
        private bool $showProgress = true,
        private array $extra = [],
    ) {
    }

    // ───────────────────────────────────────────────────────────
    // Fluent setters (return self for chaining)
    // ───────────────────────────────────────────────────────────

    public function withTitle(?string $title): self
    {
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function withDuration(?int $durationMs): self
    {
        $clone = clone $this;
        $clone->duration = $durationMs;
        return $clone;
    }

    public function makeSticky(): self
    {
        $clone = clone $this;
        $clone->duration = null;
        $clone->showProgress = false;
        return $clone;
    }

    public function dismissible(bool $dismissible = true): self
    {
        $clone = clone $this;
        $clone->dismissible = $dismissible;
        return $clone;
    }

    public function showProgress(bool $show = true): self
    {
        $clone = clone $this;
        $clone->showProgress = $show;
        return $clone;
    }

    public function withExtra(array $extra): self
    {
        $clone = clone $this;
        $clone->extra = array_merge($this->extra, $extra);
        return $clone;
    }

    // ───────────────────────────────────────────────────────────
    // Output
    // ───────────────────────────────────────────────────────────

    /**
     * Convert to the array format consumed by Flash::add() / FlashMessageDTO::from().
     *
     * @return array{
     *     title: ?string,
     *     duration: ?int,
     *     dismissible: bool,
     *     showProgress: bool,
     *     extra: array
     * }
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'duration' => $this->duration,
            'dismissible' => $this->dismissible,
            'showProgress' => $this->showProgress && !empty($this->duration),
            'extra' => $this->extra,
        ];
    }

    // ───────────────────────────────────────────────────────────
    // Static factories — common patterns
    // ───────────────────────────────────────────────────────────

    /** No specific options (defaults applied by Flash service). */
    public static function default(): self
    {
        return new self();
    }

    /** Sticky (no auto-dismiss) — typical for errors. */
    public static function sticky(): self
    {
        return new self(duration: null, showProgress: false);
    }

    /** Quick auto-dismiss with custom duration in milliseconds. */
    public static function quick(int $durationMs = 3000): self
    {
        return new self(duration: $durationMs);
    }

    /** Long-lived auto-dismiss (e.g., warnings that need reading). */
    public static function persistent(int $durationMs = 10000): self
    {
        return new self(duration: $durationMs);
    }

    /** Non-dismissible (no close button) — use sparingly. */
    public static function locked(): self
    {
        return new self(dismissible: false);
    }

    /** Build from an existing options array (useful for merging). */
    public static function fromArray(array $options): self
    {
        return new self(
            title:        $options['title'] ?? null,
            duration:     $options['duration'] ?? self::DEFAULT_DURATION,
            dismissible:  $options['dismissible'] ?? true,
            showProgress: $options['showProgress'] ?? true,
            extra:        $options['extra'] ?? [],
        );
    }
}