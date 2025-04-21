<?php

declare(strict_types=1);
enum SameSite : string
{
    case LAX = 'Lax';
    case STRICT = 'Strict';
    case NONE = 'None';
}