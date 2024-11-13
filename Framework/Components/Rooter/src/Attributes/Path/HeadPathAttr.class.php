<?php

declare(strict_types=1);

#[Attribute(Attribute::TARGET_METHOD)]
readonly class HeadPathAttr extends PathAttr
{
    public function __construct(string $path)
    {
        parent::__construct($path, HttpMethod::HEAD);
    }
}