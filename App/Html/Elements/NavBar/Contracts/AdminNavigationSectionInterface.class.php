<?php

declare(strict_types=1);

interface AdminNavigationSectionInterface
{
    public function getSection(): AbstractHtmlComponent;
}