<?php

declare(strict_types=1);

interface SectionProviderInterface
{
    public function registerSections(HtmlBuilder $html, ?HtmlSectionManagerInterface $manager = null): void;
}