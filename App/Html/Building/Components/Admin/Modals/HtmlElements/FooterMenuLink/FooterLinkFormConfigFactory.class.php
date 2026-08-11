<?php

declare(strict_types=1);

final class FooterLinkFormConfigFactory extends AbstractFooterFormConfigFactory
{
    public function __construct(
        private FooterLinkSectionConfigBuilder $sectionConfig,
    ) {
    }

    public function headerTitle(): string
    {
        return 'Footer Link Manager';
    }

    // ─── Required implementations ─────────────────────────────

    protected function buildSectionsConfig(): array
    {
        return $this->sectionConfig->buildRegularConfig();
    }

    protected function entityDescriptor(): EntityDescriptor
    {
        return new EntityDescriptor(
            key: ModalIdentifier::FOOTER_MENU_LINK->value,
            displayName: ModalIdentifier::FOOTER_MENU_LINK->getLabel(),
            plural: ModalIdentifier::FOOTER_MENU_LINK->getPlural(),
            basePath: '/admin/footer-page',
        );
    }

    protected function getHiddenFields(): array
    {
        return [
            FormFieldConfig::create(
                name: 'id',
                type: 'hidden',
            ),
            FormFieldConfig::create(
                name: 'column_id',
                type: 'hidden',
            ),
        ];
    }

    protected function getValidationRules(): string
    {
        return 'footerLinkRules';
    }

    protected function getFooterContainerClass(): array
    {
        return ['footer-link__body'];
    }

    // ─── Form identification ──────────────────────────────────

    protected function formId(): string
    {
        return 'footer-link-frm';
    }

    protected function formName(): string
    {
        return 'footer-link-frm';
    }

    protected function formClass(): array
    {
        return ['footer-link-frm'];
    }

    // ─── Footer-specific configuration ────────────────────────

    protected function getEnumClass(): ?string
    {
        return FooterLinkSectionKeys::class;
    }

    protected function getFieldHandlers(): array
    {
        $handlers = parent::getFieldHandlers();
        return array_merge(
            $handlers,
            [
                new CustomSelectFieldHandler(),
                new NativeSelectFieldHandler(),
            ],
        );
    }

    protected function submitText(): string
    {
        return 'Save Link';
    }

    protected function submitIcon(): string
    {
        return 'icon-save-link';
    }
}