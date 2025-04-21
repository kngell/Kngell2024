<?php

declare(strict_types=1);
class CustomHttpMap extends Map
{
    #[Override]
    public function get(string|int $key): mixed
    {
        if (null != $key) {
            if (! isset($this->getAll()[strtoupper($key)])) {
                return '';
            }
            return $this->getAll()[strtoupper($key)];
        }
        return array_map('strip_tags', $this->getAll() ?? []);
    }
}
