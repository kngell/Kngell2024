<?php

declare(strict_types=1);

/**
 * Value object representing a single flash message.
 * Used for type-safety between Flash service ↔ Component ↔ JSON.
 */
final class FlashMessageDTO
{
    public function __construct(
        public readonly string $type,
        public readonly string $message,
        public readonly ?string $title = null,
        public readonly ?int $duration = null,
        public readonly bool $dismissible = true,
        public readonly bool $showProgress = false,
        public readonly array $extra = [],
    ) {
    }

    public function isError(): bool
    {
        return $this->type === FlashType::DANGER->value;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'message' => $this->message,
            'title' => $this->title,
            'duration' => $this->duration,
            'dismissible' => $this->dismissible,
            'showProgress' => $this->showProgress,
            'extra' => $this->extra,
        ];
    }

    /**
     * Build a DTO from the array shape stored by Flash::add().
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type:         $data['type'] ?? 'info',
            message:      $data['message'] ?? '',
            title:        $data['title'] ?? null,
            duration:     isset($data['duration']) ? (int) $data['duration'] : null,
            dismissible:  $data['dismissible'] ?? true,
            showProgress: ($data['showProgress'] ?? false) && !empty($data['duration']),
            extra:        $data['extra'] ?? [],
        );
    }

    /**
     * Build directly from a FlashType + message (convenience for ad-hoc use).
     */
    public static function from(
        FlashType $type,
        string $message,
        ?string $title = null,
        ?int $duration = null,
        bool $dismissible = true,
        bool $showProgress = true,
        array $extra = [],
    ): self {
        return new self(
            type:         $type->value,
            message:      $message,
            title:        $title,
            duration:     $duration,
            dismissible:  $dismissible,
            showProgress: $showProgress && !empty($duration),
            extra:        $extra,
        );
    }
}