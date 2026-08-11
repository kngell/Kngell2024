<?php

declare(strict_types=1);

final class BadgeCellConfig
{
    public function __construct(
        public readonly array $badgeClasses = ['badge', 'badge--warning'],
    ) {
    }
}