<?php

declare(strict_types=1);

function route(string $path) : string
{
    $parts = explode('.', $path);
    $url = HOST;
    foreach ($parts as $part) {
        $url .= DS . $part;
    }
    return $url;
}