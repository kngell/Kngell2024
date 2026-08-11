<?php

declare(strict_types=1);

enum ProductLinks: string
{
    case ADD = '/admin/admin/product-add';
    case SAVE = '/admin/admin/product-save';
    case DELETE = '/admin/admin/product-delete';
    case CONFIRM_DELETION = '/admin/product-confirm-deletion/index';
}
