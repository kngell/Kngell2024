<?php

declare(strict_types=1);

interface StandAloneComponentInterface
{
    public function build(mixed $name = null): ?AbstractHtmlComponent;
}