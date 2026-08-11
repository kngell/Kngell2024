<?php

declare(strict_types=1);

abstract class AbstractRegularPageConfigFactory
{
    protected ?string $expectedController = null;

    public function __construct(
        protected IconBuilder $iconBuilder,
    ) {
    }

    public function createPageConfig(): RegularPageConfig
    {
        return RegularPageConfig::create($this->getEnumClass())
            ->setSections($this->getSections())
            ->setAssets($this->getAssets())
            ->setExpectedControllerClass($this->getExpectedControllerClass());
    }

    abstract public function getEnumClass(): string;

    abstract public function getAssets(): array;

    abstract public function getExpectedControllerClass(): ?string;

    public function getSections(): array
    {
        return $this->buildSections();
    }

    /**
     * @param null|string $expectedController
     *
     * @return AbstractRegularPageConfigFactory
     */
    public function expectedController(?string $expectedController): AbstractRegularPageConfigFactory
    {
        $this->expectedController = $expectedController;

        return $this;
    }

    protected function buildSections(): array
    {
        return [];
    }
}