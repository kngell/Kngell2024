<?php

declare(strict_types=1);
class CustomHttpMap extends Map
{
    #[Override]
    public function get(string|int|null $key = null): mixed
    {
        if (null != $key) {
            if (isset($this->getAll()[strtoupper($key)])) {
                return $this->getAll()[strtoupper($key)];
            }
            if (isset($this->getAll()[strtolower($key)])) {
                return $this->getAll()[strtolower($key)];
            }
            return false;
        }
        return array_map('strip_tags', $this->getAll() ?? []);
    }
}