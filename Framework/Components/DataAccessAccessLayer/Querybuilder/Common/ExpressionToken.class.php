<?php

declare(strict_types=1);

class ExpressionToken
{
    public function __construct(
        public readonly TokenType $type,
        public readonly string $value,
        public readonly int $parenDepth,
    ) {
    }
}