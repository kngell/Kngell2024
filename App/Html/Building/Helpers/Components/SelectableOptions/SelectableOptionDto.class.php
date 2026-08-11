<?php

declare(strict_types=1);

class SelectableOptionDto
{
    public array $optionActiveClass = [];

    public function __construct(
        public readonly ?string $title = null, // ← Made optional
        public readonly string $radioName = 'option-radio-name',
        public readonly ?string $radioValue = null,
        public readonly null|AbstractHtmlComponent|string $content = null,
        public readonly array $icons = [],
        public readonly bool $isDefault = false,

        // ─── Option Classes ──────────────────────────────────────────────
        public readonly array $optionClass = ['selectable-options__option'],
        public readonly array $optionHeaderClass = ['selectable-options__option-header'],
        public readonly array $optionContentClass = ['selectable-options__option-content'],
        public readonly array $optionInfoClass = ['selectable-options__option-info'],
        public readonly array $optionTitleClass = ['selectable-options__info-title'],
        public readonly array $optionIconClass = ['selectable-options__info-icons'],
        public readonly array $optionDescriptionClass = ['selectable-options__info-description'],
        public readonly array $optionInfoIconsClass = ['selectable-options__info-icons'],
        public readonly ?string $optionId = null,
        public readonly bool $isWrappedRadio = true,
        public readonly ?string $description = null,
        public readonly array $attributes = [],
        public readonly ?string $action = null,
        public readonly ?string $actionLabel = null,
        public readonly ?string $actionUrl = null,
        public readonly ?string $actionStyle = null,
        public readonly bool $isExpandable = false,
    ) {
        if ($this->isDefault) {
            $this->optionActiveClass[] = 'active';
        }
    }

    public function hasAction(): bool
    {
        return $this->action !== null && $this->actionLabel !== null;
    }

    public function getModalAttribute(): ?string
    {
        if ($this->action === 'modal' && isset($this->attributes['data-modal'])) {
            return $this->attributes['data-modal'];
        }
        return null;
    }

    public function hasTitle(): bool
    {
        return $this->title !== null && !empty($this->title);
    }
}