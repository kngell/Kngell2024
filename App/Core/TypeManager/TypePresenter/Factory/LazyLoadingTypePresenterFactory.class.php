<?php

declare(strict_types=1);

class LazyLoadingTypePresenterFactory implements TypePresenterFactoryInterface
{
    private ?TypePresenterFactory $realFactory = null;
    private bool $isInitialized = false;

    public function __construct(
        private Closure $factoryCreator,
    ) {
    }

    public function getPresenterForValue(mixed $value, ?ReflectionProperty $property = null): TypePresenterInterface
    {
        return $this->getRealFactory()->getPresenterForValue($value, $property);
    }

    public function getPresenterForType(string $type): ?TypePresenterInterface
    {
        return $this->getRealFactory()->getPresenterForType($type);
    }

    public function displayValue(mixed $value, ?ReflectionProperty $property = null): mixed
    {
        return $this->getRealFactory()->displayValue($value, $property);
    }

    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }

    public function initialize(): void
    {
        if (!$this->isInitialized) {
            $this->getRealFactory();
        }
    }

    private function getRealFactory(): TypePresenterFactory
    {
        if (!$this->isInitialized) {
            $this->realFactory = ($this->factoryCreator)();
            $this->isInitialized = true;
        }

        if ($this->realFactory === null) {
            throw new RuntimeException('TypePresenterFactory could not be initialized');
        }

        return $this->realFactory;
    }
}