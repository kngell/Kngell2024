<?php

declare(strict_types=1);

enum HttpMethod : string
{
    case HEAD = 'HEAD';
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';
    case PURGE = 'PURGE';
    case OPTIONS = 'OPTIONS';
    case TRACE = 'TRACE';
    case CONNECT = 'CONNECT';

    public static function fromString(string $method) : self
    {
        foreach (self::cases() as $httpMethod) {
            if ($httpMethod->value === strtoupper($method)) {
                return $httpMethod;
            }
        }
        throw new UnexpectedValueException("Could not match the method $method");
    }
}