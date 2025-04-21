<?php

declare(strict_types=1);
class QueryHttpMap extends Map
{
    #[Override]
    public function get(string|int $key): mixed
    {
        if (null != $key) {
            if (! isset($this->getAll()[strtolower($key)])) {
                return '';
            }
            return $this->getAll()[strtolower($key)];
        }
        return array_map('strip_tags', $this->getAll() ?? []);
    }

    public function getRouteParameters() : array
    {
        return explode('/', $this->get('url'));
    }
}
