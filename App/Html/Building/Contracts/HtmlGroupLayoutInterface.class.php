<?php

declare(strict_types=1);

interface HtmlGroupLayoutInterface
{
    public function renderGroupLayout(array $groupElements, string $wrapperClass): null|array|AbstractHtmlComponent;
}