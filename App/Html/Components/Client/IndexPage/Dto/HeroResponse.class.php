<?php

declare(strict_types=1);

class HeroResponse extends AbstractBaseEntityResponse
{
    public function __construct(
        array $image,
        ?Hero $hero,
        bool $isDefault = false,
    ) {
        parent::__construct($image, $hero, $isDefault);
    }

    public function getHero(): ?Hero
    {
        return $this->entity;
    }

    public function getTitle(): string
    {
        return $this->getHero()?->getTitle() ?? 'Welcome';
    }

    // Hero-specific methods
    public function getIntroduction(): ?string
    {
        return $this->getHero()?->getIntroduction();
    }

    public function getCtaText(): ?string
    {
        return $this->getHero()?->getCtaText();
    }
}