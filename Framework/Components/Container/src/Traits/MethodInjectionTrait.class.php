<?php

declare(strict_types=1);

trait MethodInjectionTrait
{
    /**
     * Invokes a method with dependency injection.
     *
     * @param string $method The method name
     * @param array $parameters Optional parameters to override resolution
     * @return mixed The result of the method call
     */
    protected function invokeWithInjection(string $method, array $parameters = []): mixed
    {
        return MethodInjection::invoke($this, $method, $parameters);
    }
}