<?php

declare(strict_types=1);

interface HtmlSectionInterface
{
    public function getKey(): string;

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent;

    public function shouldRender(array $formValues = []): bool;

    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): null|array|AbstractHtmlComponent;
}