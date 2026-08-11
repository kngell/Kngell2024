<?php

declare(strict_types=1);

class ContentBlockResponse extends AbstractBaseEntityResponse
{
    public function __construct(
        array $image,
        ?ContentBlock $contentBlock,
        private HtmlSectionPresentationService $presenter,
        bool $isDefault = false,
    ) {
        parent::__construct($image, $contentBlock, $isDefault);
    }

    public function getEntity(): ?ContentBlock
    {
        return $this->entity;
    }

    public function getTitle(): ?string
    {
        $entity = $this->getEntity();
        if (method_exists($entity, 'getTitle')) {
            return $this->presenter->showField($entity, 'title');
        }
        return null;
    }

    public function getDescription(): ?string
    {
        $entity = $this->getEntity();
        if (method_exists($entity, 'getSubTitle')) {
            return $this->presenter->showField($entity, 'subTitle');
        }

        return null;
    }

    // ContentBlock-specific methods
    public function getTitleIntro(): ?string
    {
        return $this->getEntity()?->get('title_intro');
    }

    public function getSpanTitle(): ?string
    {
        return $this->getEntity()?->get('title_span');
    }

    public function getCtaText(): ?string
    {
        $buttonText = $this->presenter->showField($this->getEntity(), 'buttonText');
        if ($buttonText) {
            return $buttonText;
        }
        return null;
    }

    public function getImageAlt(): ?string
    {
        $image = $this->getEntity()->get('image');
        return $image['alt'] ?? null;
    }
}