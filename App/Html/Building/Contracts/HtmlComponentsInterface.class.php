<?php

declare(strict_types=1);
interface HtmlComponentsInterface
{
    public function buildLayout(HtmlBuilder $html): array;
}