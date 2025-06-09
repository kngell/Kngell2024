<?php

declare(strict_types=1);

use AttributeMethodResolver;

/**
 * Utility class for method injection.
 */
class MethodInjection
{
    /**
     * Invokes a method with dependency injection.
     *
     * @param object $instance The object instance
     * @param string $method The method name
     * @param array $parameters Optional parameters to override resolution
     * @return mixed The result of the method call
     */
    public static function invoke(object $instance, string $method, array $parameters = []): mixed
    {
        return AttributeMethodResolver::resolveMethod($instance, $method, $parameters);
    }
}