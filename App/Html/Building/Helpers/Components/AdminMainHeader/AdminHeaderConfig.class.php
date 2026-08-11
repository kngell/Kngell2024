<?php

declare(strict_types=1);

final readonly class AdminHeaderConfig
{
    /**
     * @param string[]       $breadcrumbs
     * @param HeaderButton[] $primaryActions
     */
    public function __construct(
        public string $title,
        public array $breadcrumbs = [],
        public array $primaryActions = [],
        public bool $showActions = true,
    ) {
    }

    public function withPrimaryActions(array $primaryActions): self
    {
        return new self(
            title: $this->title,
            breadcrumbs: $this->breadcrumbs,
            primaryActions: $primaryActions,
        );
    }
}