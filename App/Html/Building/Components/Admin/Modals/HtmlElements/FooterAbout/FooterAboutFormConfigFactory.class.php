<?php

declare(strict_types=1);

final class FooterAboutFormConfigFactory extends AbstractFooterFormConfigFactory
{
    public function __construct(
        private FooterAboutSectionConfigBuilder $sections,
    ) {
    }

    public function headerTitle(): string
    {
        return 'Footer About Manager';
    }

    protected function entityDescriptor(): EntityDescriptor
    {
        return new EntityDescriptor(
            key: ModalIdentifier::FOOTER_ABOUT->value,
            displayName: ModalIdentifier::FOOTER_ABOUT->getLabel(),
            plural: ModalIdentifier::FOOTER_ABOUT->getPlural(),
            basePath: '/admin/footer-about',
        );
    }

    protected function getValidationRules(): string
    {
        return 'footerAboutRules';
    }

    protected function getFooterContainerClass(): array
    {
        return ['footer-about__body'];
    }

    protected function showFormHeader(): bool
    {
        return false;
    }
    // ─── Form identification ──────────────────────────────────

    protected function formId(): string
    {
        return 'footer-about-frm-id';
    }

    protected function formName(): string
    {
        return 'footer-about-frm';
    }

    protected function formClass(): array
    {
        return ['footer-about-frm'];
    }

    // ─── Footer-specific configuration ────────────────────────

    protected function buildSectionsConfig(): array
    {
        return $this->sections->buildRegularConfig();
    }

    protected function getEnumClass(): ?string
    {
        return AboutSectionKeys::class;
    }

    protected function getFieldHandlers(): array
    {
        $handlers = parent::getFieldHandlers();
        return array_merge(
            $handlers,
            [
                new TextareaFieldHandler(),
            ],
        );
    }

    protected function getLayoutBuilder(): ?FormLayoutInterface
    {
        return new TwoColumnsFormLayout(
            leftSections: [AboutSectionKeys::BASICS->value],
            leftColumnClass: ['column'],
            rightColumnClass: ['column'],
        );
    }

    protected function submitText(): string
    {
        return 'Save About Section';
    }

    protected function submitIcon(): string
    {
        return 'icon-save-link';
    }
}