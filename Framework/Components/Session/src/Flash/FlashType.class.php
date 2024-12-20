<?php

declare(strict_types=1);

enum FlashType : string
{
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case DANGER = 'error';
    case INFO = 'info';
}