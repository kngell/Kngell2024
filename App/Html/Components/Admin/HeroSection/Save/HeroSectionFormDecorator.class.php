<?php

declare(strict_types=1);

class HeroSectionFormDecorator extends AbstractFormDecorator
{
    protected const array HEADER_BTN_CONFIG = [
        [
            'type' => 'submit',
            'label' => 'Delete',
            'action' => '/hero-section-delete/confirm',
            'formName' => 'hero_delete_form',
            'requiresEditMode' => true,
            'requiresEntityId' => true,
            'size' => 'md-compact',
            'ariaLabel' => 'Delete',
            'style' => 'danger',
            'icon' => 'icon-trash',
            'iconPosition' => 'left',
            'attributes' => [],
            'class' => [],
        ],
        [
            'type' => 'submit',
            'label' => 'Add New',
            'action' => '/hero-page/add',
            'formName' => 'hero_add_form',
            'requiresEditMode' => false,
            'requiresEntityId' => false,
            'size' => 'md-compact',
            'ariaLabel' => 'Add New',
            'style' => 'primary',
            'icon' => 'icon-plus',
            'iconPosition' => 'left',
            'attributes' => [],
            'class' => [],
        ],
    ];

    protected const array BREADCRUMBS_LINKS = [
        'Dashboard',
        'Pages',
        'Hero Section',
    ];

    protected function getFormKey(): string
    {
        return 'heroSectionForm';
    }

    protected function getHeaderKey(): ?string
    {
        return 'heroHeader';
    }

    protected function headerTitle(): ?string
    {
        return 'Hero Section Manager';
    }
}