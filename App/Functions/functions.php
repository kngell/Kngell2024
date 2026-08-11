<?php

declare(strict_types=1);

function route(string $path): string
{
    $parts = explode('.', $path);
    $url = HOST;
    foreach ($parts as $part) {
        $url .= DS . $part;
    }
    return $url;
}

// helpers.php
function category_add_url(): string
{
    return CategoryLinks::add();
}
function category_edit_url(string|int $id): string
{
    return CategoryLinks::edit($id);
}
function category_delete_url(string|int $id): string
{
    return CategoryLinks::delete($id);
}
function product_edit_url(string|int $id): string
{
    return ProductLinks::edit($id);
}

// {# Clean and obvious #}
// <a href="{{ category_add_url() }}">Add Category</a>
// <a href="{{ category_edit_url(category.id) }}">Edit</a>

// {# Or with a helper #}
// <a href="{{ category_link('edit', category.id) }}">Edit</a>