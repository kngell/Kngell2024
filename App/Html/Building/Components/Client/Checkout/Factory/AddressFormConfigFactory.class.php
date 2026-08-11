<?php

declare(strict_types=1);

final class AddressFormConfigFactory extends AbstractFormConfigFactory
{
    public function __construct(
        private CheckoutAddressSectionConfigBuilder $sectionBuilder,
    ) {
    }

    #[Override]
    protected function entityDescriptor(): EntityDescriptor
    {
        return new EntityDescriptor(
            key: ModalIdentifier::CHECKOUT_ADDRESS->value,
            displayName: 'Address',
            plural: 'Addresses',
            basePath: '/checkout',
        );
    }

    protected function buildSectionsConfig(): array
    {
        return array_merge(
            $this->getRegularConfig(),
            $this->getMediaConfig(),
        );
    }

    protected function getRegularConfig(): array
    {
        return $this->sectionBuilder->buildRegularConfig();
    }

    protected function getMediaConfig(): array
    {
        return $this->sectionBuilder->buildMediaConfig();
    }

    #[Override]
    protected function getHiddenFields(): array
    {
        $hidden = [
            FormFieldConfig::create(
                name: 'id',
                type: 'hidden',
            ),
            FormFieldConfig::create(
                name: 'address_type',
                type: 'hidden',
            )->setDefaultValue('shipping'),
        ];

        // Add logged_in flag for JS handling
        if ($this->sectionBuilder->isLoggedIn()) {
            $hidden[] = FormFieldConfig::create(
                name: 'is_logged_in',
                type: 'hidden',
            )->setDefaultValue('1');
        }

        return $hidden;
    }

    protected function getFieldHandlers(): array
    {
        return [
            new InputFieldHandler(),
            new NativeSelectFieldHandler(),
        ];
    }

    #[Override]
    protected function getLayoutBuilder(): ?FormLayoutInterface
    {
        return new SimpleFormLayout();
    }

    #[Override]
    protected function isFooterEnabled(): bool
    {
        return false;
    }

    #[Override]
    protected function getStandAloneFooter(): bool
    {
        return true;
    }

    protected function getFieldLayouts(): array
    {
        return [
            'input' => new FieldLayout(),
            'checkbox' => new FieldCheckboxLayout(),
        ];
    }

    protected function getFormContainerClass(): array
    {
        return ['modal-body', 'address-modal__body'];
    }

    #[Override]
    protected function formClass(): array
    {
        return ['address-frm'];
    }

    #[Override]
    protected function formId(): string
    {
        return 'address-frm';
    }

    #[Override]
    protected function formName(): string
    {
        return 'address-frm';
    }

    protected function getEnumClass(): ?string
    {
        return CheckoutAddressSection::class;
    }

    #[Override]
    protected function customAttributes(): array
    {
        return array_merge(
            parent::customAttributes(),
            [
                'data-validate' => 'true',
                'data-validation-rules' => 'addressRules',
                'data-ajax-form' => '',
                'data-is-logged-in' => $this->sectionBuilder->isLoggedIn() ? 'true' : 'false',
            ],
        );
    }

    #[Override]
    protected function submitText(): string
    {
        return 'Save Address';
    }

    #[Override]
    protected function submitIcon(): string
    {
        return 'icon-save';
    }
}