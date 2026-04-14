<?php

declare(strict_types=1);
#[Attribute]
class BannerLabel
{
    public function __construct(
        public string $label,
    ) {
    }
}