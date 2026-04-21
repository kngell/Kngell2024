<?php

declare(strict_types=1);

enum TargetAttr : string
{
    case BLANK = '_blank'; //	The response is displayed in a new window or tab
    case SELF = '_self'; //	The response is displayed in the current window
    case PARENT = '_parent'; //	The response is displayed in the parent frame
    case TOP = '_top'; //	The response is displayed in the full body of the window
}