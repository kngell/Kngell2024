<?php

declare(strict_types=1);

class PageConfig extends RendererConfig implements SectionConfigInterface
{
    private string $pageKey;
    private ?string $headerTitle = null;
    private mixed $beforeRenderCallback = null;
    private ?string $expectedControllerClass = null;
    private ?string $entityKey = null;
    private bool $hasContainer = false;
    private array $containerClass = [];

    private function __construct(string $pageKey)
    {
        $this->pageKey = $pageKey;
        parent::__construct();
    }

    public function getSectionConfigs(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getPageKey(): string
    {
        return $this->pageKey;
    }

    /**
     * @param string $pageKey
     *
     * @return PageConfig
     */
    public function setPageKey(string $pageKey): PageConfig
    {
        $this->pageKey = $pageKey;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getHeaderTitle(): ?string
    {
        return $this->headerTitle;
    }

    /**
     * @param null|string $headerTitle
     *
     * @return PageConfig
     */
    public function setHeaderTitle(?string $headerTitle): PageConfig
    {
        $this->headerTitle = $headerTitle;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBeforeRenderCallback(): mixed
    {
        return $this->beforeRenderCallback;
    }

    /**
     * @param mixed $beforeRenderCallback
     *
     * @return PageConfig
     */
    public function setBeforeRenderCallback(mixed $beforeRenderCallback): PageConfig
    {
        $this->beforeRenderCallback = $beforeRenderCallback;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getExpectedControllerClass(): ?string
    {
        return $this->expectedControllerClass;
    }

    /**
     * @param null|string $expectedControllerClass
     *
     * @return PageConfig
     */
    public function setExpectedControllerClass(?string $expectedControllerClass): PageConfig
    {
        $this->expectedControllerClass = $expectedControllerClass;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getEntityKey(): ?string
    {
        return $this->entityKey;
    }

    /**
     * @param null|string $entityKey
     *
     * @return PageConfig
     */
    public function setEntityKey(?string $entityKey): PageConfig
    {
        $this->entityKey = $entityKey;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasContainer(): bool
    {
        return $this->hasContainer;
    }

    /**
     * @param bool $hasContainer
     *
     * @return PageConfig
     */
    public function setHasContainer(bool $hasContainer): PageConfig
    {
        $this->hasContainer = $hasContainer;

        return $this;
    }

    /**
     * @return array
     */
    public function getContainerClass(): array
    {
        return $this->containerClass;
    }

    /**
     * @param array $containerClass
     *
     * @return PageConfig
     */
    public function setContainerClass(array $containerClass): PageConfig
    {
        $this->containerClass = $containerClass;

        return $this;
    }

    public static function create(string $pageKey): self
    {
        return new self($pageKey);
    }
}