<?php

declare(strict_types=1);

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;

final readonly class RouteArgumentGenerator
{
    private const PARAMETER_ALIASES = [
        'id' => ['public_id', 'product_id', 'item_id', 'uuid', 'guid', 'key', 'record_id'],
        'slug' => ['name', 'title', 'permalink', 'url', 'path', 'seo_name'],
        'page' => ['p', 'page_num', 'currentPage', 'current_page', 'pg', 'page_number'],
        'limit' => ['per_page', 'size', 'page_size', 'results_per_page', 'take', 'count'],
        'offset' => ['skip', 'start', 'start_index'],
        'token' => ['access_token', 'auth_token', 'bearer_token', 'api_token'],
        'email' => ['username', 'login', 'user_email', 'email_address'],
        'sort' => ['order_by', 'sort_by', 'order'],
        'direction' => ['order_direction', 'sort_direction', 'dir'],
        'search' => ['q', 'query', 'filter', 'keyword'],
        'category' => ['cat', 'category_id', 'cat_id'],
        'status' => ['state', 'active', 'enabled'],
    ];

    private Serializer $serialiser;

    public function __construct()
    {
        $this->serialiser = SerializerBuilder::create()->build();
    }

    public function generate(RouteInfo $route, Request $request): array
    {
        $args = [];
        $urlRouteParams = $route->getRouteParams();
        $queryParams = $request->getQuery()->getAll();

        $allParams = array_merge($queryParams, $urlRouteParams);

        /** @var RouteArguments $argument */
        foreach ($route->getArguments() as $argument) {
            $paramName = $argument->getParameterName();

            $value = $this->resolveArgumentValue($argument, $allParams, $request);

            $args[$paramName] = $this->handleResolvedValue($value, $argument);
        }

        $this->validatePathVariable($route, $urlRouteParams);
        return $args;
    }

    private function resolveArgumentValue(RouteArguments $argument, array $params, Request $request): mixed
    {
        $paramName = $argument->getParameterName();

        // 1. Check direct match
        if (array_key_exists($paramName, $params)) {
            return $this->castValue($params[$paramName], $argument->getReflectionType());
        }

        // 2. Check aliases from RouteArguments
        foreach ($argument->getAliases() as $alias) {
            if (array_key_exists($alias, $params)) {
                return $this->castValue($params[$alias], $argument->getReflectionType());
            }
        }

        // 3. Check request body
        $bodyValue = $this->getFromRequestBody($argument, $request);
        if ($bodyValue !== null) {
            return $bodyValue;
        }

        return null;
    }

    private function handleResolvedValue(mixed $value, RouteArguments $argument): mixed
    {
        if ($value !== null) {
            return $value;
        }
        if ($argument->isDefaultValue()) {
            return $argument->getDefaultValue();
        }

        if ($argument->isNullable()) {
            return null;
        }
        $aliases = self::PARAMETER_ALIASES[$argument->getParameterName()] ?? [];
        $aliasHint = $aliases ? ' or query as: ' . implode(', ', $aliases) : '';

        throw new MissingArgumentException(
            "Missing required parameter: {$argument->getParameterName()}. " .
            "Expected in path as '{$argument->getParameterName()}'{$aliasHint}.",
        );
    }

    private function getFromRequestBody(RouteArguments $argument, Request $request): mixed
    {
        $paramName = $argument->getParameterName();
        $postData = $request->getPost()->getAll();

        if (array_key_exists($paramName, $postData)) {
            return $this->castValue($postData[$paramName], $argument->getReflectionType());
        }

        $aliases = self::PARAMETER_ALIASES[$paramName] ?? [];
        foreach ($aliases as $alias) {
            if (array_key_exists($alias, $postData)) {
                return $this->castValue($postData[$alias], $argument->getReflectionType());
            }
        }

        if ($this->isComplexType($argument->getReflectionType())) {
            return $this->createRequestBodyArgs($argument, $request);
        }

        return null;
    }

    private function isComplexType(ReflectionNamedType $type): bool
    {
        $typeName = $type->getName();
        $simpleTypes = ['string', 'int', 'bool', 'float', 'array', 'mixed', 'object'];

        return !in_array($typeName, $simpleTypes) && class_exists($typeName);
    }

    private function castValue(mixed $value, ReflectionNamedType $type): mixed
    {
        if ($value === null) {
            return null;
        }

        $typeName = $type->getName();

        switch ($typeName) {
            case 'int':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'string':
                return (string) $value;
            case 'array':
                if (is_string($value)) {
                    if (str_contains($value, ',')) {
                        return explode(',', $value);
                    }
                    if (json_validate($value)) {
                        return json_decode($value, true);
                    }
                }
                return is_array($value) ? $value : [$value];
            default:
                if (class_exists($typeName) && is_scalar($value)) {
                    try {
                        return new $typeName($value);
                    } catch (Throwable) {
                        return $value;
                    }
                }
                return $value;
        }
    }

    private function validatePathVariable(RouteInfo $route, array $urlRouteParams): void
    {
        /** @var PathElement $path */
        foreach ($route->getPath() as $path) {
            if ($path->getType()->name === 'VARIABLE') {
                if (array_key_exists($path->getValue(), $urlRouteParams)) {
                    unset($urlRouteParams[$path->getValue()]);
                }
            }
        }

        // List of ALL route metadata parameters that should be ignored
        $metadataParams = [
            'path',           // The route pattern itself
            'controller',     // Controller name
            'method',        // Method name
            'httpMethod',    // HTTP method (GET, POST, etc.)
            'middleware',    // Middleware array
            'responseBody',   // Response body config
            'ResponseStatus', // HTTP status
            'arguments',     // Generic arguments
            '_group',        // Route group config
            'prefix',        // Group prefix
            'constraints',   // Parameter constraints
            'parameterMapping', // Parameter mapping config
            'parameterAliases', // Parameter aliases config
        ];

        // Filter out metadata parameters
        $extraParams = array_filter($urlRouteParams, function ($key) use ($metadataParams) {
            return !in_array($key, $metadataParams, true);
        }, ARRAY_FILTER_USE_KEY);

        if (!empty($extraParams)) {
            throw new InvalidRouteArgumentException(
                sprintf(
                    'URL contains extra parameters that don\'t match method arguments: %s',
                    implode(', ', array_keys($extraParams)),
                ),
            );
        }
    }

    private function createRequestBodyArgs(RouteArguments $argument, Request $request): mixed
    {
        if ($request->hasXmlBody()) {
            return $this->serialiser->deserialize(
                $request->getRawContent(),
                $argument->getReflectionType()->getName(),
                'xml',
            );
        }

        if ($request->hasJsonBody()) {
            return $this->serialiser->deserialize(
                $request->getRawContent(),
                $argument->getReflectionType()->getName(),
                'json',
            );
        }

        if ($request->hasFormUrlEncodedBody() || $request->hasFormDataBody()) {
            $json = json_encode($request->getPost()->getAll());
            return $this->serialiser->deserialize(
                $json,
                $argument->getReflectionType()->getName(),
                'json',
            );
        }

        return null;
    }
}