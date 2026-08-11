<?php

declare(strict_types=1);

interface HtmlTemplatePathInterface
{
    public function getTemplate(string $fileName): ?string;
}