<?php

declare(strict_types=1);

$text = [
    'type' => 'text',
    'name' => 'username',
    'label' => 'Username',
    'value' => 'john_doe',
    'required' => true,
    'maxlength' => 20,
    'counter' => '0/20',
    'leftIcon' => ['icon' => 'icon-user', 'aria' => 'User icon'],
    'rightIcon' => ['icon' => 'icon-check', 'aria' => 'Valid'],
    'footer' => [
        'hint' => 'Choose a unique username',
        'counter' => '0/20',
        'error' => 'Username is required',
    ],
];

$select = [
    'type' => 'select',
    'name' => 'category',
    'label' => 'Category',
    'placeholder' => 'Select a category',
    'options' => [
        '1' => 'Electronics',
        '2' => 'Clothing',
        '3' => 'Books',
    ],
    'value' => '2',
    'required' => true,
    'counter' => '3 options',
    'leftIcon' => ['icon' => 'icon-tag', 'aria' => 'Category icon'],
    'rightIcon' => ['icon' => 'icon-arrow-down', 'aria' => 'Dropdown arrow'],
    'footer' => [
        'hint' => 'Choose a product category',
        'counter' => '1/3',
        'error' => 'Please select a category',
    ],
];
$minimal = [
    'type' => 'text',
    'name' => 'email',
    'label' => 'Email',
];
$minimal_select = [
    'type' => 'select',
    'name' => 'country',
    'label' => 'Country',
    'options' => ['FR' => 'France', 'BE' => 'Belgium'],
];

$customSelect = [
    'type' => 'custom-select',
    'name' => 'product_id',
    'label' => 'Product',
    'placeholder' => 'Search Product by name or Sku...',
    'searchPlaceholder' => 'Search products...',
    'searchable' => true,
    'options' => [
        '1' => 'Product 1 (SKU-001)',
        '2' => 'Product 2 (SKU-002)',
        '3' => 'Product 3 (SKU-003)',
    ],
    'value' => '2',
    'required' => true,
    'wrapperClass' => ['custom-product-select'],
    'leftIcon' => ['icon' => 'icon-box', 'aria' => 'Product icon'],
    'rightIcon' => ['icon' => 'icon-arrow-down', 'aria' => 'Dropdown arrow'],
    'searchIcon' => ['icon' => 'icon-search', 'aria' => 'Search products'],
    'counter' => '3 products',
    'footer' => [
        'hint' => 'Search and select a product',
        'counter' => '1/3',
        'error' => 'Please select a product',
    ],
];