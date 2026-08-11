<?php

declare(strict_types=1);

trait ObfuscatorTrait
{
    private ObfuscatorInterface $strategy;

    private function applyPrefixSuffix(string $value, ?DisplayFormat $format): string
    {
        if ($format === null) {
            return $value;
        }

        $result = $value;
        if ($format->prefix !== null) {
            $result = $format->prefix . $result;
        }
        if ($format->suffix !== null) {
            $result = $result . $format->suffix;
        }

        return $result;
    }

    private function getStrategy(?string $strategyName = null): ObfuscatorInterface
    {
        if ($strategyName !== null) {
            return (new ObfuscatorManager(new ObfuscatorFactory()))->strategy($strategyName);
        }

        if (!isset($this->strategy)) {
            $this->strategy = (new ObfuscatorManager(new ObfuscatorFactory()))->strategy('hashid');
        }

        return $this->strategy;
    }

    private function cleanValue(string $value, Entity $entity): string
    {
        $format = $entity->getFormat();
        $prefix = $format ? $format->prefix : null;
        $cleanValue = $prefix !== null ? ltrim($value, $prefix) : $value;

        $deobfuscated = $this->getStrategy()->deobfuscate($cleanValue);

        return $deobfuscated ?? $value;
    }
}