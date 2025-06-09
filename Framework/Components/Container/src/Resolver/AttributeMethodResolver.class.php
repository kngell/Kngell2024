<?php

declare(strict_types=1);

use App;
use Exception;
use Framework\Components\Container\Attributes\Inject;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * Resolves dependencies using attribute-based method injection.
 */
class AttributeMethodResolver
{
    /**
     * Resolves a method with attribute-based dependency injection.
     *
     * @param object $instance The object instance
     * @param string $method The method name
     * @param array $parameters Optional parameters to override resolution
     * @return mixed The result of the method call
     */
    public static function resolveMethod(object $instance, string $method, array $parameters = []): mixed
    {
        $container = App::getInstance();
        $reflectionMethod = new ReflectionMethod($instance, $method);

        // Check if method has Inject attribute
        $attributes = $reflectionMethod->getAttributes(Inject::class);
        if (empty($attributes)) {
            // No Inject attribute, just call the method with provided parameters
            return $reflectionMethod->invokeArgs($instance, $parameters);
        }

        // Method has Inject attribute, resolve dependencies
        $args = [];
        $reflectionParams = $reflectionMethod->getParameters();

        foreach ($reflectionParams as $param) {
            $paramName = $param->getName();

            // If parameter was explicitly provided, use it
            if (isset($parameters[$paramName])) {
                $args[] = $parameters[$paramName];
                continue;
            }

            // Check for parameter-level Inject attribute
            $paramAttributes = $param->getAttributes(Inject::class);
            if (! empty($paramAttributes)) {
                $injectAttr = $paramAttributes[0]->newInstance();
                $bindingId = $injectAttr->getId();

                if ($bindingId !== null) {
                    // Use the binding ID from the attribute
                    $args[] = $container->get($bindingId);
                    continue;
                }
            }

            // Try to resolve by type
            $type = $param->getType();
            if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                $typeName = $type->getName();

                // For User type, check for 'current.user' binding first
                if ($typeName === 'User' || $typeName === '\User') {
                    $args[] = $container->get('current.user');
                    continue;
                }

                // Otherwise resolve by type
                $args[] = $container->get($typeName);
                continue;
            }

            // If we couldn't resolve and parameter is optional, use default value
            if ($param->isOptional()) {
                $args[] = $param->getDefaultValue();
                continue;
            }

            // If we get here, we couldn't resolve the parameter
            throw new Exception("Cannot resolve parameter '$paramName' for method '$method'");
        }

        return $reflectionMethod->invokeArgs($instance, $args);
    }
}