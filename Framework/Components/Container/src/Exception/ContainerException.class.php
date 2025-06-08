<?php

declare(strict_types=1);

/**
 * Base container exception.
 */
class ContainerException extends Exception
{
    public static function bindingNotFound(string $abstract): self
    {
        return new self("No binding found for [{$abstract}]");
    }

    public static function circularDependency(string $abstract, array $stack): self
    {
        $stackTrace = implode(' -> ', $stack) . ' -> ' . $abstract;
        return new self("Circular dependency detected: {$stackTrace}");
    }

    public static function cannotResolve(string $abstract, string $reason): self
    {
        return new self("Cannot resolve [{$abstract}]: {$reason}");
    }

    public static function invalidBinding(string $abstract, string $reason): self
    {
        return new self("Invalid binding for [{$abstract}]: {$reason}");
    }
}