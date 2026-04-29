<?php

declare(strict_types=1);
class CustomHttpMap extends Map
{
    #[Override]
    public function get(string|int|null $key = null, mixed $default = null): mixed
    {
        if (null != $key) {
            if (isset($this->getAll()[strtoupper($key)])) {
                return $this->getAll()[strtoupper($key)];
            }
            if (isset($this->getAll()[strtolower($key)])) {
                return $this->getAll()[strtolower($key)];
            }
            return $default; // Return the default value, not null
        }

        $data = $this->getAll() ?? [];
        array_walk_recursive($data, function (&$value) {
            if (is_string($value)) {
                $value = strip_tags($value);
            }
        });

        return $data;
    }
}