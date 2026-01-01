<?php

declare(strict_types=1);

interface ContentSourceInterface
{
    public function getContent(): string;
}