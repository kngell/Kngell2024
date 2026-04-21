<?php

declare(strict_types=1);

trait AriaAttributesTrait
{
    protected array $aria = [];
    protected string $ariaLabel = '';
    protected bool $ariaHaspopup = false;
    protected bool $ariaExpanded = false;
    protected bool $ariaHidden = false;
    protected string $role = '';

    public function aria(string ...$props): static
    {
        $aria = [];
        if (ArrayUtils::isKeyValueList($props)) {
            $props = ArrayUtils::fromSequentialToAssoc($props);
        }
        foreach ($props as $name => $prop) {
            $aria['aria-' . $name] = $prop;
        }
        $this->aria = array_merge($this->aria, $aria);
        return $this;
    }

    public function ariaLabel(string $ariaLabel): static
    {
        $this->ariaLabel = $ariaLabel;
        return $this;
    }

    public function ariaHidden(bool $ariaHidden = true): static
    {
        $this->ariaHidden = $ariaHidden;
        return $this;
    }

    public function ariaHaspopup(bool $ariaHaspopup): static
    {
        $this->ariaHaspopup = $ariaHaspopup;
        return $this;
    }

    public function ariaExpanded(bool $ariaExpanded): static
    {
        $this->ariaExpanded = $ariaExpanded;
        return $this;
    }

    public function role(string $role): static
    {
        $this->role = $role;
        return $this;
    }
}