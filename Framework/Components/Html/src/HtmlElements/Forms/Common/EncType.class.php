<?php

declare(strict_types=1);

enum Enctype : string
{
    case FORM_DATA = 'multipart/form-data';
    case APPLICATION = 'application/x-www-form-urlencoded';
    case TEXT = 'text/plain';
}