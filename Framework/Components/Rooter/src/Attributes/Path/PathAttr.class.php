<?php

declare(strict_types=1);

#[Attribute(Attribute::TARGET_METHOD)]
readonly class PathAttr
{
    public function __construct(public string $path, public HttpMethod $method)
    {
    }
}