<?php

declare(strict_types=1);

enum FooterLinks: string
{
    case ADD_COLUMN = '/admin/footer-page/add';
    case EDIT_COLUMN = '/admin/footer-page/edit/1';
    case SAVE_COLUMN = '/admin/footer-page/save';
    case DELETE_COLUMN = '/admin/footer-page/delete';
    case UPSORT_COLUMN = '/admin/footer-page/update-sort';
    case CANCEL = '/admin/footer-page/cancel';
}