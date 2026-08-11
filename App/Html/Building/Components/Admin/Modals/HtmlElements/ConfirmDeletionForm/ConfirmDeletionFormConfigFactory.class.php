<?php

declare(strict_types=1);

final class ConfirmDeletionFormConfigFactory extends AbstractFormConfigFactory
{
    #[Override]
    protected function entityDescriptor(): EntityDescriptor
    {
        return new EntityDescriptor(
            key: ModalIdentifier::CONFIRM_DELETION->value,
            displayName: ModalIdentifier::CONFIRM_DELETION->getLabel(),
            plural: ModalIdentifier::CONFIRM_DELETION->getPlural(),
            basePath: '/admin/confirm-deletion',
        );
    }

    protected function buildSections(): array
    {
        return [
            DeletionCheckBoxSection::class,
            DeletionImpactSection::class,
            DeletionOptionSection::class,
            DeletionSummarySection::class,
        ];
    }

    protected function getFieldHandlers(): array
    {
        return [
            new InputBoxHandler(),
        ];
    }

    #[Override]
    protected function getLayoutBuilder(): ?FormLayoutInterface
    {
        return new SimpleFormLayout();
    }

    protected function getFormSectionEnumClass(): ?string
    {
        return ConfirmDeletionSection::class;
    }

    #[Override]
    protected function isFooterEnabled(): bool
    {
        return false;
    }

    protected function getStandAloneFooter(): bool
    {
        return true;
    }

    #[Override]
    protected function customAttributes(): array
    {
        return [
            'data-validate' => 'true',
            'data-validation-rules' => 'confirmDeletionRules',
            'data-ajax-form' => '',
        ];
    }

    #[Override]
    protected function getHiddenFields(): array
    {
        return [
            FormFieldConfig::create(
                name: 'id',
                type: 'hidden',
            )->setMap('id[value'),
        ];
    }

    protected function getFieldLayouts(): array
    {
        return [
            'input' => new InputBoxLayout(),
        ];
    }

    protected function getFormContainerClass(): array
    {
        return ['modal-body', 'confirm-deletion__body'];
    }

    #[Override]
    protected function formClass(): array
    {
        return ['confirm-deletion-frm'];
    }

    #[Override]
    protected function formId(): string
    {
        return 'confirm-deletion-frm';
    }

    #[Override]
    protected function formName(): string
    {
        return 'confirm-deletion-frm';
    }

    protected function getEnumClass(): ?string
    {
        return ConfirmDeletionSection::class;
    }
}