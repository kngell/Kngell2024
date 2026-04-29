<?php

declare(strict_types=1);

class ProductFormDecorator extends AbstractFormDecorator
{
    protected const array HEADER_BTN_CONFIG = [
        [
            'type' => 'submit',
            'label' => 'Delete',
            'action' => '/product-confirm-deletion/confirm',
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
            'action' => '/admin/product-add',
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
        'Products',
        'Add Product',
    ];

    protected function getFormKey(): string
    {
        return 'productForm';
    }

    protected function getHeaderKey(): ?string
    {
        return 'productMainHeader';
    }

    protected function headerTitle(): ?string
    {
        return 'Add Product';
    }
}