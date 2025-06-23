<?php

declare(strict_types=1);

#[Attribute(Attribute::TARGET_METHOD)]
readonly class ResponseStatus
{
    public function __construct(public HttpStatusCode $statusCode)
    {
    }
}