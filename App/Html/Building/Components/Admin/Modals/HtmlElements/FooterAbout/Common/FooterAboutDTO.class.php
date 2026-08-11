<?php

declare(strict_types=1);

final class FooterAboutDTO extends BaseFooterDTO
{
    private ?string $content;
    private ?string $logoUrl;
    private ?string $logoIcon;
    private ?string $logoAlt;
    private ?string $logoLink;

    public function __construct(
        string $cancelRoute,
        string $deleteRoute,
        ?string $content = null,
        ?string $logoUrl = null,
        ?string $logoIcon = null,
        ?string $logoAlt = null,
        ?string $logoLink = null,
        bool $isVisible = false,
        null|string|int $id = null,
        int $sortOrder = 0,
        bool $isActive = true,
        ?string $validFrom = null,
        ?string $validTo = null,
    ) {
        parent::__construct(
            cancelRoute: $cancelRoute,
            deleteRoute: $deleteRoute,
            isVisible: $isVisible,
            id: $id,
            sortOrder: $sortOrder,
            isActive: $isActive,
            validFrom: $validFrom,
            validTo: $validTo,
        );
        $this->content = $content;
        $this->logoUrl = $logoUrl;
        $this->logoIcon = $logoIcon;
        $this->logoAlt = $logoAlt;
        $this->logoLink = $logoLink;
    }

    public function toFormValues(): array
    {
        return array_merge(parent::toFormValues(), [
            'content' => $this->content,
            'logo_url' => $this->logoUrl,
            'logo_icon' => $this->logoIcon,
            'logo_alt' => $this->logoAlt,
            'logo_link' => $this->logoLink,
        ]);
    }

    /**
     * @return null|string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param null|string $content
     *
     * @return FooterAboutDTO
     */
    public function setContent(?string $content): FooterAboutDTO
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    /**
     * @param null|string $logoUrl
     *
     * @return FooterAboutDTO
     */
    public function setLogoUrl(?string $logoUrl): FooterAboutDTO
    {
        $this->logoUrl = $logoUrl;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLogoIcon(): ?string
    {
        return $this->logoIcon;
    }

    /**
     * @param null|string $logoIcon
     *
     * @return FooterAboutDTO
     */
    public function setLogoIcon(?string $logoIcon): FooterAboutDTO
    {
        $this->logoIcon = $logoIcon;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLogoAlt(): ?string
    {
        return $this->logoAlt;
    }

    /**
     * @param null|string $logoAlt
     *
     * @return FooterAboutDTO
     */
    public function setLogoAlt(?string $logoAlt): FooterAboutDTO
    {
        $this->logoAlt = $logoAlt;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLogoLink(): ?string
    {
        return $this->logoLink;
    }

    /**
     * @param null|string $logoLink
     *
     * @return FooterAboutDTO
     */
    public function setLogoLink(?string $logoLink): FooterAboutDTO
    {
        $this->logoLink = $logoLink;

        return $this;
    }
}