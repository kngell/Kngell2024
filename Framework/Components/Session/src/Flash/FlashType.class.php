<?php

declare(strict_types=1);

enum FlashType : string
{
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case DANGER = 'danger';
    case INFO = 'info';
}