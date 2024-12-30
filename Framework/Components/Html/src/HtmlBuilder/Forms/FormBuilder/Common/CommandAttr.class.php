<?php

declare(strict_types=1);

enum CommandAttr : string
{
    case SHOW_MODAL = 'show-modal';
    case CLOSE = 'close';
    case SHOW_POPOVER = 'show-popover';
    case HIDE_POPOVER = 'hide-popover';
    case TOGGLE_POPOVER = 'toggle-popover';
}