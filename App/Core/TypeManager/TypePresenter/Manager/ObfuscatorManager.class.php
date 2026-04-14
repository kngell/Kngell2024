<?php

// ObfuscatorManager.php
declare(strict_types=1);

final class ObfuscatorManager
{
    private array $strategies = [];
    private string $defaultStrategy;
    private ObfuscatorFactory $factory;

    public function __construct(ObfuscatorFactory $factory, string $defaultStrategy = 'hashid')
    {
        $this->factory = $factory;
        $this->defaultStrategy = $defaultStrategy;
    }

    public function strategy(?string $name = null): ObfuscatorInterface
    {
        $name = $name ?? $this->defaultStrategy;

        if (!isset($this->strategies[$name])) {
            // Lazy-load through factory
            $this->strategies[$name] = $this->factory->create($name);
        }

        return $this->strategies[$name];
    }

    public function current(): ObfuscatorInterface
    {
        return $this->strategy($this->defaultStrategy);
    }

    public function hasStrategy(string $name): bool
    {
        return in_array($name, ['hashid', 'encrypt']); // Add more as needed
    }
}