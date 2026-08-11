<?php

declare(strict_types=1);
enum CategoryLinks: string
{
    public function withId(int|string $id): string
    {
        return str_replace('{id}', (string) $id, $this->value);
    }

    public function value(): string
    {
        return $this->value;
    }
    case ADD = '/admin/category-page/add';
    case EDIT = '/admin/category-page/{id}/edit';
    case SHOW = '/admin/category-age/{id}/show';
    case DELETE = '/admin/category-delete/delete';
    case LIST = '/admin/category-list/index';
    case CONFIRM_DELETION = '/admin/category-confirm-deletion/confirm';
    case CANCEL_DELETION = '/admin/category-confirm-deletion/cancel';
}