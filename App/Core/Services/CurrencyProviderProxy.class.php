<?php

declare(strict_types=1);

final class CurrencyProviderProxy implements CurrencyCodeProviderInterface
{
    private ?CurrencyCodeProviderInterface $instance = null;
    private Closure $factory;

    public function __construct(callable $factory)
    {
        $this->factory = Closure::fromCallable($factory);
    }

    public function getCurrencyCode(int $currencyId): string
    {
        return $this->getInstance()->getCurrencyCode($currencyId);
    }

    public function getSystemDefaultCurrencyCode(): string
    {
        return $this->getInstance()->getSystemDefaultCurrencyCode();
    }

    private function getInstance(): CurrencyCodeProviderInterface
    {
        if ($this->instance === null) {
            $this->instance = ($this->factory)();
        }
        return $this->instance;
    }
}