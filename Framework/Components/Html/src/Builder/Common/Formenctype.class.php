<?php

declare(strict_types=1);

enum Formenctype : string
{
    case WWW = 'application/x-www-form-urlencoded';
    case FORM_DATA = 'multipart/form-data';
    case TEXT = 'text/plain';
}