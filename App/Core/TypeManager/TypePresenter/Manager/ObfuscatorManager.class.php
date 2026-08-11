<?php

declare(strict_types=1);

final class ObfuscatorManager
{
    /** @var array<string, ObfuscatorInterface> */
    private array $strategies = [];

    private string $defaultStrategy;

    public function __construct(
        private readonly ObfuscatorFactory $factory,
        ?string $defaultStrategy = null,
    ) {
        $this->defaultStrategy = $defaultStrategy
            ?? ObfuscatorConfig::getConfig()['default']
            ?? 'hashid';
    }

    public function strategy(?string $name = null): ObfuscatorInterface
    {
        $name = $name ?? $this->defaultStrategy;

        if (!isset($this->strategies[$name])) {
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
        $config = ObfuscatorConfig::getConfig();
        return isset($config[$name]) && ($config[$name]['enabled'] ?? false);
    }

    public function deobfuscate(mixed $value): mixed
    {
        if (!is_string($value) || !ObfuscationUtils::isObfuscated($value)) {
            return $value;
        }
        $strategyName = ObfuscationUtils::getStrategyFromPrefix($value);
        $cleanToken = ObfuscationUtils::stripPrefix($value);
        $rawId = $this->strategy($strategyName)->deobfuscate($cleanToken);

        if ($rawId === null) {
            return $value;
        }
        return is_numeric($rawId) ? (int) $rawId : $rawId;
    }

    /**
     * @param int $value The raw ID to obfuscate
     * @param string|null $strategy The strategy to use, or null to use default
     *
     * @return string The obfuscated value with prefix
     */
    public function obfuscate(int $value, ?string $strategy = null): string
    {
        $strategy = $strategy ?? $this->defaultStrategy;
        $obfuscated = $this->strategy($strategy)->obfuscate($value);

        return ObfuscatorConfig::addPrefix($obfuscated, $strategy);
    }

    /**
     * Obfuscate a value preserving the format of the original value.
     *
     * @param int $value The raw ID to obfuscate
     * @param string $originalValue The original obfuscated value to detect format from
     *
     * @return string The obfuscated value with the same format as original
     */
    public function obfuscatePreservingFormat(int $value, string $originalValue): string
    {
        $hasColon = str_contains($originalValue, ':');

        $strategy = ObfuscationUtils::getStrategyFromPrefix($originalValue);

        return $this->obfuscate($value, $strategy);
    }

    public function isObfuscated(mixed $value): bool
    {
        return ObfuscationUtils::isObfuscated($value);
    }
}