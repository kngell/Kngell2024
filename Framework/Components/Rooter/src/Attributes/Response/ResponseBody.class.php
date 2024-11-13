<?php

declare(strict_types=1);

#[Attribute(Attribute::TARGET_METHOD)]
readonly class ResponseBody
{
    public function __construct(public ResponseBodyType $type, public string|null $produces = null)
    {
    }
}