<?php

declare(strict_types=1);

enum FlashFlagKey: string
{
    case INVALIDATE_CACHE = 'invalidate_cache';
    // Future flags can be added here
    // case USER_PREFERENCE_UPDATE = 'update_user_preferences';
    // case CLEAR_TEMP_DATA = 'clear_temp_data';
}