<?php

declare(strict_types=1);

final class FooterSocialFormConfigFactory extends AbstractFooterFormConfigFactory
{
    public function __construct(
        private FooterSocialSectionConfigBuilder $sectionConfig,
    ) {
    }

    public function headerTitle(): string
    {
        return 'Footer Social Links Manager';
    }

    // ─── Required implementations ─────────────────────────────

    protected function buildSectionsConfig(): array
    {
        return $this->sectionConfig->buildRegularConfig();
    }

    protected function entityDescriptor(): EntityDescriptor
    {
        return new EntityDescriptor(
            key: ModalIdentifier::FOOTER_SOCIALS->value,
            displayName: ModalIdentifier::FOOTER_SOCIALS->getLabel(),
            plural: ModalIdentifier::FOOTER_SOCIALS->getPlural(),
            basePath: '/admin/footer-page',
        );
    }

    protected function getValidationRules(): string
    {
        return 'footerSocialRules';
    }

    protected function getFooterContainerClass(): array
    {
        return ['footer-social__body'];
    }

    // ─── Form identification ──────────────────────────────────

    protected function formId(): string
    {
        return 'footer-social-frm';
    }

    protected function formName(): string
    {
        return 'footer-social-frm';
    }

    protected function formClass(): array
    {
        return ['footer-social-frm'];
    }

    // ─── Footer-specific configuration ────────────────────────

    protected function getEnumClass(): ?string
    {
        return FooterSocialsSectionKeys::class;
    }

    protected function submitText(): string
    {
        return 'Save Link';
    }

    protected function submitIcon(): string
    {
        return 'icon-save';
    }
}