<?php

declare(strict_types=1);

class FormRegularSection extends BaseRegularSection
{
    protected SectionLayout $layoutType = SectionLayout::LAYOUT_CUSTOM_ROWS;

    public function getKey(): string
    {
        return $this->config->getKey();
    }

    protected function getSectionConfig(array $formValues = []): RegularSectionConfig
    {
        return $this->config;
    }

    protected function getFieldsConfig(array $formValues = []): array
    {
        return $this->config->getFields();
    }

    protected function getRowIndicesConfig(): array
    {
        return $this->config->getRowIndicesConfig();
    }
}