<?php

declare(strict_types=1);

class HeroResponse extends AbstractBaseEntityResponse
{
    public function __construct(
        array $image,
        ?ContentBlock $hero,
        bool $isDefault = false,
    ) {
        parent::__construct($image, $hero, $isDefault);
    }

    public function getEntity(): ?ContentBlock
    {
        return $this->entity;
    }

    public function getTitle(): string
    {
        return $this->getEntity()?->getTitle() ?? 'Welcome';
    }

    // Hero-specific methods
    public function getTitleHighlight(): ?string
    {
        return $this->getEntity()?->getIntroduction();
    }

    public function getCtaText(): ?string
    {
        return $this->getEntity()?->getCtaText();
    }
}
