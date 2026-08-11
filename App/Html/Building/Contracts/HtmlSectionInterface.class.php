<?php

declare(strict_types=1);

interface HtmlSectionInterface
{
    public function getKey(): string;

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent;

    public function setPagination(array $pagination = []): self;

    public function shouldRender(array|Entity $formValues = []): bool;

    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): null|array|AbstractHtmlComponent;

    public function buildForm(): ?AbstractHtmlComponent;

    public function hasform(): bool;

    public function getAction(): ?string;

    public function getSectionsCustomLayout(array $sections): ?AbstractHtmlComponent;
}