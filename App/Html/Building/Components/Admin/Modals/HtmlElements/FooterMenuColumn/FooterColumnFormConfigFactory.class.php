<?php

declare(strict_types=1);

final class FooterColumnFormConfigFactory extends AbstractFooterFormConfigFactory
{
    public function __construct(
        private FooterColumnSectionConfigBuilder $sectionConfig,
    ) {
    }

    public function headerTitle(): string
    {
        return 'Footer Column Manager';
    }

    // ─── Required implementations ─────────────────────────────

    protected function buildSectionsConfig(): array
    {
        return $this->sectionConfig->buildRegularConfig();
    }

    protected function entityDescriptor(): EntityDescriptor
    {
        return new EntityDescriptor(
            key: ModalIdentifier::FOOTER_MENU_COLUMN->value,
            displayName: ModalIdentifier::FOOTER_MENU_COLUMN->getLabel(),
            plural: ModalIdentifier::FOOTER_MENU_COLUMN->getPlural(),
            basePath: '/admin/footer-page',
        );
    }

    protected function getValidationRules(): string
    {
        return 'footerColumnRules';
    }

    protected function getFooterContainerClass(): array
    {
        return ['footer-column__body'];
    }

    // ─── Form identification ──────────────────────────────────

    protected function formId(): string
    {
        return 'footer-column-frm';
    }

    protected function formName(): string
    {
        return 'footer-column-frm';
    }

    protected function formClass(): array
    {
        return ['footer-column-frm'];
    }

    // ─── Footer-specific configuration ────────────────────────

    protected function getEnumClass(): ?string
    {
        return FooterColumnSectionKeys::class;
    }

    protected function submitText(): string
    {
        return 'Save Column';
    }

    protected function submitIcon(): string
    {
        return 'icon-save';
    }
}